-- Migration 012: Fix duplicate finance_categories and duplicate finance_transactions
-- สาเหตุ: INSERT IGNORE ไม่มี UNIQUE constraint → รัน migration ซ้ำได้ → categories ซ้ำ
-- migration 011 JOIN categories ซ้ำ → transactions ซ้ำ

-- Step 1: ลบ finance_transactions ซ้ำ (เก็บ row ที่ category_id ต่ำสุดไว้)
-- transactions ที่มี reference_no ซ้ำ ให้เก็บแค่ row ที่ id ต่ำสุด
DELETE ft1 FROM finance_transactions ft1
INNER JOIN finance_transactions ft2 
    ON ft1.reference_no = ft2.reference_no 
    AND ft1.reference_no IS NOT NULL
    AND ft1.reference_no != ''
    AND ft1.id > ft2.id;

-- Step 2: ย้าย transactions ที่ชี้ไปยัง duplicate category → ชี้ไปที่ original category (id ต่ำสุด)
UPDATE finance_transactions ft
INNER JOIN finance_categories fc_dup ON ft.category_id = fc_dup.id
INNER JOIN (
    SELECT name, type, MIN(id) AS min_id 
    FROM finance_categories 
    GROUP BY name, type
) fc_orig ON fc_dup.name = fc_orig.name AND fc_dup.type = fc_orig.type
SET ft.category_id = fc_orig.min_id
WHERE ft.category_id != fc_orig.min_id;

-- Step 3: ลบ duplicate finance_categories (เก็บ row ที่ id ต่ำสุดไว้)
DELETE fc1 FROM finance_categories fc1
INNER JOIN (
    SELECT name, type, MIN(id) AS min_id 
    FROM finance_categories 
    GROUP BY name, type
) fc2 ON fc1.name = fc2.name AND fc1.type = fc2.type
WHERE fc1.id > fc2.min_id;

-- Step 4: เพิ่ม UNIQUE constraint ป้องกันซ้ำอีก
ALTER TABLE finance_categories ADD UNIQUE KEY uk_name_type (name, type);
