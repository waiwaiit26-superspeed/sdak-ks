-- Migration 016: Add access_code to activities table
-- Allows public viewing of participant lists with a secret code

ALTER TABLE `activities` ADD COLUMN `access_code` VARCHAR(20) DEFAULT NULL AFTER `status`;
