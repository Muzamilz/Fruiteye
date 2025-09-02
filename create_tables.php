<?php
// Simple table creation script to fix the missing tables issue
require_once 'config.php';

try {
    $pdo = get_db_connection();
    
    // Create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `users` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create analyses table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `analyses` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create user_settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `user_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user_setting` (`user_id`, `setting_key`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create system_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `system_logs` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Insert default admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['admin@fruiteye.com']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO users (name, email, password, is_admin, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute(['مدير النظام', 'admin@fruiteye.com', password_hash('password', PASSWORD_DEFAULT), 1]);
    }
    
    // Insert test user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['user@test.com']);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO users (name, email, password, is_admin, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute(['مستخدم تجريبي', 'user@test.com', password_hash('password', PASSWORD_DEFAULT), 0]);
    }
    
    echo "✅ تم إنشاء جميع الجداول بنجاح!<br>";
    echo "✅ تم إدراج المستخدمين الافتراضيين<br>";
    echo "<br><strong>يمكنك الآن تسجيل الدخول:</strong><br>";
    echo "البريد الإلكتروني: admin@fruiteye.com<br>";
    echo "كلمة المرور: password<br>";
    echo "<br><a href='login.php'>تسجيل الدخول</a>";
    
} catch (PDOException $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
