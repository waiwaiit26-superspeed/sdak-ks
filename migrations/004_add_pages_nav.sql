CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(500) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `content` LONGTEXT DEFAULT NULL,
    `cover_image` VARCHAR(500) DEFAULT NULL,
    `meta_description` TEXT DEFAULT NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `created_by` INT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nav_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `url` VARCHAR(500) DEFAULT NULL,
    `page_id` INT DEFAULT NULL,
    `target` ENUM('_self','_blank') NOT NULL DEFAULT '_self',
    `icon` VARCHAR(100) DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `nav_items`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE SET NULL,
    INDEX `idx_parent` (`parent_id`),
    INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `nav_items` (`title`, `url`, `icon`, `sort_order`, `is_active`)
SELECT 'หน้าแรก', './', 'bi-house-door', 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `nav_items` WHERE `title` = 'หน้าแรก' AND `url` = './');

INSERT IGNORE INTO `nav_items` (`title`, `url`, `icon`, `sort_order`, `is_active`)
SELECT 'ข่าวสาร', './web/?page=news', 'bi-newspaper', 2, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `nav_items` WHERE `title` = 'ข่าวสาร' AND `url` = './web/?page=news');

INSERT IGNORE INTO `nav_items` (`title`, `url`, `icon`, `sort_order`, `is_active`)
SELECT 'กิจกรรม', './web/?page=activities', 'bi-calendar-event', 3, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `nav_items` WHERE `title` = 'กิจกรรม' AND `url` = './web/?page=activities');
