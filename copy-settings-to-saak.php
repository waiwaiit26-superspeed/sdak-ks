<?php
/**
 * Copy Google Client ID + SMTP settings from sdak DB to saak DB
 * Usage: curl "https://sdak.obec.in/copy-settings-to-saak.php?key=sdak2026"
 * Self-deleting script
 */

if (($_GET['key'] ?? '') !== 'sdak2026') {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid key']));
}

header('Content-Type: application/json; charset=utf-8');

// sdak DB config
$sdakDb = new PDO('mysql:host=127.0.0.1;port=3306;dbname=obecin_sdakks;charset=utf8mb4', 'obecin_sdakks', 'SdakKs@2026');
$sdakDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// saak DB config
$saakDb = new PDO('mysql:host=127.0.0.1;port=3306;dbname=obecin_saak;charset=utf8mb4', 'obecin_saak', 'Saak@2026');
$saakDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Keys to copy
$keys = [
    'google_client_id',
    'google_client_secret',
    'smtp_host',
    'smtp_port',
    'smtp_username',
    'smtp_password',
    'smtp_from_email',
    'smtp_from_name',
    'smtp_encryption',
];

// Read from sdak
$placeholders = implode(',', array_fill(0, count($keys), '?'));
$stmt = $sdakDb->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ($placeholders)");
$stmt->execute($keys);
$sdakSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if (empty($sdakSettings)) {
    die(json_encode(['error' => 'No settings found in sdak DB']));
}

// Write to saak
$copied = [];
foreach ($sdakSettings as $key => $value) {
    $check = $saakDb->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
    $check->execute([$key]);
    
    if ($check->fetchColumn() > 0) {
        $upd = $saakDb->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        $upd->execute([$value, $key]);
        $copied[$key] = 'updated';
    } else {
        $ins = $saakDb->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        $ins->execute([$key, $value]);
        $copied[$key] = 'inserted';
    }
}

// Self-delete if requested
$selfDelete = false;
if (isset($_GET['delete'])) {
    @unlink(__FILE__);
    $selfDelete = true;
}

echo json_encode([
    'success' => true,
    'copied' => $copied,
    'total' => count($copied),
    'self_delete' => $selfDelete ? 'deleted' : 'kept',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
