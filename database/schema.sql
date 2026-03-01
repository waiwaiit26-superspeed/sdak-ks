-- ============================================
-- SDAK-KS Database Schema
-- สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์
-- ============================================

CREATE DATABASE IF NOT EXISTS `sdak_ks` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sdak_ks`;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) DEFAULT NULL,
    `google_id` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('admin','member') NOT NULL DEFAULT 'member',
    `member_type` ENUM('ordinary','associate','affiliate','honorary') DEFAULT NULL,
    `status` ENUM('pending','active','cancelled','suspended') NOT NULL DEFAULT 'pending',
    `prefix` VARCHAR(50) DEFAULT NULL COMMENT 'คำนำหน้าชื่อ',
    `full_name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `school_organization` VARCHAR(255) DEFAULT NULL COMMENT 'โรงเรียน/หน่วยงาน',
    `position` VARCHAR(255) DEFAULT NULL COMMENT 'ตำแหน่ง',
    `academic_rank` VARCHAR(100) DEFAULT NULL COMMENT 'วิทยฐานะ',
    `profile_image` VARCHAR(500) DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `national_id` VARCHAR(20) DEFAULT NULL COMMENT 'เลขบัตรประชาชน',
    `first_name` VARCHAR(100) DEFAULT NULL COMMENT 'ชื่อ',
    `last_name` VARCHAR(100) DEFAULT NULL COMMENT 'นามสกุล',
    `birth_date` DATE DEFAULT NULL COMMENT 'วันเกิด',
    `home_address` JSON DEFAULT NULL COMMENT 'ที่อยู่ปัจจุบัน',
    `work_address` JSON DEFAULT NULL COMMENT 'ที่อยู่สถานที่ทำงาน',
    `education_area` VARCHAR(100) DEFAULT NULL COMMENT 'สังกัดเขตพื้นที่',
    `region` VARCHAR(50) DEFAULT NULL COMMENT 'ภาค',
    `work_phone` VARCHAR(20) DEFAULT NULL COMMENT 'โทรศัพท์ที่ทำงาน',
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
) ENGINE=InnoDB;

-- ============================================
-- Auth Tokens Table
-- ============================================
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
) ENGINE=InnoDB;

-- ============================================
-- News Table
-- ============================================
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
) ENGINE=InnoDB;

-- ============================================
-- Activities Table
-- ============================================
CREATE TABLE IF NOT EXISTS `activities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `cover_image` VARCHAR(500) DEFAULT NULL,
    `location` VARCHAR(500) DEFAULT NULL,
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME DEFAULT NULL,
    `max_participants` INT DEFAULT NULL COMMENT 'NULL = ไม่จำกัด',
    `has_fee` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=ไม่เก็บเงิน, 1=เก็บเงิน',
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
) ENGINE=InnoDB;

-- ============================================
-- Activity Registrations Table
-- ============================================
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
) ENGINE=InnoDB;

-- ============================================
-- Member Statistics / Audit Log Table
-- ============================================
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
) ENGINE=InnoDB;

-- ============================================
-- Site Settings Table
-- ============================================
CREATE TABLE IF NOT EXISTS `site_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Activity Logs (system-wide action tracking)
-- ============================================
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
) ENGINE=InnoDB;

-- ============================================
-- Default Admin User (password: admin123)
-- ============================================
INSERT INTO `users` (`username`, `email`, `password`, `role`, `member_type`, `status`, `full_name`, `school_organization`, `position`)
VALUES ('admin', 'admin@sdak-ks.org', '$2y$12$rRLf3O8pGO2k3qzt3l6Cl.ZmxaRxUijJueVOYNvVQuZhQBM/P7lYG', 'admin', NULL, 'active', 'ผู้ดูแลระบบ', 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์', 'ผู้ดูแลระบบ');

-- ============================================
-- Default Site Settings
-- ============================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์'),
('site_name_short', 'ส.ร.ม.ก.'),
('site_name_en', 'SDAK-KS'),
('site_description', 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์ (ส.ร.ม.ก.)'),
('contact_email', 'contact@sdak-ks.org'),
('contact_phone', ''),
('contact_address', 'จังหวัดกาฬสินธุ์'),
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
('receipt_organization_name', ''),
('receipt_organization_address', '');

-- ============================================
-- Membership Fees (ค่าธรรมเนียมสมาชิกรายปี)
-- ============================================
CREATE TABLE IF NOT EXISTS `membership_fees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `year` INT NOT NULL COMMENT 'ปี พ.ศ.',
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `fee_type` ENUM('onetime','annual') NOT NULL DEFAULT 'annual' COMMENT 'onetime=จ่ายครั้งเดียว, annual=จ่ายรายปี',
    `status` ENUM('pending','paid','overdue','waived') NOT NULL DEFAULT 'pending',
    `payment_slip` VARCHAR(500) DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_year` (`user_id`, `year`),
    INDEX `idx_status` (`status`),
    INDEX `idx_year` (`year`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- Receipts (ใบเสร็จรับเงิน)
-- ============================================
CREATE TABLE IF NOT EXISTS `receipts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `receipt_type` ENUM('membership_fee','activity_fee','other') NOT NULL DEFAULT 'other',
    `reference_id` INT DEFAULT NULL COMMENT 'FK to fees/registrations',
    `book_number` VARCHAR(50) NOT NULL DEFAULT '1',
    `receipt_number` VARCHAR(50) NOT NULL,
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `payer_name` VARCHAR(300) NOT NULL,
    `payer_address` TEXT DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `amount_text` VARCHAR(500) DEFAULT NULL COMMENT 'จำนวนเงินเป็นตัวอักษร',
    `received_by` VARCHAR(300) DEFAULT NULL,
    `issued_date` DATE NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`receipt_type`),
    INDEX `idx_reference` (`receipt_type`, `reference_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
