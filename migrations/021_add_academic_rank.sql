-- ============================================
-- 021: Add academic_rank (วิทยฐานะ) column to users table
-- ============================================

ALTER TABLE `users`
    ADD COLUMN `academic_rank` VARCHAR(100) DEFAULT NULL COMMENT 'วิทยฐานะ' AFTER `position`;
