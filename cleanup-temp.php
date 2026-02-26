<?php
/**
 * cleanup-temp.php — ลบไฟล์ชั่วคราวที่ไม่ใช้แล้ว แล้วลบตัวเองด้วย
 * เรียกผ่าน: https://sdak.obec.in/cleanup-temp.php?key=xxx
 */

require_once __DIR__ . '/webhook-secret.php';

// ต้องมี key ตรงกับ WEBHOOK_SECRET
if (($_GET['key'] ?? '') !== WEBHOOK_SECRET) {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: application/json; charset=utf-8');

$filesToDelete = [
    'setup-multisite.php',
    'fix-config.php',
];

// Also delete config backups
$backupGlob = glob(__DIR__ . '/config/config.php.bak.*');

$results = [];

foreach ($filesToDelete as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $ok = @unlink($path);
        $results[] = ['file' => $file, 'status' => $ok ? 'deleted' : 'failed'];
    } else {
        $results[] = ['file' => $file, 'status' => 'not_found'];
    }
}

foreach ($backupGlob as $bak) {
    $ok = @unlink($bak);
    $name = basename($bak);
    $results[] = ['file' => "config/{$name}", 'status' => $ok ? 'deleted' : 'failed'];
}

// ลบตัวเอง
$selfDeleted = @unlink(__FILE__);
$results[] = ['file' => 'cleanup-temp.php', 'status' => $selfDeleted ? 'self-deleted' : 'failed'];

echo json_encode(['results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
