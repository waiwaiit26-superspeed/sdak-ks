-- Migration: Create telegram_message_logs table
-- ตารางเก็บประวัติการส่งข้อความ Telegram จาก admin

CREATE TABLE IF NOT EXISTS `telegram_message_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT UNSIGNED NOT NULL COMMENT 'ID ของ admin ที่ส่ง',
    `message_type` ENUM('text', 'photo', 'document', 'video') NOT NULL DEFAULT 'text',
    `recipient_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'จำนวนผู้รับทั้งหมด',
    `success_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ส่งสำเร็จ',
    `fail_count` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ส่งล้มเหลว',
    `message_preview` TEXT NULL COMMENT 'ตัวอย่างข้อความที่ส่ง',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_admin_id` (`admin_id`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_tg_msg_log_admin` FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
