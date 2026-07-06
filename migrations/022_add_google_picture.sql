-- ============================================
-- 022: Add google_picture column to users table
-- เก็บ URL รูปโปรไฟล์จาก Google แยกจาก profile_image ที่ user upload เอง
-- ============================================

ALTER TABLE `users`
    ADD COLUMN `google_picture` VARCHAR(500) DEFAULT NULL
        COMMENT 'Google Profile Picture URL (auto-synced on login)' AFTER `profile_image`;

-- ย้าย Google URL ที่อยู่ใน profile_image ไปที่ google_picture
-- แล้ว clear profile_image ให้เป็น NULL เพื่อสงวนไว้สำหรับ user-uploaded เท่านั้น
UPDATE `users`
SET `google_picture` = `profile_image`,
    `profile_image`  = NULL
WHERE `profile_image` IS NOT NULL
  AND (`profile_image` LIKE 'https://lh3.googleusercontent.com/%'
    OR `profile_image` LIKE 'https://googleusercontent.com/%'
    OR `profile_image` LIKE '%googleusercontent%');
