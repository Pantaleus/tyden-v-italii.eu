-- Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table (SMTP, TinyMCE key, active theme, etc.)
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(100) PRIMARY KEY,
    `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configurations
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
('active_theme', 'warm_mediterranean'), -- Options: 'warm_mediterranean' or 'italian_tricolore'
('tinymce_api_key', 'no-api-key'),
('smtp_host', 'localhost'),
('smtp_port', '587'),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_from_email', 'info@tyden-v-italii.eu'),
('smtp_from_name', 'Týden v Itálii');

-- Trips Table
CREATE TABLE IF NOT EXISTS `trips` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `cover_image` VARCHAR(255) NULL,
    `is_active` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trip Translations
CREATE TABLE IF NOT EXISTS `trip_translations` (
    `trip_id` INT NOT NULL,
    `lang` CHAR(2) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY (`trip_id`, `lang`),
    FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Timeline Steps (Transport/Hotel steps)
CREATE TABLE IF NOT EXISTS `timeline_steps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trip_id` INT NOT NULL,
    `step_order` INT NOT NULL DEFAULT 0,
    `transport_type` VARCHAR(50) NOT NULL, -- flight, train, bus, walk, hotel, taxi, car
    `departure_time` VARCHAR(50) NULL,
    `arrival_time` VARCHAR(50) NULL,
    `icon` VARCHAR(50) NULL,
    FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Timeline Step Translations
CREATE TABLE IF NOT EXISTS `timeline_step_translations` (
    `step_id` INT NOT NULL,
    `lang` CHAR(2) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `text` TEXT NULL,
    PRIMARY KEY (`step_id`, `lang`),
    FOREIGN KEY (`step_id`) REFERENCES `timeline_steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Posts Table
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trip_id` INT NULL,
    `cover_image` VARCHAR(255) NULL,
    `is_active` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Post Translations
CREATE TABLE IF NOT EXISTS `post_translations` (
    `post_id` INT NOT NULL,
    `lang` CHAR(2) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `meta_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    PRIMARY KEY (`post_id`, `lang`),
    UNIQUE KEY `unique_slug_lang` (`slug`, `lang`),
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments Table
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `author_name` VARCHAR(100) NOT NULL,
    `author_email` VARCHAR(100) NOT NULL,
    `content` TEXT NOT NULL,
    `is_approved` TINYINT DEFAULT 0, -- 0 = Pending, 1 = Approved, -1 = Spam/Rejected
    `ip_address` VARCHAR(45) NOT NULL,
    `parent_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tracker Logs Table
CREATE TABLE IF NOT EXISTS `tracker_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `country_code` VARCHAR(10) NULL,
    `country_name` VARCHAR(100) NULL,
    `city` VARCHAR(100) NULL,
    `user_agent` TEXT NOT NULL,
    `browser` VARCHAR(50) NULL,
    `os` VARCHAR(50) NULL,
    `device` VARCHAR(20) NULL, -- 'desktop', 'mobile', 'tablet'
    `url_path` VARCHAR(255) NOT NULL,
    `referrer` TEXT NULL,
    `screen_width` INT NULL,
    `screen_height` INT NULL,
    `session_id` VARCHAR(64) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for tracker reports performance
CREATE INDEX `idx_tracker_created` ON `tracker_logs` (`created_at`);
CREATE INDEX `idx_tracker_ip` ON `tracker_logs` (`ip_address`);
CREATE INDEX `idx_tracker_url` ON `tracker_logs` (`url_path`);
CREATE INDEX `idx_tracker_country` ON `tracker_logs` (`country_code`);
