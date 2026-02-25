<?php
// =============================================
// webhook.php — GitHub Webhook Auto-Deploy
// วางไว้บน server: /domains/sdak.obec.in/public_html/webhook.php
// URL: https://sdak.obec.in/webhook.php
//
// วิธีทำงาน:
// 1. GitHub push event → webhook เรียก URL นี้
// 2. ตรวจ signature ว่ามาจาก GitHub จริง
// 3. รัน git pull เพื่อดึงโค้ดล่าสุด
// 4. ถ้า git pull ไม่ได้ → ดาวน์โหลด zip จาก GitHub API แทน
// =============================================

// ============ CONFIG ============
// โหลดค่าลับจากไฟล์แยก (ไม่อยู่ใน git)
require_once __DIR__ . '/webhook-secret.php';
// webhook-secret.php กำหนด: WEBHOOK_SECRET, GITHUB_TOKEN

// Repository info
define('REPO_OWNER', 'waiwaijaidee');
define('REPO_NAME', 'sdak-ks');
define('REPO_BRANCH', 'main');

// Deploy target directory (directory ที่ webhook.php อยู่)
define('DEPLOY_DIR', __DIR__);

// Log file
define('LOG_FILE', __DIR__ . '/deploy-log.txt');
// ================================

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Get payload
$payload = file_get_contents('php://input');
if (empty($payload)) {
    http_response_code(400);
    die('Empty payload');
}

// Verify GitHub signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!$signature) {
    http_response_code(403);
    logMsg('ERROR: Missing signature header');
    die('Missing signature');
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, WEBHOOK_SECRET);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    logMsg('ERROR: Invalid signature');
    die('Invalid signature');
}

// Parse JSON payload
$data = json_decode($payload, true);
if (!$data) {
    http_response_code(400);
    logMsg('ERROR: Invalid JSON payload');
    die('Invalid JSON');
}

// Only process push events to our branch
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/' . REPO_BRANCH) {
    http_response_code(200);
    logMsg("SKIP: Push to different branch: {$ref}");
    die("Skipped — not " . REPO_BRANCH . " branch");
}

// Get commit info
$headCommit = $data['head_commit'] ?? [];
$commitMsg = $headCommit['message'] ?? 'unknown';
$pusher = $data['pusher']['name'] ?? 'unknown';
$timestamp = date('Y-m-d H:i:s');

logMsg("========================================");
logMsg("DEPLOY START: {$timestamp}");
logMsg("Pushed by: {$pusher}");
logMsg("Commit: {$commitMsg}");

// ============================================
// METHOD 1: Try git pull (fastest & cleanest)
// ============================================
$shellAvailable = function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
$gitAvailable = false;

if ($shellAvailable) {
    try {
        $gitPath = trim(@shell_exec('which git 2>/dev/null') ?? '');
        $gitAvailable = !empty($gitPath) && is_dir(DEPLOY_DIR . '/.git');
    } catch (\Throwable $e) {
        logMsg("shell_exec check failed: " . $e->getMessage());
    }
}

if ($gitAvailable) {
    logMsg("METHOD: git pull");
    $output = [];
    $exitCode = -1;

    // Set safe directory (needed when running as web user)
    @exec("git config --global --add safe.directory " . escapeshellarg(DEPLOY_DIR) . " 2>&1");

    // Pull latest
    @exec("cd " . escapeshellarg(DEPLOY_DIR) . " && git fetch origin " . REPO_BRANCH . " 2>&1 && git reset --hard origin/" . REPO_BRANCH . " 2>&1", $output, $exitCode);

    $outputStr = implode("\n", $output);
    logMsg("git output: {$outputStr}");

    if ($exitCode === 0) {
        // Install/update composer dependencies
        runComposer();
        // Run migrations
        runMigrations();
        logMsg("DEPLOY SUCCESS (git pull)");
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'method' => 'git pull', 'commit' => $commitMsg]);
        exit;
    }

    logMsg("git pull FAILED (exit code: {$exitCode}), trying fallback...");
} else {
    logMsg("shell_exec/git not available — using zip download");
}

// ============================================
// METHOD 2: Download zip from GitHub API (no git needed)
// ============================================
logMsg("METHOD: zip download (fallback)");

$zipUrl = "https://api.github.com/repos/" . REPO_OWNER . "/" . REPO_NAME . "/zipball/" . REPO_BRANCH;
$zipFile = sys_get_temp_dir() . '/deploy-' . REPO_NAME . '-' . time() . '.zip';

// Download zip
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $zipUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_USERAGENT      => 'sdak-ks-deploy/1.0',
    CURLOPT_HTTPHEADER     => [
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . GITHUB_TOKEN,
    ],
]);
$zipData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($zipData)) {
    logMsg("ERROR: Failed to download zip (HTTP {$httpCode})");
    http_response_code(502);
    echo json_encode(['status' => 'error', 'message' => "Zip download failed (HTTP {$httpCode})"]);
    exit;
}

file_put_contents($zipFile, $zipData);
logMsg("Downloaded zip: " . strlen($zipData) . " bytes");

// Extract zip
$zip = new ZipArchive();
if ($zip->open($zipFile) !== true) {
    logMsg("ERROR: Cannot open zip file");
    @unlink($zipFile);
    http_response_code(500);
    die('Cannot open zip');
}

// GitHub zip contains a root folder like "owner-repo-abc1234/"
$rootDir = '';
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (substr_count($name, '/') === 1 && substr($name, -1) === '/') {
        $rootDir = $name;
        break;
    }
}

if (!$rootDir) {
    logMsg("ERROR: Cannot find root directory in zip");
    $zip->close();
    @unlink($zipFile);
    http_response_code(500);
    die('Invalid zip structure');
}

$extractDir = sys_get_temp_dir() . '/deploy-extract-' . time();
$zip->extractTo($extractDir);
$zip->close();

// Copy files — skip protected files/dirs
$protectedFiles = [
    'webhook.php',          // อย่าเขียนทับตัวเอง
    'webhook-secret.php',   // secrets ของ server
    'config/config.php',    // config มี DB credentials ของ production
    'deploy-log.txt',       // log
    '.git',                 // git history
    'vendor',               // composer (จะรัน composer install แทน)
    'uploads',              // ไฟล์ที่ user upload
    'cache',
    'backups',
];

$sourceDir = $extractDir . '/' . rtrim($rootDir, '/');

$copied = 0;
$skipped = 0;
copyDir($sourceDir, DEPLOY_DIR, $protectedFiles, $copied, $skipped);

logMsg("Copied {$copied} files, skipped {$skipped}");

// Cleanup
deleteDir($extractDir);
@unlink($zipFile);

// Install/update composer dependencies
runComposer();

// Run database migrations
runMigrations();

logMsg("DEPLOY SUCCESS (zip download)");
http_response_code(200);
echo json_encode(['status' => 'ok', 'method' => 'zip download', 'files_copied' => $copied]);
exit;

// ============================================
// HELPER FUNCTIONS
// ============================================

function runComposer() {
    if (!file_exists(DEPLOY_DIR . '/composer.json')) {
        logMsg("COMPOSER: composer.json not found — skipped");
        return;
    }

    $shellAvailable = function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
    if (!$shellAvailable) {
        logMsg("COMPOSER: shell_exec not available — skipped");
        return;
    }

    $output = [];
    $exitCode = -1;
    @exec("cd " . escapeshellarg(DEPLOY_DIR) . " && composer install --no-dev --optimize-autoloader 2>&1", $output, $exitCode);
    $outputStr = implode("\n", $output);

    if ($exitCode === 0) {
        logMsg("COMPOSER: install OK");
    } else {
        logMsg("COMPOSER: install failed (exit {$exitCode}): {$outputStr}");
    }
}

function runMigrations() {
    // ===== STEP 1: Backup DB ก่อน migrate =====
    $backupFile = DEPLOY_DIR . '/backup-db.php';
    if (file_exists($backupFile)) {
        try {
            $result = require $backupFile;
            if (is_array($result)) {
                if ($result['status'] === 'ok') {
                    $sizeKB = round(($result['size'] ?? 0) / 1024, 1);
                    logMsg("BACKUP: {$result['file']} ({$result['tables']} tables, {$sizeKB} KB)");
                } else {
                    logMsg("BACKUP ERROR: " . ($result['error'] ?? 'unknown'));
                }
            }
        } catch (\Throwable $e) {
            logMsg("BACKUP ERROR: " . $e->getMessage());
        }
    } else {
        logMsg("BACKUP: backup-db.php not found — skipped");
    }

    // ===== STEP 2: Run migrations =====
    $migrateFile = DEPLOY_DIR . '/migrate.php';
    if (!file_exists($migrateFile)) {
        logMsg("MIGRATE: migrate.php not found — skipped");
        return;
    }
    try {
        $results = require $migrateFile;
        if (is_array($results)) {
            $runCount = count($results['run'] ?? []);
            $skipCount = count($results['skipped'] ?? []);
            $errCount = count($results['errors'] ?? []);
            logMsg("MIGRATE: {$runCount} run, {$skipCount} skipped, {$errCount} errors");
            foreach ($results['run'] ?? [] as $f) logMsg("  ✅ {$f}");
            foreach ($results['errors'] ?? [] as $e) logMsg("  ❌ {$e}");
        }
    } catch (\Throwable $e) {
        logMsg("MIGRATE ERROR: " . $e->getMessage());
    }
}

function copyDir($source, $dest, $protected, &$copied, &$skipped) {
    if (!is_dir($source)) return;
    if (!is_dir($dest)) @mkdir($dest, 0755, true);

    $items = scandir($source);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $srcPath = $source . '/' . $item;
        $destPath = $dest . '/' . $item;
        $relPath = ltrim(str_replace(DEPLOY_DIR, '', $destPath), '/');

        // Skip protected files/dirs
        foreach ($protected as $p) {
            if ($item === $p || strpos($relPath, $p) === 0) {
                $skipped++;
                continue 2;
            }
        }

        if (is_dir($srcPath)) {
            if (!is_dir($destPath)) @mkdir($destPath, 0755, true);
            copyDir($srcPath, $destPath, $protected, $copied, $skipped);
        } else {
            if (@copy($srcPath, $destPath)) {
                $copied++;
            }
        }
    }
}

function deleteDir($dir) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        is_dir($path) ? deleteDir($path) : @unlink($path);
    }
    @rmdir($dir);
}

function logMsg($msg) {
    $time = date('H:i:s');
    @file_put_contents(LOG_FILE, "[{$time}] {$msg}\n", FILE_APPEND | LOCK_EX);
}
