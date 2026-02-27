-- ============================================
-- Migration 015: Create member_types table
-- ย้ายประเภทสมาชิกจาก hardcode/site_settings ไปเก็บในตารางฐานข้อมูล
-- ============================================

CREATE TABLE IF NOT EXISTS `member_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_key` VARCHAR(50) NOT NULL UNIQUE COMMENT 'ค่า enum เช่น ordinary, associate',
    `label` VARCHAR(100) NOT NULL COMMENT 'ชื่อแสดง เช่น สมาชิกสามัญ',
    `label_short` VARCHAR(50) DEFAULT NULL COMMENT 'ชื่อย่อ เช่น สามัญ',
    `description` VARCHAR(500) DEFAULT NULL COMMENT 'คำอธิบาย เช่น รองผู้อำนวยการ/อดีตรองผู้อำนวยการ',
    `fee_mode` ENUM('none','onetime','annual') NOT NULL DEFAULT 'none' COMMENT 'รูปแบบการเก็บค่าธรรมเนียม',
    `fee_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'จำนวนเงินค่าธรรมเนียม',
    `icon` VARCHAR(50) DEFAULT 'bi-person-fill' COMMENT 'Bootstrap icon class',
    `icon_bg` VARCHAR(20) DEFAULT '#a78bfa' COMMENT 'สีพื้นหลัง icon',
    `icon_color` VARCHAR(20) DEFAULT '#3b0764' COMMENT 'สีตัวอักษร icon',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'ลำดับการแสดงผล',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=เปิดใช้ 0=ปิด',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed ประเภทเริ่มต้น (ดึงค่า fee จาก site_settings ถ้ามี)
INSERT INTO `member_types` (`type_key`, `label`, `label_short`, `description`, `fee_mode`, `fee_amount`, `icon`, `icon_bg`, `icon_color`, `sort_order`, `is_active`)
SELECT 'ordinary', 'สมาชิกสามัญ', 'สามัญ', 'รองผู้อำนวยการ/อดีตรองผู้อำนวยการ',
    COALESCE((SELECT `setting_value` FROM `site_settings` WHERE `setting_key` = 'membership_fee_mode_ordinary'), 'none'),
    COALESCE((SELECT CAST(`setting_value` AS DECIMAL(10,2)) FROM `site_settings` WHERE `setting_key` = 'membership_fee_ordinary'), 0),
    'bi-star-fill', '#fbbf24', '#92400e', 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `member_types` WHERE `type_key` = 'ordinary');

INSERT INTO `member_types` (`type_key`, `label`, `label_short`, `description`, `fee_mode`, `fee_amount`, `icon`, `icon_bg`, `icon_color`, `sort_order`, `is_active`)
SELECT 'associate', 'สมาชิกวิสามัญ', 'วิสามัญ', 'ผู้สนับสนุนสมาคม',
    COALESCE((SELECT `setting_value` FROM `site_settings` WHERE `setting_key` = 'membership_fee_mode_associate'), 'none'),
    COALESCE((SELECT CAST(`setting_value` AS DECIMAL(10,2)) FROM `site_settings` WHERE `setting_key` = 'membership_fee_associate'), 0),
    'bi-people-fill', '#60a5fa', '#1e3a5f', 2, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `member_types` WHERE `type_key` = 'associate');

INSERT INTO `member_types` (`type_key`, `label`, `label_short`, `description`, `fee_mode`, `fee_amount`, `icon`, `icon_bg`, `icon_color`, `sort_order`, `is_active`)
SELECT 'affiliate', 'สมาชิกสมทบ', 'สมทบ', 'สมาชิกทั่วไป',
    COALESCE((SELECT `setting_value` FROM `site_settings` WHERE `setting_key` = 'membership_fee_mode_affiliate'), 'none'),
    COALESCE((SELECT CAST(`setting_value` AS DECIMAL(10,2)) FROM `site_settings` WHERE `setting_key` = 'membership_fee_affiliate'), 0),
    'bi-person-fill', '#a78bfa', '#3b0764', 3, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `member_types` WHERE `type_key` = 'affiliate');

INSERT INTO `member_types` (`type_key`, `label`, `label_short`, `description`, `fee_mode`, `fee_amount`, `icon`, `icon_bg`, `icon_color`, `sort_order`, `is_active`)
SELECT 'honorary', 'สมาชิกกิตติมศักดิ์', 'กิตติมศักดิ์', 'สมาชิกกิตติมศักดิ์',
    COALESCE((SELECT `setting_value` FROM `site_settings` WHERE `setting_key` = 'membership_fee_mode_honorary'), 'none'),
    COALESCE((SELECT CAST(`setting_value` AS DECIMAL(10,2)) FROM `site_settings` WHERE `setting_key` = 'membership_fee_honorary'), 0),
    'bi-award-fill', '#f59e0b', '#78350f', 4, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `member_types` WHERE `type_key` = 'honorary');
