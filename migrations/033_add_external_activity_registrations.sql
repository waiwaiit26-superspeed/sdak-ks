-- Migration 033: Support external (non-member) participants in activity registrations
-- Date: 2026-07-16

ALTER TABLE activity_registrations
    MODIFY COLUMN user_id INT NULL,
    ADD COLUMN is_external TINYINT(1) NOT NULL DEFAULT 0 AFTER user_id,
    ADD COLUMN external_prefix VARCHAR(50) NULL AFTER is_external,
    ADD COLUMN external_first_name VARCHAR(150) NULL AFTER external_prefix,
    ADD COLUMN external_last_name VARCHAR(150) NULL AFTER external_first_name,
    ADD COLUMN external_full_name VARCHAR(255) NULL AFTER external_last_name,
    ADD COLUMN external_school_organization VARCHAR(255) NULL AFTER external_full_name,
    ADD COLUMN external_payer_address TEXT NULL AFTER external_school_organization;

CREATE INDEX idx_activity_external ON activity_registrations (activity_id, is_external);
