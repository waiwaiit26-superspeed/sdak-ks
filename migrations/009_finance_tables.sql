-- ===================================================
-- Finance Management System Tables
-- ===================================================

-- ประเภทรายรับ/รายจ่าย
CREATE TABLE IF NOT EXISTS finance_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('income', 'expense') NOT NULL DEFAULT 'income' COMMENT 'income=รายรับ, expense=รายจ่าย',
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- รายการเงินรับเข้า/จ่ายออก
CREATE TABLE IF NOT EXISTS finance_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL COMMENT 'income=รายรับ, expense=รายจ่าย',
    title VARCHAR(500) NOT NULL,
    description TEXT NULL,
    amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    transaction_date DATE NOT NULL,
    reference_no VARCHAR(100) NULL,
    attachment VARCHAR(500) NULL COMMENT 'รูปหลักฐาน/เอกสารแนบ',
    created_by INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES finance_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_status (status),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ผู้จัดการการเงิน (มอบสิทธิ์)
CREATE TABLE IF NOT EXISTS finance_managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    assigned_by INT NOT NULL,
    permissions JSON NULL COMMENT 'สิทธิ์ที่ได้รับ',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT IGNORE INTO finance_categories (name, type, description, sort_order) VALUES
('ค่าธรรมเนียมสมาชิก', 'income', 'เงินค่าธรรมเนียมจากสมาชิก', 1),
('เงินบริจาค', 'income', 'เงินบริจาคจากบุคคลภายนอกหรือองค์กร', 2),
('รายรับจากกิจกรรม', 'income', 'รายรับจากการจัดกิจกรรมต่างๆ', 3),
('เงินสนับสนุน', 'income', 'เงินสนับสนุนจากหน่วยงาน', 4),
('รายรับอื่นๆ', 'income', 'รายรับอื่นๆ ที่ไม่จัดอยู่ในหมวดหมู่ข้างต้น', 5),
('ค่าจัดกิจกรรม', 'expense', 'ค่าใช้จ่ายในการจัดกิจกรรมต่างๆ', 6),
('ค่าเดินทาง', 'expense', 'ค่าเดินทางและค่าพาหนะ', 7),
('ค่าอาหารและเครื่องดื่ม', 'expense', 'ค่าอาหารและเครื่องดื่มสำหรับการประชุม/กิจกรรม', 8),
('ค่าวัสดุอุปกรณ์', 'expense', 'ค่าวัสดุสิ้นเปลืองและอุปกรณ์สำนักงาน', 9),
('ค่าสาธารณูปโภค', 'expense', 'ค่าน้ำ ค่าไฟ ค่าอินเทอร์เน็ต ค่าโทรศัพท์', 10),
('เงินสวัสดิการ', 'expense', 'เงินช่วยเหลือสวัสดิการสมาชิก', 11),
('รายจ่ายอื่นๆ', 'expense', 'รายจ่ายอื่นๆ ที่ไม่จัดอยู่ในหมวดหมู่ข้างต้น', 12);
