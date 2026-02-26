<?php
/**
 * update-saak-name.php — อัปเดตชื่อสมาคม SAAK ใน DB + config
 * เรียก: https://saak.obec.in/update-saak-name.php?key=xxx
 * ลบตัวเองเมื่อเสร็จ
 */
require_once __DIR__ . '/webhook-secret.php';

if (($_GET['key'] ?? '') !== WEBHOOK_SECRET) {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: application/json; charset=utf-8');
$results = [];

// ===== 1. อัปเดต config/sites/saak.obec.in.php =====
$configFile = __DIR__ . '/config/sites/saak.obec.in.php';
if (file_exists($configFile)) {
    $content = file_get_contents($configFile);
    $original = $content;
    
    // Replace SITE_NAME
    $content = preg_replace(
        "/define\('SITE_NAME',\s*'[^']*'\)/",
        "define('SITE_NAME', 'สมาคมผู้บริหารโรงเรียนมัธยมจังหวัดกาฬสินธุ์')",
        $content
    );
    // Replace SITE_NAME_SHORT
    $content = preg_replace(
        "/define\('SITE_NAME_SHORT',\s*'[^']*'\)/",
        "define('SITE_NAME_SHORT', 'ส.บ.ม.กส.')",
        $content
    );
    // Replace comment
    $content = str_replace('สมาคม SAAK', 'สมาคมผู้บริหารโรงเรียนมัธยมจังหวัดกาฬสินธุ์ (ส.บ.ม.กส.)', $content);
    
    if ($content !== $original) {
        file_put_contents($configFile, $content);
        $results[] = ['step' => 'config file', 'status' => 'updated'];
    } else {
        $results[] = ['step' => 'config file', 'status' => 'no change needed'];
    }
} else {
    $results[] = ['step' => 'config file', 'status' => 'not found'];
}

// ===== 2. อัปเดต DB site_settings =====
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/vendor/autoload.php';
    
    $db = new \Medoo\Medoo([
        'type'      => DB_TYPE,
        'host'      => DB_HOST,
        'port'      => DB_PORT,
        'database'  => DB_NAME,
        'username'  => DB_USER,
        'password'  => DB_PASS,
        'charset'   => DB_CHARSET,
    ]);
    
    $updates = [
        'site_name'       => 'สมาคมผู้บริหารโรงเรียนมัธยมจังหวัดกาฬสินธุ์',
        'site_name_short' => 'ส.บ.ม.กส.',
        'site_name_en'    => 'SAAK',
    ];
    
    foreach ($updates as $key => $value) {
        $exists = $db->get('site_settings', 'setting_value', ['setting_key' => $key]);
        if ($exists !== null) {
            $db->update('site_settings', ['setting_value' => $value], ['setting_key' => $key]);
            $results[] = ['step' => "DB {$key}", 'status' => 'updated', 'value' => $value];
        } else {
            $db->insert('site_settings', ['setting_key' => $key, 'setting_value' => $value]);
            $results[] = ['step' => "DB {$key}", 'status' => 'inserted', 'value' => $value];
        }
    }
    
    $results[] = ['step' => 'DB connection', 'database' => DB_NAME, 'status' => 'ok'];
} catch (\Throwable $e) {
    $results[] = ['step' => 'DB', 'status' => 'error', 'message' => $e->getMessage()];
}

// ===== 3. ลบตัวเอง =====
$selfDeleted = @unlink(__FILE__);
$results[] = ['step' => 'self-delete', 'status' => $selfDeleted ? 'deleted' : 'failed'];

echo json_encode(['results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
