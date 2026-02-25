-- ============================================
-- Migration: Add fee_type to membership_fees + fee_mode settings
-- รองรับ 3 รูปแบบ: none / onetime / annual
-- ============================================

-- เพิ่ม fee_type column (onetime = จ่ายครั้งเดียว, annual = จ่ายรายปี)
ALTER TABLE `membership_fees`
    ADD COLUMN `fee_type` ENUM('onetime','annual') NOT NULL DEFAULT 'annual' AFTER `amount`;

-- เพิ่ม settings สำหรับ fee_mode ของแต่ละประเภทสมาชิก
-- none = ไม่เก็บค่าใช้จ่าย, onetime = จ่ายครั้งเดียว, annual = จ่ายรายปี
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('membership_fee_mode_ordinary', 'none'),
('membership_fee_mode_associate', 'annual'),
('membership_fee_mode_affiliate', 'annual'),
('membership_fee_mode_honorary', 'none')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- เพิ่ม settings สำหรับ membership_fee_ordinary, membership_fee_associate (ถ้ายังไม่มี)
INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('membership_fee_ordinary', '0'),
('membership_fee_associate', '300')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
