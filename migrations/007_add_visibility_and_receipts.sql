-- =====================================================================
-- Migration: Activity Visibility + Receipt System
-- Date: 2026-02-14
-- =====================================================================

-- 1. Activity Visibility
ALTER TABLE activities
    ADD COLUMN visibility ENUM('public','members_only','custom') NOT NULL DEFAULT 'public' AFTER status,
    ADD COLUMN visibility_text VARCHAR(500) NULL AFTER visibility;

-- 2. Receipts Table
CREATE TABLE IF NOT EXISTS receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_number INT NOT NULL,
    book_number VARCHAR(50) NOT NULL DEFAULT 'ส.ร.ม.ก. 68',
    user_id INT NOT NULL,
    receipt_type ENUM('membership_fee','activity_fee','other') NOT NULL DEFAULT 'membership_fee',
    reference_id INT NULL COMMENT 'FK to membership_fees or activity_registrations',
    title VARCHAR(500) NOT NULL COMMENT 'หัวข้อใบเสร็จ',
    payer_name VARCHAR(255) NOT NULL,
    payer_address TEXT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    amount_text VARCHAR(500) NOT NULL COMMENT 'จำนวนเงินเป็นตัวอักษร',
    received_by VARCHAR(255) NULL COMMENT 'ผู้รับเงิน/เหรัญญิก',
    issued_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Receipt settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
    ('receipt_book_number', 'ส.ร.ม.ก. 68'),
    ('receipt_next_number', '1'),
    ('receipt_treasurer_name', ''),
    ('receipt_organization_name', 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์'),
    ('receipt_organization_address', 'อำเภอเมือง จังหวัดกาฬสินธุ์')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
