-- ============================================
-- Activity Logs Table (ประวัติการใช้งานระบบ)
-- เก็บ log ทุกการกระทำ: login, crud, approve ฯลฯ
-- ============================================

CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL COMMENT 'ผู้กระทำ',
    `action` VARCHAR(100) NOT NULL COMMENT 'ประเภทการกระทำ เช่น login, create_news, approve_member',
    `module` VARCHAR(50) DEFAULT NULL COMMENT 'โมดูล เช่น auth, member, news, activity, nav, page',
    `target_id` INT DEFAULT NULL COMMENT 'ID ของรายการที่ถูกกระทำ',
    `target_type` VARCHAR(50) DEFAULT NULL COMMENT 'ประเภทเป้าหมาย เช่น user, news, activity',
    `details` TEXT DEFAULT NULL COMMENT 'รายละเอียดเพิ่มเติม',
    `old_value` TEXT DEFAULT NULL COMMENT 'ค่าเดิม (JSON)',
    `new_value` TEXT DEFAULT NULL COMMENT 'ค่าใหม่ (JSON)',
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_action` (`action`),
    INDEX `idx_module` (`module`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Note: admin users should have member_type = NULL
-- They are NOT members of ส.ร.ม.ก.
-- ============================================
ALTER TABLE `users` MODIFY `member_type` ENUM('ordinary','associate','affiliate','honorary') DEFAULT NULL;

-- Set existing admin users member_type to NULL
UPDATE `users` SET `member_type` = NULL WHERE `role` = 'admin';
