-- Migration 013: เพิ่มคอลัมน์ alias ใน nav_items (ถ้ายังไม่มี)
-- จำเป็นสำหรับ saak DB ที่สร้างจาก setup-multisite.php
-- สำหรับ sdak ที่มี alias อยู่แล้ว จะ error "Duplicate column" → migrate.php จะ catch ได้

ALTER TABLE `nav_items` ADD COLUMN `alias` VARCHAR(100) DEFAULT NULL AFTER `url`;
