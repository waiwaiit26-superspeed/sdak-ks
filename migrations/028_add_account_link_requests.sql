-- Migration 028: Account Link Requests
-- Stores requests from users who want to link their email/Google account
-- to an existing member record (admin must approve)

CREATE TABLE IF NOT EXISTS account_link_requests (
    id                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    target_user_id         INT UNSIGNED NOT NULL          COMMENT 'existing member to link to',
    request_type           ENUM('email','google') NOT NULL DEFAULT 'email',
    email                  VARCHAR(255) NULL,
    google_id              VARCHAR(255) NULL,
    proposed_username      VARCHAR(100) NULL,
    proposed_password_hash VARCHAR(255) NULL,
    extra_data             TEXT NULL                      COMMENT 'JSON: google_name, google_picture, etc.',
    status                 ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    requested_at           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_by            INT UNSIGNED NULL,
    approved_at            DATETIME NULL,
    note                   TEXT NULL,
    INDEX idx_target (target_user_id),
    INDEX idx_status (status),
    INDEX idx_email (email(80))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
