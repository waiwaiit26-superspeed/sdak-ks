-- =====================================================================
-- Migration: Fix duplicate receipt numbers and enforce uniqueness
-- Date: 2026-07-14
-- =====================================================================

-- Ensure receipt_number is never empty before dedupe logic
UPDATE receipts
SET receipt_number = CAST(id AS CHAR)
WHERE receipt_number IS NULL OR TRIM(receipt_number) = '';

-- 1) Snapshot max numeric receipt number by book
CREATE TEMPORARY TABLE tmp_receipt_book_max AS
SELECT
    book_number,
    COALESCE(MAX(CAST(receipt_number AS UNSIGNED)), 0) AS max_num
FROM receipts
GROUP BY book_number;

-- 2) Collect duplicate rows (keep the first row of each duplicated number)
CREATE TEMPORARY TABLE tmp_receipt_dup_rows AS
SELECT
    t.id,
    t.book_number,
    ROW_NUMBER() OVER (PARTITION BY t.book_number ORDER BY t.created_at, t.id) AS book_seq
FROM (
    SELECT
        r.id,
        r.book_number,
        r.created_at
    FROM (
        SELECT
            id,
            book_number,
            receipt_number,
            created_at,
            ROW_NUMBER() OVER (
                PARTITION BY book_number, receipt_number
                ORDER BY created_at, id
            ) AS dup_seq
        FROM receipts
    ) r
    WHERE r.dup_seq > 1
) t;

-- 3) Re-number duplicates to next available numbers in each book
UPDATE receipts r
JOIN tmp_receipt_dup_rows d ON d.id = r.id
JOIN tmp_receipt_book_max bm ON bm.book_number = d.book_number
SET r.receipt_number = CAST((bm.max_num + d.book_seq) AS CHAR);

DROP TEMPORARY TABLE IF EXISTS tmp_receipt_dup_rows;
DROP TEMPORARY TABLE IF EXISTS tmp_receipt_book_max;

-- 4) Add unique index for book_number + receipt_number (idempotent)
SET @idx_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'receipts'
      AND INDEX_NAME = 'uq_receipt_book_number'
);

SET @add_idx_sql := IF(
    @idx_exists = 0,
    'ALTER TABLE receipts ADD UNIQUE KEY uq_receipt_book_number (book_number, receipt_number)',
    'SELECT 1'
);

PREPARE add_idx_stmt FROM @add_idx_sql;
EXECUTE add_idx_stmt;
DEALLOCATE PREPARE add_idx_stmt;
