-- Migration 026: Add member_number_confirmed column to users table
-- ยืนยันเลขสมาชิก (admin กด confirm เพื่อระบุว่าเลขถูกต้อง)

SET @sql = IF(
    NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'member_number_confirmed'
    ),
    'ALTER TABLE `users` ADD COLUMN `member_number_confirmed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''ยืนยันเลขสมาชิก: 1=ยืนยันแล้ว'' AFTER `member_number`',
    'SELECT ''Column member_number_confirmed already exists'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
