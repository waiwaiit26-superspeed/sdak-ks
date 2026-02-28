-- Migration: Add Telegram Support
-- เพิ่ม telegram_chat_id ในตาราง users
-- สร้างตาราง telegram_link_tokens สำหรับเก็บ one-time tokens

-- เพิ่ม telegram_chat_id ในตาราง users
ALTER TABLE `users` 
ADD COLUMN `telegram_chat_id` BIGINT NULL DEFAULT NULL COMMENT 'Telegram Chat ID ของผู้ใช้' AFTER `profile_image`,
ADD COLUMN `telegram_linked_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'วันที่เชื่อมต่อ Telegram' AFTER `telegram_chat_id`,
ADD UNIQUE KEY `uk_users_telegram_chat_id` (`telegram_chat_id`);

-- สร้างตาราง telegram_link_tokens สำหรับเก็บ one-time tokens
CREATE TABLE `telegram_link_tokens` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL COMMENT 'ID ของผู้ใช้',
    `token` VARCHAR(255) NOT NULL COMMENT 'One-time token สำหรับเชื่อมต่อ',
    `expires_at` TIMESTAMP NOT NULL COMMENT 'วันที่หมดอายุ',
    `used_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'วันที่ใช้ token',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_telegram_tokens_token` (`token`),
    KEY `fk_telegram_tokens_user_id` (`user_id`),
    KEY `idx_telegram_tokens_expires` (`expires_at`),
    CONSTRAINT `fk_telegram_tokens_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตาราง one-time tokens สำหรับเชื่อมต่อ Telegram';