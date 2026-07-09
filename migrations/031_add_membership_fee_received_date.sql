-- Ensure membership_fees.received_date exists for payment confirmation and receipt date handling
SET @has_col := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'membership_fees'
      AND COLUMN_NAME = 'received_date'
);

SET @sql := IF(
    @has_col = 0,
    'ALTER TABLE membership_fees ADD COLUMN received_date DATE NULL AFTER paid_at',
    'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
