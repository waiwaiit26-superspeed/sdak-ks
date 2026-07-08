-- Migration 025: Create sub_admins table for delegated admin permissions
-- Areas: members | news | activities
-- Permissions per area:
--   members:    view, approve, create, edit, delete
--   news:       create, edit, delete
--   activities: create, edit, delete

CREATE TABLE IF NOT EXISTS `sub_admins` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `user_id`     INT          NOT NULL,
  `area`        VARCHAR(50)  NOT NULL COMMENT 'members | news | activities',
  `permissions` JSON         NOT NULL DEFAULT ('[]'),
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `assigned_by` INT          NOT NULL,
  `note`        TEXT         NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_area` (`user_id`, `area`),
  KEY `idx_area` (`area`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_sa_user`     FOREIGN KEY (`user_id`)     REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sa_assigned` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
