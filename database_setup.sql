-- Database setup for Fruit Analysis System (عين الفاكهة)
-- Run this script in phpMyAdmin or MySQL command line

-- Create database
CREATE DATABASE IF NOT EXISTS `fruit_analysis` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fruit_analysis`;

-- Users table
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(150) NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL,
    `is_admin` tinyint(1) DEFAULT 0,
    `avatar` varchar(255) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` timestamp NULL DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analyses table
CREATE TABLE `analyses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `image_path` varchar(500) NOT NULL,
    `original_filename` varchar(255) NOT NULL,
    `fruit_type` varchar(100) DEFAULT NULL,
    `disease_detected` varchar(200) DEFAULT NULL,
    `confidence_score` decimal(5,2) DEFAULT NULL,
    `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
    `result_data` text DEFAULT NULL,
    `file_size` int(11) DEFAULT NULL,
    `analysis_duration` decimal(8,2) DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User settings table
CREATE TABLE `user_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user_setting` (`user_id`, `setting_key`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System logs table
CREATE TABLE `system_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User activity table for tracking user actions
CREATE TABLE `user_activity` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `action` varchar(100) NOT NULL,
    `description` text DEFAULT NULL,
    `details` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_user_action` (`user_id`, `action`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT INTO `users` (`name`, `email`, `password`, `is_admin`, `created_at`) VALUES
('مدير النظام', 'admin@fruiteye.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());
-- Default password is 'password' - change this after first login

-- Insert sample user for testing
INSERT INTO `users` (`name`, `email`, `password`, `is_admin`, `created_at`) VALUES
('مستخدم تجريبي', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, NOW());
-- Default password is 'password' - change this after first login

-- Insert default settings for notifications
INSERT INTO `user_settings` (`user_id`, `setting_key`, `setting_value`) VALUES
(1, 'email_notifications', '1'),
(1, 'browser_notifications', '0'),
(1, 'weekly_reports', '1'),
(2, 'email_notifications', '1'),
(2, 'browser_notifications', '0'),
(2, 'weekly_reports', '1');

-- Insert sample user activity data
INSERT INTO `user_activity` (`user_id`, `action`, `description`, `details`, `created_at`) VALUES
(1, 'login', 'User logged in successfully', 'Admin login from dashboard', NOW() - INTERVAL 1 HOUR),
(1, 'file_upload', 'File uploaded successfully', 'apple.jpg uploaded and processed', NOW() - INTERVAL 2 HOUR),
(1, 'profile_update', 'Profile information updated', 'Updated name and email', NOW() - INTERVAL 1 DAY),
(2, 'login', 'User logged in successfully', 'Regular user login', NOW() - INTERVAL 3 HOUR),
(2, 'file_upload', 'File upload failed', 'orange.jpg upload failed - file too large', NOW() - INTERVAL 4 HOUR),
(2, 'file_upload', 'File uploaded successfully', 'banana.jpg uploaded and processed', NOW() - INTERVAL 1 DAY),
(1, 'password_change', 'Password changed successfully', 'Password updated from settings', NOW() - INTERVAL 2 DAY),
(2, 'avatar_upload', 'Avatar uploaded successfully', 'Profile picture updated', NOW() - INTERVAL 3 DAY);

-- Create indexes for better performance
CREATE INDEX `idx_analyses_user_status` ON `analyses` (`user_id`, `status`);
CREATE INDEX `idx_analyses_fruit_type` ON `analyses` (`fruit_type`);
CREATE INDEX `idx_users_last_login` ON `users` (`last_login`);

-- Views for easier data access
CREATE VIEW `user_analysis_stats` AS
SELECT 
    u.id as user_id,
    u.name as user_name,
    COUNT(a.id) as total_analyses,
    COUNT(CASE WHEN a.disease_detected IS NOT NULL AND a.disease_detected != '' THEN 1 END) as diseased_count,
    COUNT(CASE WHEN a.disease_detected IS NULL OR a.disease_detected = '' THEN 1 END) as healthy_count,
    AVG(a.confidence_score) as avg_confidence,
    MAX(a.created_at) as last_analysis
FROM users u
LEFT JOIN analyses a ON u.id = a.user_id AND a.status = 'completed'
GROUP BY u.id, u.name;

-- Create view for recent activity
CREATE VIEW `recent_activity` AS
SELECT 
    'analysis' as activity_type,
    a.id as activity_id,
    u.name as user_name,
    CONCAT('تحليل ', a.fruit_type) as activity_description,
    a.created_at as activity_time
FROM analyses a
JOIN users u ON a.user_id = u.id
WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
UNION ALL
SELECT 
    'user_registration' as activity_type,
    u.id as activity_id,
    u.name as user_name,
    'تسجيل مستخدم جديد' as activity_description,
    u.created_at as activity_time
FROM users u
WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY activity_time DESC
LIMIT 50;
