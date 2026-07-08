-- Migration 027: Allow duplicate member numbers
-- ลบ UNIQUE index บน member_number เพื่ออนุญาตให้เลขสมาชิกซ้ำกันได้
-- (admin สามารถกำหนดเลขสมาชิกเองได้โดยไม่ต้องเป็น unique)

SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND INDEX_NAME   = 'idx_member_number'
);

SET @sql = IF(@idx_exists > 0,
    'ALTER TABLE `users` DROP INDEX `idx_member_number`',
    'SELECT ''Index idx_member_number does not exist, skipping'''
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
