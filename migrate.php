<?php
// =============================================
// migrate.php — Auto Database Migration
// รันอัตโนมัติหลัง deploy (เรียกจาก webhook.php)
// หรือรันมือ: https://sdak.obec.in/migrate.php?key=YOUR_WEBHOOK_SECRET
//
// อ่าน SQL จาก migrations/ folder เรียงตามชื่อ (001_xxx.sql, 002_xxx.sql)
// ข้ามไฟล์ที่รันไปแล้ว (track ใน table `migrations`)
// =============================================

require_once __DIR__ . '/config/config.php';

// โหลด webhook secret (ถ้ายังไม่ได้โหลดจาก webhook.php)
$secretFile = __DIR__ . '/webhook-secret.php';
if (!defined('WEBHOOK_SECRET') && file_exists($secretFile)) {
    require_once $secretFile;
}

// ============ PDO Connection (ไม่ใช้ Medoo เพื่อความเสถียร) ============
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

        // ข้ามถ้ารันแล้ว
        if (in_array($filename, $executed)) {
            $results['skipped'][] = $filename;
            continue;
        }

        // อ่าน SQL
        $sql = file_get_contents($file);
        if (empty(trim($sql))) {
            $results['skipped'][] = $filename . ' (empty)';
            continue;
        }

        // รัน SQL
        try {
            $pdo->exec($sql);
            // บันทึกว่ารันแล้ว
            $insert = $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)");
            $insert->execute([$filename]);
            $results['run'][] = $filename;
        } catch (PDOException $e) {
            $results['errors'][] = $filename . ': ' . $e->getMessage();
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
