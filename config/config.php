<?php
/**
 * =============================================
 * SDAK-KS Configuration — Multi-Site
 * @version 2.0.0
 * =============================================
 * ระบบจะเลือก config ตาม domain อัตโนมัติ
 *
 * เพิ่มสมาคมใหม่:
 *   1. สร้างไฟล์ config/sites/{domain}.php
 *   2. สร้าง DB ใหม่บน server
 *   3. ชี้ domain มาที่ public_html
 *   4. เสร็จ!
 * =============================================
 */

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Forbidden');
}

defined('SDAK_KS') or define('SDAK_KS', true);

// =============================================
// 1. Detect current host
// =============================================
$_current_host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
$_current_host_noport = preg_replace('/:\d+$/', '', $_current_host); // localhost:8888 → localhost
$_current_port = $_SERVER['SERVER_PORT'] ?? '80';
$_current_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// =============================================
// 2. Load site-specific config
// =============================================
$_site_config_file = __DIR__ . '/sites/' . $_current_host_noport . '.php';

if (file_exists($_site_config_file)) {
    require_once $_site_config_file;
    define('SITE_CONFIG_SOURCE', $_current_host_noport);
} else {
    // Fallback: ถ้าไม่มี site config → ใช้ค่า default (ป้องกันเว็บล่ม)
    define('SITE_NAME', 'SDAK Association');
    define('SITE_NAME_SHORT', 'SDAK');
    define('SITE_NAME_EN', 'SDAK');
    define('DB_MODE', 'production');
    define('DB_TYPE', 'mysql');
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', 3306);
    define('DB_NAME', '');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
    define('SITE_CONFIG_SOURCE', 'default');
}

// =============================================
// 3. Domain & Base URL
// =============================================
define('SITE_DOMAIN', $_current_host_noport);

if (!defined('BASE_URL')) {
    if (DB_MODE === 'local') {
        if ($_current_port !== '80' && $_current_port !== '443') {
            define('BASE_URL', "{$_current_scheme}://{$_current_host_noport}:{$_current_port}");
        } else {
            define('BASE_URL', "{$_current_scheme}://{$_current_host_noport}");
        }
    } else {
        define('BASE_URL', "{$_current_scheme}://{$_current_host_noport}");
    }
}

// =============================================
// 4. Auth Configuration
// =============================================
if (!defined('TOKEN_EXPIRY')) define('TOKEN_EXPIRY', 86400);
if (!defined('REFRESH_TOKEN_EXPIRY')) define('REFRESH_TOKEN_EXPIRY', 604800);
if (!defined('TOKEN_SECRET')) define('TOKEN_SECRET', 'sdak-ks-secret-key-change-this-in-production');

// Google OAuth (fallback empty)
if (!defined('GOOGLE_CLIENT_ID')) define('GOOGLE_CLIENT_ID', '');
if (!defined('GOOGLE_CLIENT_SECRET')) define('GOOGLE_CLIENT_SECRET', '');
if (!defined('GOOGLE_REDIRECT_URI')) define('GOOGLE_REDIRECT_URI', '');

// =============================================
// 5. Upload Configuration — แยกตาม domain
// =============================================
//   uploads/sdak.obec.in/profiles/xxx.webp
//   uploads/saak.obec.in/profiles/xxx.webp
//   uploads/localhost/profiles/xxx.webp
define('UPLOAD_DIR', __DIR__ . '/../uploads/' . SITE_DOMAIN . '/');
define('UPLOAD_URL', BASE_URL . '/uploads/' . SITE_DOMAIN . '/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// สร้างโฟลเดอร์อัตโนมัติ
$_upload_subdirs = ['profiles', 'logos', 'news', 'activities', 'finance', 'payment_slips', 'payments', 'slips'];
foreach ($_upload_subdirs as $_subdir) {
    $_full_path = UPLOAD_DIR . $_subdir;
    if (!is_dir($_full_path)) {
        @mkdir($_full_path, 0755, true);
    }
}

// =============================================
// 6. Timezone & Error reporting
// =============================================
date_default_timezone_set(defined('APP_TIMEZONE') ? APP_TIMEZONE : 'Asia/Bangkok');

if (DB_MODE === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
