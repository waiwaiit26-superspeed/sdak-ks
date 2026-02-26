<?php
/**
 * fix-config.php — เขียน config.php ใหม่แบบ multi-site บน server
 * รันครั้งเดียว แล้วลบ
 */
$secretFile = __DIR__ . '/webhook-secret.php';
if (file_exists($secretFile)) require_once $secretFile;

$allowedKey = defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : '';
$isCLI = (php_sapi_name() === 'cli');
$validKey = !empty($allowedKey) && ($_GET['key'] ?? '') === $allowedKey;
if (!$isCLI && !$validKey) { http_response_code(403); die('Forbidden'); }

header('Content-Type: text/plain; charset=utf-8');

// อ่าน config.php จาก git (ที่ webhook deploy มาแล้วแต่ไม่ overwrite)
// ใช้วิธีดาวน์โหลดจาก GitHub raw
$githubUrl = 'https://raw.githubusercontent.com/waiwaijaidee/sdak-ks/main/config/config.php';
$token = defined('GITHUB_TOKEN') ? GITHUB_TOKEN : '';

$ch = curl_init($githubUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/vnd.github.v3.raw',
        'Authorization: token ' . $token,
        'User-Agent: SDAK-KS-Deploy',
    ],
]);
$content = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($content)) {
    echo "❌ ดาวน์โหลด config.php จาก GitHub ไม่สำเร็จ (HTTP {$httpCode})\n";
    exit(1);
}

// Backup old config
$configPath = __DIR__ . '/config/config.php';
$backupPath = __DIR__ . '/config/config.php.bak.' . date('YmdHis');

if (file_exists($configPath)) {
    copy($configPath, $backupPath);
    echo "✅ Backup config เดิมไว้ที่: " . basename($backupPath) . "\n";
}

// Write new config
file_put_contents($configPath, $content);
echo "✅ เขียน config/config.php ใหม่สำเร็จ (" . strlen($content) . " bytes)\n";

// Verify
echo "\n--- ทดสอบ ---\n";
echo "File size: " . filesize($configPath) . " bytes\n";
echo "Contains SITE_DOMAIN: " . (strpos($content, 'SITE_DOMAIN') !== false ? 'YES' : 'NO') . "\n";
echo "Contains sites/: " . (strpos($content, 'sites/') !== false ? 'YES' : 'NO') . "\n";

echo "\n🗑️ ลบไฟล์นี้ได้: fix-config.php\n";
