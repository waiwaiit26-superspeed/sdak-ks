<?php
/**
 * SDAK-KS Configuration
 * @version 1.0.1
 */

// Prevent direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Forbidden');
}

defined('SDAK_KS') or define('SDAK_KS', true);


// Site Info
define('SITE_NAME', 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์');
define('SITE_NAME_SHORT', 'ส.ร.ม.ก.');
define('SITE_NAME_EN', 'SDAK-KS');

// Database Configuration
// define('DB_MODE', 'local'); // local | production
define('DB_MODE', 'production'); // local | production

if (DB_MODE === 'local') {
    define('DB_TYPE', 'mysql');
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', 8889);
    define('DB_NAME', 'sdak_ks');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');
    define('DB_CHARSET', 'utf8mb4');
} else {
    define('DB_TYPE', 'mysql');
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', 3306);
    define('DB_NAME', 'obecin_sdakks');
    define('DB_USER', 'obecin_sdakks');
    define('DB_PASS', 'SdakKs@2026');
    define('DB_CHARSET', 'utf8mb4');
}

// Base URL
if (DB_MODE === 'local') {
    define('BASE_URL', './');
} else {
    define('BASE_URL', 'https://sdak.obec.in/');
}


// Auth Configuration
define('TOKEN_EXPIRY', 86400); // 24 hours
define('REFRESH_TOKEN_EXPIRY', 604800); // 7 days
define('TOKEN_SECRET', 'sdak-ks-secret-key-change-this-in-production');

// Google OAuth
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', '');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Timezone
date_default_timezone_set('Asia/Bangkok');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);  // เปลี่ยนจาก 0 เป็น 1 ชั่วคราว

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
