-- ============================================
-- Migration 014: Add member_number column to users table
-- Column exists in SDAK but was missing from SAAK
-- ============================================

-- Add member_number column after member_type (if not exists)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'member_number');

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `member_number` VARCHAR(50) DEFAULT NULL COMMENT ''เลขที่สมาชิก'' AFTER `member_type`',
    'SELECT ''Column member_number already exists''');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
