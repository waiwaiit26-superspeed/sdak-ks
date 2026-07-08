-- Migration 023: Allow NULL for email column in users table
-- Fix: registrations without email fail with UNIQUE constraint violation
-- because empty string '' is treated as a duplicate value.
-- Solution: convert email='' to NULL (NULLs are allowed in UNIQUE columns).

-- 1. Change email column to allow NULL
ALTER TABLE `users`
    MODIFY `email` VARCHAR(255) DEFAULT NULL;

-- 2. Convert existing empty-string emails to NULL
UPDATE `users` SET `email` = NULL WHERE `email` = '';

-- 3. Remove test users created during debugging (pending, no real data)
DELETE FROM `users`
WHERE `username` IN ('testdebug_uniqueemail', 'testverify_noemail', 'testdebug999', 'testuser123')
  AND `status` = 'pending'
  AND `role` = 'member';

