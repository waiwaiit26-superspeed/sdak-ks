# คู่มือ: GitHub Webhook Auto-Deploy + Backup & Migrate Database

> **ใช้เป็นคำสั่งให้ AI ตั้งค่าโปรเจกต์ใหม่** — อ้างอิงจากโปรเจกต์ sdak-ks  
> ครอบคลุม 3 ส่วน: **Webhook Deploy** → **Backup DB** → **Migrate DB**

---

## ภาพรวมการทำงาน

```
git push → GitHub → Webhook POST → Server
                                      │
                                      ├── 1. ตรวจ signature (HMAC SHA-256)
                                      ├── 2. ดึงโค้ด (git pull / zip fallback)
                                      ├── 3. composer install
                                      ├── 4. backup-db.php (สำรอง DB)
                                      └── 5. migrate.php (รัน SQL migrations)
```

---

## ไฟล์ที่ต้องสร้าง

```
project-root/
├── webhook.php              # รับ GitHub webhook → auto deploy
├── webhook-secret.php       # เก็บ secret key (❌ อยู่ใน .gitignore)
├── backup-db.php            # สำรอง DB ก่อน migrate
├── migrate.php              # รัน SQL migration อัตโนมัติ
├── migrations/              # ไฟล์ SQL เรียงตามลำดับ
│   ├── 001_initial_schema.sql
│   ├── 002_xxx.sql
│   └── ...
└── backups/
    └── db/                  # backup จะถูกสร้างอัตโนมัติ
        └── .htaccess        # Deny from all
```

---

## ส่วนที่ 0: เตรียม Git ก่อน Push

> สมมติว่าสร้าง repo และเชื่อม remote ผ่าน GitHub Desktop เรียบร้อยแล้ว

### 0.1 เพิ่มใน `.gitignore` (ก่อน push ครั้งแรก)

```gitignore
# Secrets — ห้าม push ขึ้น GitHub
webhook-secret.php

# Backup & Deploy log
backups/
deploy-log.txt
```

### 0.2 Workflow ใช้งานประจำวัน

```
แก้โค้ด → Commit & Push (ผ่าน GitHub Desktop หรือ terminal)
→ GitHub ส่ง webhook ไป server อัตโนมัติ
→ server ดึงโค้ด + backup DB + migrate DB
```

### 0.3 lftp Deploy (Git diff + commit baseline)

สำหรับ deploy ด้วย FTP โดยใช้ `lftp` และเฉพาะไฟล์ที่เปลี่ยนจาก git commit:

1. สร้างไฟล์ config deploy

```bash
cp .deploy.env.example .deploy.env
```

2. แก้ค่าใน `.deploy.env`
- `FTP_HOST` และ `FTP_PORT`
- `FTP_USER` และ `FTP_PASS`
- `REMOTE_DIR` = path บน FTP ที่เป็น web root หรือโฟลเดอร์ deploy
- `SITE_URL` = URL เว็บไซต์
- `DEPLOY_SECRET` = secret เดียวกับ `webhook-secret.php` และ `backup-db.php`/`migrate.php`

3. สร้าง baseline commit ของ production

```bash
git rev-parse HEAD > .deploy.git_hash
```

4. ใช้ `deploy-lftp.sh` เพื่อ deploy

```bash
./deploy-lftp.sh
```

5. ถ้ามีไฟล์ใน `migrations/` ที่เปลี่ยนแปลง
- สคริปต์จะเรียก `backup-db.php` ก่อน
- แล้วเรียก `migrate.php`

6. ถ้าต้องการตรวจ remote state ก่อน deploy
- ตั้ง `REMOTE_STATE_URL` เป็น URL ที่ตอบค่าธรรมดา เช่น `deploy-state.php`
- สคริปต์จะตรวจว่า `remote hash == .deploy.git_hash`

---

## ส่วนที่ 1: GitHub Webhook Auto-Deploy

### 1.1 ตั้งค่า Webhook บน GitHub

1. ไปที่ GitHub repo → **Settings** → **Webhooks** → **Add webhook**

| Field | Value |
|-------|-------|
| **Payload URL** | `https://yourdomain.com/webhook.php` |
| **Content type** | `application/json` |
| **Secret** | ใส่ secret key เดียวกับใน `webhook-secret.php` |
| **Which events?** | Just the push event |
| **Active** | ✅ |

### 1.2 สร้าง GitHub Personal Access Token

1. ไปที่ https://github.com/settings/tokens?type=beta
2. **Generate new token (Fine-grained)**
3. Repository access: **Only select repositories** → เลือก repo
4. Permissions: **Contents** → Read-only
5. คัดลอก token ไปใส่ใน `webhook-secret.php` บน server

### 1.3 สร้าง `webhook-secret.php`

> ❌ ห้าม push ขึ้น GitHub — ต้องอยู่ใน `.gitignore`  
> ✅ upload ไป server ด้วยมือครั้งเดียว

**ตัวอย่างจาก sdak-ks:**

```php
<?php
// webhook-secret.php — ค่าลับสำหรับ webhook
// ❌ ห้าม push ขึ้น GitHub (อยู่ใน .gitignore)
// ✅ ต้อง upload ไป server ด้วยมือ (ครั้งเดียว)

// Webhook secret — ต้องตรงกับที่ตั้งใน GitHub Webhook settings
define('WEBHOOK_SECRET', 'your-secret-key-change-this');

// GitHub Personal Access Token (fine-grained)
// สร้างที่: https://github.com/settings/tokens?type=beta
// Permissions: Contents → Read-only
define('GITHUB_TOKEN', 'github_pat_xxxxxxxxxxxxx');
```

### 1.4 สร้าง `webhook.php`

**หลักการทำงาน:**
1. รับ POST จาก GitHub push event
2. ตรวจ signature ด้วย HMAC SHA-256
3. Deploy ด้วย **git pull** (ถ้ามี git) หรือ **ดาวน์โหลด zip** (fallback สำหรับ shared hosting)
4. รัน `composer install`
5. เรียก `backup-db.php` แล้วตามด้วย `migrate.php`

**โค้ดเต็มจาก sdak-ks:**

```php
<?php
// =============================================
// webhook.php — GitHub Webhook Auto-Deploy
// วางไว้บน server: /domains/yourdomain.com/public_html/webhook.php
// URL: https://yourdomain.com/webhook.php
//
// วิธีทำงาน:
// 1. GitHub push event → webhook เรียก URL นี้
// 2. ตรวจ signature ว่ามาจาก GitHub จริง
// 3. รัน git pull เพื่อดึงโค้ดล่าสุด
// 4. ถ้า git pull ไม่ได้ → ดาวน์โหลด zip จาก GitHub API แทน
// =============================================

// ============ CONFIG ============
require_once __DIR__ . '/webhook-secret.php';
// webhook-secret.php กำหนด: WEBHOOK_SECRET, GITHUB_TOKEN

// Repository info — ★ เปลี่ยนให้ตรงกับ repo ของคุณ ★
define('REPO_OWNER', 'your-github-username');
define('REPO_NAME', 'your-repo-name');
define('REPO_BRANCH', 'main');

define('DEPLOY_DIR', __DIR__);
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

    @exec("git config --global --add safe.directory " . escapeshellarg(DEPLOY_DIR) . " 2>&1");
    @exec("cd " . escapeshellarg(DEPLOY_DIR) . " && git fetch origin " . REPO_BRANCH . " 2>&1 && git reset --hard origin/" . REPO_BRANCH . " 2>&1", $output, $exitCode);

    $outputStr = implode("\n", $output);
    logMsg("git output: {$outputStr}");

    if ($exitCode === 0) {
        runComposer();
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

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $zipUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_USERAGENT      => REPO_NAME . '-deploy/1.0',
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

// ★ ไฟล์/โฟลเดอร์ที่ห้ามเขียนทับ — ปรับตามโปรเจกต์ ★
$protectedFiles = [
    'webhook.php',          // อย่าเขียนทับตัวเอง
    'webhook-secret.php',   // secrets ของ server
    'config/sites',         // site configs มี credentials
    'deploy-log.txt',       // log
    '.git',                 // git history
    'vendor',               // composer (จะรัน composer install แทน)
    'uploads',              // ไฟล์ที่ user upload
    'cache',
    'backups',
];

// ★ ไฟล์ชั่วคราวที่ต้องลบออกจาก server (ถ้ามี) ★
$filesToDelete = [
    // 'old-script.php',
    // 'config/config.php.bak.*',
];

$sourceDir = $extractDir . '/' . rtrim($rootDir, '/');
$copied = 0;
$skipped = 0;
copyDir($sourceDir, DEPLOY_DIR, $protectedFiles, $copied, $skipped);

logMsg("Copied {$copied} files, skipped {$skipped}");

cleanupObsoleteFiles($filesToDelete);

// Cleanup temp
deleteDir($extractDir);
@unlink($zipFile);

runComposer();
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
    logMsg("COMPOSER: " . ($exitCode === 0 ? 'OK' : 'FAILED (exit ' . $exitCode . ')'));
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
            if (@copy($srcPath, $destPath)) $copied++;
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

function cleanupObsoleteFiles($filesToDelete) {
    $deleted = 0;
    foreach ($filesToDelete as $pattern) {
        $fullPattern = DEPLOY_DIR . '/' . $pattern;
        if (strpos($pattern, '*') !== false) {
            foreach (glob($fullPattern) as $file) {
                if (@unlink($file)) { $deleted++; logMsg("CLEANUP: deleted " . basename($file)); }
            }
        } else {
            if (file_exists($fullPattern) && @unlink($fullPattern)) {
                $deleted++;
                logMsg("CLEANUP: deleted {$pattern}");
            }
        }
    }
    if ($deleted > 0) logMsg("CLEANUP: removed {$deleted} obsolete file(s)");
}

function logMsg($msg) {
    $time = date('H:i:s');
    @file_put_contents(LOG_FILE, "[{$time}] {$msg}\n", FILE_APPEND | LOCK_EX);
}
```

### 1.5 จุดที่ต้องแก้ไขให้ตรงกับโปรเจกต์ใหม่

| ตัวแปร | แก้ไขเป็น | อยู่ในไฟล์ |
|--------|----------|-----------|
| `REPO_OWNER` | GitHub username | `webhook.php` |
| `REPO_NAME` | ชื่อ repo | `webhook.php` |
| `REPO_BRANCH` | branch ที่ deploy (`main`) | `webhook.php` |
| `WEBHOOK_SECRET` | secret key ที่ตั้งเอง | `webhook-secret.php` |
| `GITHUB_TOKEN` | Personal Access Token | `webhook-secret.php` |
| `$protectedFiles` | ไฟล์/โฟลเดอร์ที่ห้ามเขียนทับ | `webhook.php` |

---

## ส่วนที่ 2: Backup Database ก่อน Migrate

### 2.1 หลักการ

- ใช้ **Pure PHP/PDO** (ไม่ใช้ `mysqldump` เพราะ shared hosting ปิด `shell_exec`)
- Export ทุกตาราง: `DROP TABLE` + `CREATE TABLE` + `INSERT` (batch 100 rows)
- เก็บใน `backups/db/` พร้อม `.htaccess` ป้องกันเข้าถึง
- เก็บไม่เกิน **10 ไฟล์** — ลบเก่าสุดอัตโนมัติ
- ถูกเรียกจาก `webhook.php` → `runMigrations()` อัตโนมัติก่อนทุกครั้งที่ migrate

### 2.2 วิธีเรียกใช้

| วิธี | รายละเอียด |
|------|-----------|
| **อัตโนมัติ** | `webhook.php` เรียก `backup-db.php` ก่อน `migrate.php` ทุกครั้ง |
| **ผ่าน browser** | `https://yourdomain.com/backup-db.php?key=YOUR_WEBHOOK_SECRET` |
| **ผ่าน CLI** | `php backup-db.php` |

### 2.3 โค้ดเต็ม `backup-db.php`

```php
<?php
// =============================================
// backup-db.php — Auto Database Backup (Pure PHP/PDO)
// ใช้ mysqldump ไม่ได้ (shell_exec ถูก disable) → ใช้ PDO export แทน
//
// เรียกจาก webhook.php ก่อน migrate อัตโนมัติ
// หรือเรียกมือ: https://yourdomain.com/backup-db.php?key=YOUR_WEBHOOK_SECRET
// =============================================

require_once __DIR__ . '/config/config.php';

$secretFile = __DIR__ . '/webhook-secret.php';
if (!defined('WEBHOOK_SECRET') && file_exists($secretFile)) {
    require_once $secretFile;
}

function getBackupPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = DB_TYPE . ':host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// ============ ป้องกันเรียกจากภายนอก ============
$allowedKey = defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : '';
$isWebhook = (php_sapi_name() !== 'cli' && !isset($_GET['key']));
$isCLI = (php_sapi_name() === 'cli');
$validKey = !empty($allowedKey) && ($_GET['key'] ?? '') === $allowedKey;

if (!$isWebhook && !$isCLI && !$validKey) {
    http_response_code(403);
    die('Forbidden — ต้องใส่ ?key=xxx');
}

// ============ CONFIG ============
$backupDir = __DIR__ . '/backups/db';
$maxBackups = 10; // เก็บไว้ไม่เกิน 10 ไฟล์ ลบเก่าสุดอัตโนมัติ
// ================================

$result = ['status' => 'ok', 'file' => '', 'tables' => 0, 'size' => 0, 'error' => ''];

try {
    if (!is_dir($backupDir)) {
        @mkdir($backupDir, 0755, true);
        @file_put_contents($backupDir . '/.htaccess', "Deny from all\n");
    }

    $pdo = getBackupPDO();
    $dbName = DB_NAME;
    $timestamp = date('Y-m-d_His');
    $filename = "backup_{$dbName}_{$timestamp}.sql";
    $filepath = $backupDir . '/' . $filename;

    $sql = '';
    $tableCount = 0;

    $sql .= "-- =============================================\n";
    $sql .= "-- Database Backup: {$dbName}\n";
    $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
    $sql .= "-- Auto-generated by backup-db.php\n";
    $sql .= "-- =============================================\n\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $tableCount++;

        $sql .= "-- -------------------------------------------------\n";
        $sql .= "-- Table: {$table}\n";
        $sql .= "-- -------------------------------------------------\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

        $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
        $sql .= $createStmt['Create Table'] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $columns = array_keys($rows[0]);
            $colList = '`' . implode('`, `', $columns) . '`';

            $chunks = array_chunk($rows, 100);
            foreach ($chunks as $chunk) {
                $values = [];
                foreach ($chunk as $row) {
                    $vals = [];
                    foreach ($row as $val) {
                        $vals[] = ($val === null) ? 'NULL' : $pdo->quote($val);
                    }
                    $values[] = '(' . implode(', ', $vals) . ')';
                }
                $sql .= "INSERT INTO `{$table}` ({$colList}) VALUES\n";
                $sql .= implode(",\n", $values) . ";\n";
            }
            $sql .= "\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    $sql .= "\n-- Backup complete: {$tableCount} tables\n";

    file_put_contents($filepath, $sql);
    $filesize = filesize($filepath);

    $result['file'] = $filename;
    $result['tables'] = $tableCount;
    $result['size'] = $filesize;

    // ลบ backup เก่าถ้าเกิน $maxBackups
    $backupFiles = glob($backupDir . '/backup_*.sql');
    if (count($backupFiles) > $maxBackups) {
        usort($backupFiles, function($a, $b) { return filemtime($a) - filemtime($b); });
        $toDelete = count($backupFiles) - $maxBackups;
        for ($i = 0; $i < $toDelete; $i++) {
            @unlink($backupFiles[$i]);
        }
    }

} catch (\Throwable $e) {
    $result['status'] = 'error';
    $result['error'] = $e->getMessage();
}

// ถ้าเรียกจาก webhook → return ผลลัพธ์
if ($isWebhook) {
    return $result;
}

// ถ้าเรียกจาก browser/CLI → แสดงผล
header('Content-Type: text/plain; charset=utf-8');
if ($result['status'] === 'ok') {
    echo "✅ Backup OK\n";
    echo "File: {$result['file']}\n";
    echo "Tables: {$result['tables']}\n";
    echo "Size: " . number_format($result['size']) . " bytes\n";
} else {
    echo "❌ Backup FAILED\n";
    echo "Error: {$result['error']}\n";
}
```

### 2.4 จุดสำคัญ

- เมื่อถูกเรียกจาก `webhook.php` จะ **return array** กลับไป (ไม่ echo)
- เมื่อเรียกจาก browser/CLI จะ **echo ผลลัพธ์** ให้ดู
- ป้องกันเรียกจากภายนอกด้วย `?key=` parameter
- `.htaccess` ใน `backups/db/` ป้องกันดาวน์โหลดไฟล์ backup ผ่านเว็บ

---

## ส่วนที่ 3: Database Migration

### 3.1 หลักการ

- อ่านไฟล์ `.sql` จาก `migrations/` เรียงตามชื่อ (`001_xxx.sql`, `002_xxx.sql`, ...)
- Track ไฟล์ที่รันแล้วในตาราง `migrations` — ข้ามไฟล์ที่รันไปแล้ว
- รองรับ **safe errors** (column/table มีอยู่แล้ว → บันทึกว่ารันแล้ว ไม่ error)

### 3.2 กฎการตั้งชื่อไฟล์ Migration

```
migrations/
├── 001_initial_schema.sql      ← สร้างตารางหลัก
├── 002_add_users_table.sql     ← เพิ่มตาราง
├── 003_add_email_column.sql    ← ALTER TABLE เพิ่ม column
└── ...
```

- ขึ้นต้นด้วยเลข **3 หลัก**: `001_`, `002_`, ...
- ตามด้วยชื่อที่อธิบายว่าทำอะไร
- นามสกุล `.sql`
- ไฟล์จะถูกรันตามลำดับ (sort by filename)

### 3.3 วิธีเรียกใช้

| วิธี | รายละเอียด |
|------|-----------|
| **อัตโนมัติ** | `webhook.php` เรียกหลังจาก backup เสร็จ |
| **ผ่าน browser** | `https://yourdomain.com/migrate.php?key=YOUR_WEBHOOK_SECRET` |
| **ผ่าน CLI** | `php migrate.php` |

### 3.4 โค้ดเต็ม `migrate.php`

```php
<?php
// =============================================
// migrate.php — Auto Database Migration
// รันอัตโนมัติหลัง deploy (เรียกจาก webhook.php)
// หรือรันมือ: https://yourdomain.com/migrate.php?key=YOUR_WEBHOOK_SECRET
//
// อ่าน SQL จาก migrations/ folder เรียงตามชื่อ (001_xxx.sql, 002_xxx.sql)
// ข้ามไฟล์ที่รันไปแล้ว (track ใน table `migrations`)
// =============================================

require_once __DIR__ . '/config/config.php';

$secretFile = __DIR__ . '/webhook-secret.php';
if (!defined('WEBHOOK_SECRET') && file_exists($secretFile)) {
    require_once $secretFile;
}

function getMigratePDO() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = DB_TYPE . ':host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// ============ ป้องกันเรียกจากภายนอก ============
$allowedKey = defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : '';
$isWebhook = (php_sapi_name() !== 'cli' && !isset($_GET['key']));
$isCLI = (php_sapi_name() === 'cli');
$validKey = !empty($allowedKey) && ($_GET['key'] ?? '') === $allowedKey;

if (!$isWebhook && !$isCLI && !$validKey) {
    http_response_code(403);
    die('Forbidden — ต้องใส่ ?key=xxx');
}

$migrationsDir = __DIR__ . '/migrations';
$results = ['run' => [], 'skipped' => [], 'errors' => []];

try {
    $pdo = getMigratePDO();

    // สร้างตาราง migrations (ถ้ายังไม่มี)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `filename` VARCHAR(255) NOT NULL,
            `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_filename` (`filename`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // อ่านไฟล์ migration ทั้งหมด เรียงตามชื่อ
    $files = glob($migrationsDir . '/*.sql');
    if (!$files) $files = [];
    sort($files); // 001_xxx.sql, 002_xxx.sql, ...

    // ดึง migration ที่รันแล้ว
    $stmt = $pdo->query("SELECT filename FROM migrations");
    $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($files as $file) {
        $filename = basename($file);

        if (in_array($filename, $executed)) {
            $results['skipped'][] = $filename;
            continue;
        }

        $sql = file_get_contents($file);
        if (empty(trim($sql))) {
            $results['skipped'][] = $filename . ' (empty)';
            continue;
        }

        try {
            $pdo->exec($sql);
            $insert = $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)");
            $insert->execute([$filename]);
            $results['run'][] = $filename;
        } catch (PDOException $e) {
            // บาง error ถือว่า "safe" — เช่น column มีอยู่แล้ว, table มีอยู่แล้ว
            $safeErrors = ['Duplicate column', 'already exists', 'Duplicate key name'];
            $isSafe = false;
            foreach ($safeErrors as $pattern) {
                if (stripos($e->getMessage(), $pattern) !== false) {
                    $isSafe = true;
                    break;
                }
            }
            if ($isSafe) {
                $insert = $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)");
                $insert->execute([$filename]);
                $results['run'][] = $filename . ' (already applied)';
            } else {
                $results['errors'][] = $filename . ': ' . $e->getMessage();
            }
        }
    }
} catch (PDOException $e) {
    $results['errors'][] = 'DB connection: ' . $e->getMessage();
}

// Output
$output = "=== Migration Results ===\n";
$output .= "Run: " . count($results['run']) . " | Skipped: " . count($results['skipped']) . " | Errors: " . count($results['errors']) . "\n";
if ($results['run']) $output .= "  ✅ " . implode("\n  ✅ ", $results['run']) . "\n";
if ($results['skipped']) $output .= "  ⏭️ " . implode("\n  ⏭️ ", $results['skipped']) . "\n";
if ($results['errors']) $output .= "  ❌ " . implode("\n  ❌ ", $results['errors']) . "\n";

// ถ้าเรียกจาก webhook → return ผลลัพธ์
if ($isWebhook) {
    return $results;
}

// ถ้าเรียกจาก browser/CLI → แสดงผล
header('Content-Type: text/plain; charset=utf-8');
echo $output;
```

---

## ส่วนที่ 4: Deploy Log

### 4.1 หลักการ

- ทุกขั้นตอนใน `webhook.php` จะเขียน log ลงไฟล์ `deploy-log.txt` ผ่านฟังก์ชัน `logMsg()`
- บันทึก: เวลา, ใครเป็นคน push, commit message, วิธี deploy, ผล backup, ผล migrate
- ไฟล์ `deploy-log.txt` อยู่ใน `.gitignore` — ไม่ถูก push ขึ้น GitHub

### 4.2 ตัวอย่าง Log

```
[08:30:01] ========================================
[08:30:01] DEPLOY START: 2026-03-01 08:30:01
[08:30:01] Pushed by: waiwaijaidee
[08:30:01] Commit: เพิ่ม migration ใหม่
[08:30:02] METHOD: zip download (fallback)
[08:30:05] Downloaded zip: 2458103 bytes
[08:30:08] Copied 145 files, skipped 12
[08:30:08] COMPOSER: OK
[08:30:09] BACKUP: backup_mydb_2026-03-01_083009.sql (25 tables, 128.5 KB)
[08:30:09] MIGRATE: 1 run, 20 skipped, 0 errors
[08:30:09]   ✅ 022_add_phone_column.sql
[08:30:09] DEPLOY SUCCESS (zip download)
```

### 4.3 ฟังก์ชัน `logMsg()` (อยู่ใน `webhook.php`)

```php
function logMsg($msg) {
    $time = date('H:i:s');
    @file_put_contents(LOG_FILE, "[{$time}] {$msg}\n", FILE_APPEND | LOCK_EX);
}
```

### 4.4 วิธีดู Log บน Server

- **FTP/SSH:** เปิดไฟล์ `deploy-log.txt` ที่ root ของโปรเจกต์
- **Browser:** `https://yourdomain.com/deploy-log.txt` (ถ้าไม่ได้บล็อก)
  > แนะนำสร้าง `.htaccess` บล็อก หรือเปลี่ยนชื่อไฟล์ให้เดายาก

---

## ลำดับการสร้างไฟล์ (สำหรับ AI)

เมื่อสร้างโปรเจกต์ใหม่ ให้สร้างไฟล์ตามลำดับนี้:

```
1. เพิ่มใน .gitignore: webhook-secret.php, backups/, deploy-log.txt
2. สร้าง webhook-secret.php (template — ต้องแก้ค่าจริงบน server)
3. สร้าง webhook.php (แก้ REPO_OWNER, REPO_NAME, $protectedFiles)
4. สร้าง backup-db.php
5. สร้าง migrate.php
6. สร้างโฟลเดอร์ migrations/ พร้อม 001_initial_schema.sql
7. Push ขึ้น GitHub
8. ตั้งค่า GitHub Webhook (Settings → Webhooks → Add)
9. Upload webhook-secret.php ไป server ด้วยมือ
```

### หมายเหตุ

- `backup-db.php` และ `migrate.php` ต้องการ `config/config.php` ที่กำหนด constants: `DB_TYPE`, `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET` — **ปรับตามโครงสร้าง config ของโปรเจกต์ใหม่**
- Hosting ที่ปิด `shell_exec` ก็ใช้ได้ — webhook จะ fallback ไปใช้ zip download
- Backup เก็บไว้ 10 ไฟล์ล่าสุด ลบเก่าอัตโนมัติ (ปรับ `$maxBackups`)
- Migration รันซ้ำไม่เสียหาย (track ด้วยตาราง `migrations`)
