<?php
// Database installation script
// Run this file once to set up the database

require_once 'config.php';

$success_messages = [];
$error_messages = [];

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $success_messages[] = "تم إنشاء قاعدة البيانات بنجاح";
    
    // Use the database
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Read and execute SQL file
    $sql_file = __DIR__ . '/database_setup.sql';
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        
        // Remove comments and split SQL into individual statements
        $sql_content = preg_replace('/--.*$/m', '', $sql_content); // Remove single line comments
        $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content); // Remove multi-line comments
        
        $statements = array_filter(
            array_map('trim', explode(';', $sql_content)),
            function($stmt) {
                return !empty($stmt) && strlen(trim($stmt)) > 0;
            }
        );
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    $error_messages[] = "خطأ في تنفيذ SQL: " . $e->getMessage() . " - Statement: " . substr($statement, 0, 100) . "...";
                }
            }
        }
        
        $success_messages[] = "تم إنشاء جداول قاعدة البيانات بنجاح";
        $success_messages[] = "تم إدراج البيانات الأولية بنجاح";
        $success_messages[] = "يمكنك الآن تسجيل الدخول باستخدام:";
        $success_messages[] = "البريد الإلكتروني: admin@fruiteye.com";
        $success_messages[] = "كلمة المرور: password";
        
    } else {
        $error_messages[] = "ملف SQL غير موجود: " . $sql_file;
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
    <title>تثبيت قاعدة البيانات - عين الفاكهة</title>
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
        <h1>تثبيت قاعدة البيانات - عين الفاكهة</h1>
        
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
        
        <?php if (empty($error_messages)): ?>
            <div class="center">
                <a href="index.php" class="btn">الذهاب إلى الصفحة الرئيسية</a>
                <a href="login.php" class="btn">تسجيل الدخول</a>
            </div>
        <?php else: ?>
            <div class="center">
                <p>يرجى التأكد من تشغيل خادم MySQL وصحة إعدادات الاتصال في config.php</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
