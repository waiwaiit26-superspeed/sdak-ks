-- ============================================
-- Migration: Add membership_fees table + new settings
-- ============================================

-- ค่าธรรมเนียมสมาชิกรายปี
CREATE TABLE IF NOT EXISTS `membership_fees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `year` INT NOT NULL COMMENT 'ปี พ.ศ.',
    `amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending','paid','overdue','waived') NOT NULL DEFAULT 'pending',
    `payment_slip` VARCHAR(500) DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `approved_by` INT DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `note` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_user_year` (`user_id`, `year`),
    INDEX `idx_status` (`status`),
    INDEX `idx_year` (`year`)
) ENGINE=InnoDB;

-- New settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('registration_enabled', '1'),
('membership_fee_affiliate', '200'),
('membership_fee_honorary', '0'),
('bank_name', ''),
('bank_account_name', ''),
('bank_account_number', '')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
