-- Migration 024: Add allowed_member_types column to activities table
-- Comma-separated member types allowed to register; NULL = all types allowed.

ALTER TABLE `activities`
    ADD COLUMN `allowed_member_types` VARCHAR(255) DEFAULT NULL
        COMMENT 'Comma-separated member types allowed, null=all'
    AFTER `show_registrations`;
