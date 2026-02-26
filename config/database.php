<?php
/**
 * Database Connection using Medoo
 */
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Medoo\Medoo;

function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new Medoo([
                'type' => DB_TYPE,
                'host' => DB_HOST,
                'port' => DB_PORT,
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASS,
                'charset' => DB_CHARSET,
                'collation' => 'utf8mb4_unicode_ci',
                'option' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
    }
    return $db;
}

/**
 * Get site config from DB (cached), fallback to PHP constant
 * Usage: siteConfig('site_name_short') → ดึงจาก site_settings ก่อน, ถ้าไม่มีใช้ค่าจาก config
 */
function siteConfig(string $key): string
{
    static $cache = null;
    if ($cache === null) {
        try {
            $db = getDB();
            $rows = $db->select('site_settings', ['setting_key', 'setting_value'], [
                'setting_key' => ['site_name', 'site_name_short', 'site_name_en']
            ]);
            $cache = [];
            foreach ($rows as $row) {
                if ($row['setting_value'] !== null && $row['setting_value'] !== '') {
                    $cache[$row['setting_key']] = $row['setting_value'];
                }
            }
        } catch (\Exception $e) {
            $cache = [];
        }
    }

    if (!empty($cache[$key])) {
        return $cache[$key];
    }

    // Fallback to config constant
    $map = [
        'site_name'       => 'SITE_NAME',
        'site_name_short' => 'SITE_NAME_SHORT',
        'site_name_en'    => 'SITE_NAME_EN',
    ];
    if (isset($map[$key]) && defined($map[$key])) {
        return constant($map[$key]);
    }

    return '';
}
