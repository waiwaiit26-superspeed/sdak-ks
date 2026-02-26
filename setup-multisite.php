<?php
/**
 * =============================================
 * setup-multisite.php — One-time Multi-Site Setup
 * =============================================
 * รันครั้งเดียวบน server เพื่อ:
 * 1. สร้าง config/sites/*.php files
 * 2. ย้ายรูปเดิมไปที่ uploads/sdak.obec.in/
 * 3. Setup DB obecin_saak (clean schema + admin user)
 * 4. สร้าง upload folders
 *
 * เรียก: https://sdak.obec.in/setup-multisite.php?key=WEBHOOK_SECRET
 * =============================================
 */

$startTime = microtime(true);

// Load config (จะใช้ DB credentials ของ sdak.obec.in)
$_SERVER['SCRIPT_FILENAME'] = 'index.php';

// Load webhook secret
$secretFile = __DIR__ . '/webhook-secret.php';
if (file_exists($secretFile)) {
    require_once $secretFile;
}

// Auth check
$allowedKey = defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : '';
$isCLI = (php_sapi_name() === 'cli');
$validKey = !empty($allowedKey) && ($_GET['key'] ?? '') === $allowedKey;

if (!$isCLI && !$validKey) {
    http_response_code(403);
    die('Forbidden — ต้องใส่ ?key=xxx');
}

header('Content-Type: text/plain; charset=utf-8');

$results = [];

function logResult($step, $msg, $ok = true) {
    global $results;
    $icon = $ok ? '✅' : '❌';
    $line = "{$icon} [{$step}] {$msg}";
    $results[] = $line;
    echo $line . "\n";
    flush();
}

// =============================================
// STEP 1: สร้าง config/sites/*.php
// =============================================
echo "=== STEP 1: สร้าง Site Config Files ===\n";

$sitesDir = __DIR__ . '/config/sites';
if (!is_dir($sitesDir)) {
    mkdir($sitesDir, 0755, true);
    logResult('1.0', "สร้าง config/sites/ directory");
}

// --- sdak.obec.in ---
$sdakConfig = $sitesDir . '/sdak.obec.in.php';
if (!file_exists($sdakConfig)) {
    $content = <<<'PHP'
<?php
if (!defined('SDAK_KS')) { http_response_code(403); exit; }

// --- Site Info ---
define('SITE_NAME', 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์');
define('SITE_NAME_SHORT', 'ส.ร.ม.ก.');
define('SITE_NAME_EN', 'SDAK-KS');

// --- Database ---
define('DB_MODE', 'production');
define('DB_TYPE', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'obecin_sdakks');
define('DB_USER', 'obecin_sdakks');
define('DB_PASS', 'SdakKs@2026');
define('DB_CHARSET', 'utf8mb4');

// --- Google OAuth ---
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', '');

// --- Timezone ---
define('APP_TIMEZONE', 'Asia/Bangkok');
PHP;
    file_put_contents($sdakConfig, $content);
    logResult('1.1', "สร้าง config/sites/sdak.obec.in.php");
} else {
    logResult('1.1', "sdak.obec.in.php มีอยู่แล้ว — ข้าม");
}

// --- saak.obec.in ---
$saakConfig = $sitesDir . '/saak.obec.in.php';
if (!file_exists($saakConfig)) {
    $content = <<<'PHP'
<?php
if (!defined('SDAK_KS')) { http_response_code(403); exit; }

// --- Site Info ---
define('SITE_NAME', 'สมาคม SAAK');
define('SITE_NAME_SHORT', 'SAAK');
define('SITE_NAME_EN', 'SAAK');

// --- Database ---
define('DB_MODE', 'production');
define('DB_TYPE', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'obecin_saak');
define('DB_USER', 'obecin_saak');
define('DB_PASS', 'Saak@2026');
define('DB_CHARSET', 'utf8mb4');

// --- Google OAuth ---
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', '');

// --- Timezone ---
define('APP_TIMEZONE', 'Asia/Bangkok');
PHP;
    file_put_contents($saakConfig, $content);
    logResult('1.2', "สร้าง config/sites/saak.obec.in.php");
} else {
    logResult('1.2', "saak.obec.in.php มีอยู่แล้ว — ข้าม");
}

// --- index.php (prevent directory listing) ---
$indexFile = $sitesDir . '/index.php';
if (!file_exists($indexFile)) {
    file_put_contents($indexFile, "<?php http_response_code(403); exit('Forbidden');\n");
    logResult('1.3', "สร้าง config/sites/index.php");
}

// =============================================
// STEP 2: ย้ายรูปเดิมไปที่ uploads/sdak.obec.in/
// =============================================
echo "\n=== STEP 2: ย้ายรูปเดิมไปที่ uploads/sdak.obec.in/ ===\n";

$uploadsBase = __DIR__ . '/uploads';
$sdakUploads = $uploadsBase . '/sdak.obec.in';
$subdirs = ['profiles', 'logos', 'news', 'activities', 'finance', 'payment_slips', 'payments', 'slips'];

// สร้าง folder sdak.obec.in ก่อน
foreach ($subdirs as $sub) {
    $dir = $sdakUploads . '/' . $sub;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
logResult('2.0', "สร้าง uploads/sdak.obec.in/ subdirectories");

// ย้ายไฟล์จาก uploads/{subfolder}/ → uploads/sdak.obec.in/{subfolder}/
$movedCount = 0;
$skippedCount = 0;

foreach ($subdirs as $sub) {
    $oldDir = $uploadsBase . '/' . $sub;
    $newDir = $sdakUploads . '/' . $sub;

    if (!is_dir($oldDir)) continue;

    $files = scandir($oldDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '.gitkeep') continue;
        $oldPath = $oldDir . '/' . $file;
        $newPath = $newDir . '/' . $file;

        if (is_file($oldPath)) {
            if (file_exists($newPath)) {
                $skippedCount++;
                continue;
            }
            if (copy($oldPath, $newPath)) {
                $movedCount++;
            }
        }
    }
}

logResult('2.1', "Copy ไฟล์จาก uploads/ → uploads/sdak.obec.in/: {$movedCount} files copied, {$skippedCount} skipped");

// สร้าง folder saak.obec.in ด้วย
$saakUploads = $uploadsBase . '/saak.obec.in';
foreach ($subdirs as $sub) {
    $dir = $saakUploads . '/' . $sub;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
logResult('2.2', "สร้าง uploads/saak.obec.in/ subdirectories");

// =============================================
// STEP 3: อัปเดต DB sdak ให้ upload path ชี้ไปที่ domain folder
// =============================================
echo "\n=== STEP 3: อัปเดต upload paths ใน DB sdak ===\n";

try {
    $sdakPdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=obecin_sdakks;charset=utf8mb4',
        'obecin_sdakks', 'SdakKs@2026',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // อัปเดต profile_image paths ใน users
    $stmt = $sdakPdo->exec("
        UPDATE users SET profile_image = REPLACE(profile_image, 'uploads/profiles/', 'uploads/sdak.obec.in/profiles/')
        WHERE profile_image LIKE 'uploads/profiles/%'
    ");
    logResult('3.1', "อัปเดต users.profile_image: {$stmt} rows");

    // อัปเดต cover_image ใน news
    $stmt = $sdakPdo->exec("
        UPDATE news SET cover_image = REPLACE(cover_image, 'uploads/news/', 'uploads/sdak.obec.in/news/')
        WHERE cover_image LIKE 'uploads/news/%'
    ");
    logResult('3.2', "อัปเดต news.cover_image: {$stmt} rows");

    // อัปเดต cover_image ใน activities
    $stmt = $sdakPdo->exec("
        UPDATE activities SET cover_image = REPLACE(cover_image, 'uploads/activities/', 'uploads/sdak.obec.in/activities/')
        WHERE cover_image LIKE 'uploads/activities/%'
    ");
    logResult('3.3', "อัปเดต activities.cover_image: {$stmt} rows");

    // อัปเดต payment_slip ใน membership_fees
    $stmt = $sdakPdo->exec("
        UPDATE membership_fees SET payment_slip = REPLACE(payment_slip, 'uploads/payment_slips/', 'uploads/sdak.obec.in/payment_slips/')
        WHERE payment_slip LIKE 'uploads/payment_slips/%'
    ");
    $stmt2 = $sdakPdo->exec("
        UPDATE membership_fees SET payment_slip = REPLACE(payment_slip, 'uploads/payments/', 'uploads/sdak.obec.in/payments/')
        WHERE payment_slip LIKE 'uploads/payments/%'
    ");
    $stmt3 = $sdakPdo->exec("
        UPDATE membership_fees SET payment_slip = REPLACE(payment_slip, 'uploads/slips/', 'uploads/sdak.obec.in/slips/')
        WHERE payment_slip LIKE 'uploads/slips/%'
    ");
    logResult('3.4', "อัปเดต membership_fees.payment_slip");

    // อัปเดต payment_proof ใน activity_registrations
    $stmt = $sdakPdo->exec("
        UPDATE activity_registrations SET payment_proof = REPLACE(payment_proof, 'uploads/', 'uploads/sdak.obec.in/')
        WHERE payment_proof LIKE 'uploads/%' AND payment_proof NOT LIKE 'uploads/sdak.obec.in/%'
    ");
    logResult('3.5', "อัปเดต activity_registrations.payment_proof: {$stmt} rows");

    // อัปเดต attachment ใน finance_transactions
    $stmt = $sdakPdo->exec("
        UPDATE finance_transactions SET attachment = REPLACE(attachment, 'uploads/', 'uploads/sdak.obec.in/')
        WHERE attachment LIKE 'uploads/%' AND attachment NOT LIKE 'uploads/sdak.obec.in/%'
    ");
    logResult('3.6', "อัปเดต finance_transactions.attachment: {$stmt} rows");

    // อัปเดต site_settings สำหรับ logo
    $stmt = $sdakPdo->exec("
        UPDATE site_settings SET setting_value = REPLACE(setting_value, 'uploads/logos/', 'uploads/sdak.obec.in/logos/')
        WHERE setting_key IN ('site_logo', 'receipt_logo') AND setting_value LIKE 'uploads/logos/%'
    ");
    logResult('3.7', "อัปเดต site_settings logo: {$stmt} rows");

    // อัปเดต cover_image ใน pages
    $stmt = $sdakPdo->exec("
        UPDATE pages SET cover_image = REPLACE(cover_image, 'uploads/', 'uploads/sdak.obec.in/')
        WHERE cover_image LIKE 'uploads/%' AND cover_image NOT LIKE 'uploads/sdak.obec.in/%'
    ");
    logResult('3.8', "อัปเดต pages.cover_image: {$stmt} rows");

} catch (PDOException $e) {
    logResult('3.X', "DB sdak error: " . $e->getMessage(), false);
}

// =============================================
// STEP 4: Setup DB obecin_saak (clean schema)
// =============================================
echo "\n=== STEP 4: Setup DB obecin_saak ===\n";

try {
    $saakPdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=obecin_saak;charset=utf8mb4',
        'obecin_saak', 'Saak@2026',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => true]
    );

    // ตรวจว่ามีตาราง users หรือยัง (ถ้ามีแล้ว = setup รันไปแล้ว)
    $tableCheck = $saakPdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    if ($tableCheck > 0) {
        logResult('4.0', "DB obecin_saak มี tables อยู่แล้ว — ข้ามการสร้าง schema");
    } else {
        // สร้าง schema ทั้งหมด (ไม่รวม CREATE DATABASE / USE)
        $schema = <<<'SQL'

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) DEFAULT NULL,
    `google_id` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('admin','member') NOT NULL DEFAULT 'member',
    `member_type` ENUM('ordinary','associate','affiliate','honorary') DEFAULT NULL,
    `status` ENUM('pending','active','cancelled','suspended') NOT NULL DEFAULT 'pending',
    `prefix` VARCHAR(50) DEFAULT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `school_organization` VARCHAR(255) DEFAULT NULL,
    `position` VARCHAR(255) DEFAULT NULL,
    `profile_image` VARCHAR(500) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `national_id` VARCHAR(20) DEFAULT NULL,
    `first_name` VARCHAR(100) DEFAULT NULL,
    `last_name` VARCHAR(100) DEFAULT NULL,
    `birth_date` DATE DEFAULT NULL,
    `home_address` JSON DEFAULT NULL,
    `work_address` JSON DEFAULT NULL,
    `education_area` VARCHAR(100) DEFAULT NULL,
    `region` VARCHAR(50) DEFAULT NULL,
    `work_phone` VARCHAR(20) DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    `cancel_reason` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_member_type` (`member_type`),
    INDEX `idx_role` (`role`),
    INDEX `idx_google_id` (`google_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auth Tokens
CREATE TABLE IF NOT EXISTS `auth_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(500) NOT NULL UNIQUE,
    `refresh_token` VARCHAR(500) DEFAULT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- News
CREATE TABLE IF NOT EXISTS `news` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(500) DEFAULT NULL,
    `excerpt` TEXT DEFAULT NULL,
    `content` LONGTEXT NOT NULL,
    `cover_image` VARCHAR(500) DEFAULT NULL,
    `author_id` INT NOT NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `published_at` DATETIME DEFAULT NULL,
    `views` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_status` (`status`),
    INDEX `idx_published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activities
CREATE TABLE IF NOT EXISTS `activities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `cover_image` VARCHAR(500) DEFAULT NULL,
    `location` VARCHAR(500) DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `max_participants` INT DEFAULT NULL,
    `has_fee` TINYINT(1) NOT NULL DEFAULT 0,
    `fee_amount` DECIMAL(10,2) DEFAULT 0.00,
    `fee_description` TEXT DEFAULT NULL,
    `registration_open` TINYINT(1) NOT NULL DEFAULT 1,
    `require_approval` TINYINT(1) NOT NULL DEFAULT 1,
    `status` ENUM('draft','open','closed','cancelled') NOT NULL DEFAULT 'draft',
    `visibility` ENUM('public','members_only','custom') NOT NULL DEFAULT 'public',
    `visibility_text` VARCHAR(500) DEFAULT NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_status` (`status`),
    INDEX `idx_start_date` (`start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Registrations
CREATE TABLE IF NOT EXISTS `activity_registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `activity_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    `payment_status` ENUM('not_required','pending','paid','refunded') NOT NULL DEFAULT 'not_required',
    `payment_proof` VARCHAR(500) DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_registration` (`activity_id`, `user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Member Statistics
CREATE TABLE IF NOT EXISTS `member_statistics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `action` ENUM('registered','approved','cancelled','suspended','reactivated','type_changed','profile_updated') NOT NULL,
    `old_value` TEXT DEFAULT NULL,
    `new_value` TEXT DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `performed_by` INT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `target_id` INT DEFAULT NULL,
    `target_type` VARCHAR(50) DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `old_value` TEXT DEFAULT NULL,
    `new_value` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_module` (`module`),
    INDEX `idx_action` (`action`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Resets
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(128) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`),
    INDEX `idx_user_expires` (`user_id`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pages
CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `content` LONGTEXT DEFAULT NULL,
    `cover_image` VARCHAR(500) DEFAULT NULL,
    `meta_description` TEXT DEFAULT NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `created_by` INT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Nav Items
CREATE TABLE IF NOT EXISTS `nav_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `url` VARCHAR(500) DEFAULT NULL,
    `page_id` INT DEFAULT NULL,
    `target` ENUM('_self','_blank') NOT NULL DEFAULT '_self',
    `icon` VARCHAR(100) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `nav_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE SET NULL,
    INDEX `idx_parent` (`parent_id`),
    INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membership Fees
CREATE TABLE IF NOT EXISTS `membership_fees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `year` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `fee_type` ENUM('onetime','annual') NOT NULL DEFAULT 'annual',
    `status` ENUM('pending','paid','overdue','waived') NOT NULL DEFAULT 'pending',
    `payment_slip` VARCHAR(500) DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `received_date` DATE DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_year` (`user_id`, `year`),
    INDEX `idx_status` (`status`),
    INDEX `idx_year` (`year`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Receipts
CREATE TABLE IF NOT EXISTS `receipts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `receipt_type` ENUM('membership_fee','activity_fee','other') NOT NULL DEFAULT 'other',
    `reference_id` INT DEFAULT NULL,
    `book_number` VARCHAR(50) NOT NULL DEFAULT '1',
    `receipt_number` VARCHAR(50) NOT NULL,
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `payer_name` VARCHAR(300) NOT NULL,
    `payer_address` TEXT DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `amount_text` VARCHAR(500) DEFAULT NULL,
    `received_by` VARCHAR(300) DEFAULT NULL,
    `issued_date` DATE NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`receipt_type`),
    INDEX `idx_reference` (`receipt_type`, `reference_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Finance Categories
CREATE TABLE IF NOT EXISTS `finance_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL DEFAULT 'income',
    `description` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_name_type` (`name`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Finance Transactions
CREATE TABLE IF NOT EXISTS `finance_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT NULL,
    `amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `transaction_date` DATE NOT NULL,
    `reference_no` VARCHAR(100) NULL,
    `attachment` VARCHAR(500) NULL,
    `created_by` INT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
    `approved_by` INT NULL,
    `approved_at` DATETIME NULL,
    `note` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `finance_categories`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX idx_type (`type`),
    INDEX idx_transaction_date (`transaction_date`),
    INDEX idx_status (`status`),
    INDEX idx_category (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Finance Managers
CREATE TABLE IF NOT EXISTS `finance_managers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `assigned_by` INT NOT NULL,
    `permissions` JSON NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrations tracking
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `filename` VARCHAR(255) NOT NULL,
    `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SQL;

        // Execute each statement separately
        $statements = array_filter(
            array_map('trim', explode(';', $schema)),
            fn($s) => !empty($s) && $s !== ''
        );

        $executed = 0;
        $errors = 0;
        foreach ($statements as $sql) {
            try {
                $saakPdo->exec($sql);
                $executed++;
            } catch (PDOException $e) {
                logResult('4.S', "SQL Error: " . $e->getMessage() . " | SQL: " . substr($sql, 0, 80), false);
                $errors++;
            }
        }
        logResult('4.1', "สร้าง schema: {$executed} statements, {$errors} errors");

        // Insert default admin user (password: admin123)
        try {
            $saakPdo->exec("
                INSERT INTO `users` (`username`, `email`, `password`, `role`, `status`, `full_name`, `position`)
                VALUES ('admin', 'admin@saak.obec.in', '\$2y\$12\$rRLf3O8pGO2k3qzt3l6Cl.ZmxaRxUijJueVOYNvVQuZhQBM/P7lYG', 'admin', 'active', 'ผู้ดูแลระบบ', 'ผู้ดูแลระบบ')
            ");
            logResult('4.2', "สร้าง admin user (admin / admin123)");
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                logResult('4.2', "admin user มีอยู่แล้ว — ข้าม");
            } else {
                logResult('4.2', "Error creating admin: " . $e->getMessage(), false);
            }
        }

        // Insert default site settings
        try {
            $saakPdo->exec("
                INSERT IGNORE INTO `site_settings` (`setting_key`, `setting_value`) VALUES
                ('site_name', 'สมาคม SAAK'),
                ('site_name_short', 'SAAK'),
                ('site_name_en', 'SAAK'),
                ('site_description', 'สมาคม SAAK'),
                ('contact_email', 'contact@saak.obec.in'),
                ('contact_phone', ''),
                ('contact_address', ''),
                ('google_client_id', ''),
                ('google_client_secret', ''),
                ('registration_enabled', '1'),
                ('membership_fee_ordinary', '0'),
                ('membership_fee_associate', '0'),
                ('membership_fee_affiliate', '200'),
                ('membership_fee_honorary', '0'),
                ('membership_fee_mode_ordinary', 'none'),
                ('membership_fee_mode_associate', 'annual'),
                ('membership_fee_mode_affiliate', 'annual'),
                ('membership_fee_mode_honorary', 'none'),
                ('bank_name', ''),
                ('bank_account_name', ''),
                ('bank_account_number', ''),
                ('receipt_book_number', '1'),
                ('receipt_next_number', '1'),
                ('receipt_treasurer_name', ''),
                ('receipt_organization_name', 'สมาคม SAAK'),
                ('receipt_organization_address', '')
            ");
            logResult('4.3', "สร้าง site_settings defaults สำหรับ SAAK");
        } catch (PDOException $e) {
            logResult('4.3', "Error: " . $e->getMessage(), false);
        }

        // Insert default nav items
        try {
            $saakPdo->exec("
                INSERT INTO `nav_items` (`title`, `url`, `icon`, `sort_order`, `is_active`) VALUES
                ('หน้าแรก', './', 'bi-house-door', 1, 1),
                ('ข่าวสาร', './web/?page=news', 'bi-newspaper', 2, 1),
                ('กิจกรรม', './web/?page=activities', 'bi-calendar-event', 3, 1)
            ");
            logResult('4.4', "สร้าง nav_items defaults");
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                logResult('4.4', "nav_items มีอยู่แล้ว — ข้าม");
            } else {
                logResult('4.4', "Error: " . $e->getMessage(), false);
            }
        }

        // Insert default finance categories
        try {
            $saakPdo->exec("
                INSERT IGNORE INTO finance_categories (name, type, description, sort_order) VALUES
                ('ค่าธรรมเนียมสมาชิก', 'income', 'เงินค่าธรรมเนียมจากสมาชิก', 1),
                ('เงินบริจาค', 'income', 'เงินบริจาคจากบุคคลภายนอกหรือองค์กร', 2),
                ('รายรับจากกิจกรรม', 'income', 'รายรับจากการจัดกิจกรรมต่างๆ', 3),
                ('เงินสนับสนุน', 'income', 'เงินสนับสนุนจากหน่วยงาน', 4),
                ('รายรับอื่นๆ', 'income', 'รายรับอื่นๆ', 5),
                ('ค่าจัดกิจกรรม', 'expense', 'ค่าใช้จ่ายในการจัดกิจกรรมต่างๆ', 6),
                ('ค่าเดินทาง', 'expense', 'ค่าเดินทางและค่าพาหนะ', 7),
                ('ค่าอาหารและเครื่องดื่ม', 'expense', 'ค่าอาหารและเครื่องดื่ม', 8),
                ('ค่าวัสดุอุปกรณ์', 'expense', 'ค่าวัสดุสิ้นเปลือง', 9),
                ('ค่าสาธารณูปโภค', 'expense', 'ค่าน้ำ ค่าไฟ ค่าอินเทอร์เน็ต', 10),
                ('เงินสวัสดิการ', 'expense', 'เงินช่วยเหลือสวัสดิการสมาชิก', 11),
                ('รายจ่ายอื่นๆ', 'expense', 'รายจ่ายอื่นๆ', 12)
            ");
            logResult('4.5', "สร้าง finance_categories defaults");
        } catch (PDOException $e) {
            logResult('4.5', "Error: " . $e->getMessage(), false);
        }

        // Mark all migrations as executed (since schema is fresh)
        try {
            $migrationFiles = [
                '001_initial_schema.sql',
                '002_migrate_members.sql',
                '003_add_password_resets.sql',
                '004_add_pages_nav.sql',
                '005_add_membership_fees.sql',
                '006_add_fee_mode.sql',
                '007_add_visibility_and_receipts.sql',
                '008_add_activity_logs.sql',
                '009_finance_tables.sql',
                '010_fix_duplicate_nav_items.sql',
                '011_backfill_fee_transactions.sql',
                '012_fix_duplicate_categories.sql',
            ];
            $stmt = $saakPdo->prepare("INSERT IGNORE INTO migrations (filename) VALUES (?)");
            foreach ($migrationFiles as $mf) {
                $stmt->execute([$mf]);
            }
            logResult('4.6', "บันทึก migrations tracking: " . count($migrationFiles) . " entries");
        } catch (PDOException $e) {
            logResult('4.6', "Error: " . $e->getMessage(), false);
        }
    }

} catch (PDOException $e) {
    logResult('4.X', "DB saak connection error: " . $e->getMessage(), false);
}

// =============================================
// DONE
// =============================================
$elapsed = round(microtime(true) - $startTime, 2);
echo "\n========================================\n";
echo "Setup completed in {$elapsed}s\n";
echo "Results: " . count($results) . " steps\n";
echo "========================================\n";
echo "\n⚠️  สิ่งที่ต้องทำเพิ่ม:\n";
echo "1. ไปที่ https://saak.obec.in/admin/ แล้ว login ด้วย admin / admin123\n";
echo "2. ตั้งค่าข้อมูลสมาคมใน 'ตั้งค่าเว็บไซต์'\n";
echo "3. เปลี่ยนรหัสผ่าน admin\n";
echo "\n🗑️  ลบไฟล์นี้ได้หลังใช้งาน: setup-multisite.php\n";
