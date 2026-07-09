-- Migration 029: Add is_staff flag to users for non-member admin accounts
ALTER TABLE `users`
    ADD COLUMN `is_staff` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT 'บัญชีผู้ดูแลระบบ (ไม่ใช่สมาชิก)'
        AFTER `status`;
