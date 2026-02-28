-- Migration: Add Telegram Bots Management
-- เพิ่มระบบจัดการ Telegram Bots แยกตามประเภท (admin, member)

CREATE TABLE `telegram_bots` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'ชื่อ Bot (เช่น "Admin Bot", "Member Bot")',
    `type` ENUM('admin','member') NOT NULL COMMENT 'ประเภท Bot',
    `bot_token` TEXT NULL COMMENT 'Bot Token จาก BotFather',
    `bot_username` VARCHAR(255) NULL COMMENT 'Username ของ Bot (ไม่มี @)',
    `webhook_url` TEXT NULL COMMENT 'Webhook URL',
    `webhook_secret` VARCHAR(255) NULL COMMENT 'Secret สำหรับตรวจสอบ webhook',
    `chat_id` BIGINT NULL COMMENT 'Chat ID หลัก (สำหรับการแจ้งเตือน)',
    `admin_chat_ids` TEXT NULL COMMENT 'Chat IDs ของ admin (JSON array)',
    `is_active` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'เปิด/ปิดการใช้งาน',
    `description` TEXT NULL COMMENT 'คำอธิบายการใช้งาน',
    `settings` JSON NULL COMMENT 'การตั้งค่าเพิ่มเติม (JSON)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_telegram_bots_type` (`type`),
    KEY `idx_telegram_bots_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางจัดการ Telegram Bots แยกตามประเภท';

-- เพิ่มข้อมูลเริ่มต้น
INSERT INTO `telegram_bots` (`name`, `type`, `description`, `is_active`) VALUES
('Admin Bot', 'admin', 'Bot สำหรับ Admin - การแจ้งเตือนทั่วไป และการอนุมัติ', FALSE),
('Member Bot', 'member', 'Bot สำหรับสมาชิก - การเชื่อมต่อบัญชี และการแจ้งเตือนส่วนตัว', FALSE);