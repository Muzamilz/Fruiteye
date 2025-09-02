<?php
// Database update script to add missing user_activity table
// Run this file to add the user_activity table to existing database

require_once 'config.php';

$success_messages = [];
$error_messages = [];

try {
    $pdo = get_db_connection();
    
    // Check if user_activity table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'user_activity'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        // Create user_activity table
        $create_table_sql = "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($create_table_sql);
        $success_messages[] = "تم إنشاء جدول user_activity بنجاح";
        
        // Insert sample data
        $sample_data_sql = "
        INSERT INTO `user_activity` (`user_id`, `action`, `description`, `details`, `created_at`) VALUES
        (1, 'login', 'User logged in successfully', 'Admin login from dashboard', NOW() - INTERVAL 1 HOUR),
        (1, 'file_upload', 'File uploaded successfully', 'apple.jpg uploaded and processed', NOW() - INTERVAL 2 HOUR),
        (1, 'profile_update', 'Profile information updated', 'Updated name and email', NOW() - INTERVAL 1 DAY),
        (1, 'password_change', 'Password changed successfully', 'Password updated from settings', NOW() - INTERVAL 2 DAY)";
        
        // Check if we have users to add sample data for
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $user_count = $stmt->fetchColumn();
        
        if ($user_count > 0) {
            $pdo->exec($sample_data_sql);
            $success_messages[] = "تم إدراج بيانات تجريبية في جدول user_activity";
        }
        
    } else {
        $success_messages[] = "جدول user_activity موجود بالفعل";
    }
    
} catch (PDOException $e) {
    $error_messages[] = "خطأ في قاعدة البيانات: " . $e->getMessage();
} catch (Exception $e) {
    $error_messages[] = "خطأ عام: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث قاعدة البيانات - عين الفاكهة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تحديث قاعدة البيانات - عين الفاكهة</h1>
        
        <?php if (!empty($success_messages)): ?>
            <?php foreach ($success_messages as $message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($error_messages)): ?>
            <?php foreach ($error_messages as $message): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="center">
            <a href="dashboard.php" class="btn">الذهاب إلى لوحة التحكم</a>
            <a href="index.php" class="btn">الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
