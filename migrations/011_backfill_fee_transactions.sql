-- Migration 011: Backfill finance_transactions for paid membership fees
-- ที่ผ่านมา MemberController::confirmFeePayment() สร้างแค่ receipts ไม่ได้สร้าง finance_transactions
-- migration นี้เพิ่มรายการที่ขาดหายไป

INSERT INTO finance_transactions (category_id, type, title, description, amount, transaction_date, reference_no, created_by, status, created_at, updated_at)
SELECT 
    fc.id AS category_id,
    'income' AS type,
    CONCAT('ค่าธรรมเนียมสมาชิก: ', u.full_name, 
        CASE u.member_type 
            WHEN 'ordinary' THEN ' (สามัญ)' 
            WHEN 'associate' THEN ' (วิสามัญ)' 
            WHEN 'affiliate' THEN ' (สมทบ)' 
            WHEN 'honorary' THEN ' (กิตติมศักดิ์)' 
            ELSE '' 
        END
    ) AS title,
    CONCAT('ค่าธรรมเนียมสมาชิก (', IF(mf.fee_type='onetime','ครั้งเดียว', CONCAT('ปี ', mf.year)), ')') AS description,
    mf.amount,
    COALESCE(mf.received_date, DATE(mf.approved_at), CURDATE()) AS transaction_date,
    CONCAT('FEE-', mf.id) AS reference_no,
    COALESCE(mf.approved_by, 1) AS created_by,
    'approved' AS status,
    COALESCE(mf.approved_at, NOW()) AS created_at,
    COALESCE(mf.approved_at, NOW()) AS updated_at
FROM membership_fees mf
JOIN users u ON mf.user_id = u.id
JOIN finance_categories fc ON fc.name = 'ค่าธรรมเนียมสมาชิก' AND fc.type = 'income'
LEFT JOIN finance_transactions ft ON ft.reference_no = CONCAT('FEE-', mf.id)
WHERE mf.status = 'paid' AND ft.id IS NULL;
