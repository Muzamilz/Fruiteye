<?php
// Debug registration to see what's happening
require_once 'config.php';

echo "<h2>تشخيص مشكلة التسجيل</h2>";

// Test database connection and tables
echo "<h3>1. فحص قاعدة البيانات:</h3>";
try {
    $pdo = get_db_connection();
    echo "✅ الاتصال بقاعدة البيانات ناجح<br>";
    
    $result = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() > 0) {
        echo "✅ جدول المستخدمين موجود<br>";
    } else {
        echo "❌ جدول المستخدمين غير موجود - <a href='create_tables.php'>إنشاء الجداول</a><br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ خطأ في قاعدة البيانات: " . $e->getMessage() . "<br>";
    exit;
}

// Test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>2. اختبار بيانات النموذج:</h3>";
    
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    echo "الاسم: " . htmlspecialchars($name) . "<br>";
    echo "البريد الإلكتروني: " . htmlspecialchars($email) . "<br>";
    echo "طول كلمة المرور: " . strlen($password) . "<br><br>";
    
    echo "<h3>3. اختبار التحقق من صحة البيانات:</h3>";
    echo "تحقق الاسم: " . (validate_name($name) ? "✅" : "❌") . "<br>";
    echo "تحقق البريد الإلكتروني: " . (validate_email($email) ? "✅" : "❌") . "<br>";
    echo "تحقق كلمة المرور: " . (validate_password($password) ? "✅" : "❌") . "<br>";
    
    $strength_check = validate_password_strength($password);
    echo "قوة كلمة المرور: " . ($strength_check['valid'] ? "✅" : "❌") . "<br>";
    if (!$strength_check['valid']) {
        echo "أخطاء كلمة المرور: " . implode(', ', $strength_check['errors']) . "<br>";
    }
    echo "<br>";
    
    if (validate_name($name) && validate_email($email) && $strength_check['valid']) {
        echo "<h3>4. اختبار التسجيل:</h3>";
        $result = register_user($name, $email, $password);
        
        echo "نتيجة التسجيل: " . ($result['success'] ? "✅ نجح" : "❌ فشل") . "<br>";
        echo "الرسالة: " . $result['message'] . "<br>";
        
        if ($result['success']) {
            echo "<h3>5. اختبار تسجيل الدخول التلقائي:</h3>";
            $auth_result = authenticate_user($email, $password);
            echo "نتيجة تسجيل الدخول: " . ($auth_result ? "✅ نجح" : "❌ فشل") . "<br>";
            
            if ($auth_result) {
                echo "<strong style='color: green;'>التسجيل وتسجيل الدخول نجحا! يجب التوجيه إلى لوحة التحكم</strong><br>";
                echo "<a href='dashboard.php'>الذهاب إلى لوحة التحكم</a><br>";
            }
        }
    } else {
        echo "<h3>❌ البيانات غير صالحة - لا يمكن المتابعة</h3>";
    }
}
?>

<form method="POST" style="margin-top: 20px; padding: 20px; border: 1px solid #ddd;">
    <h3>اختبار التسجيل</h3>
    <div style="margin-bottom: 10px;">
        <label>الاسم:</label><br>
        <input type="text" name="name" value="Test User" style="width: 300px; padding: 5px;">
    </div>
    <div style="margin-bottom: 10px;">
        <label>البريد الإلكتروني:</label><br>
        <input type="email" name="email" value="testuser@example.com" style="width: 300px; padding: 5px;">
    </div>
    <div style="margin-bottom: 10px;">
        <label>كلمة المرور (يجب أن تحتوي على: 8+ أحرف، حرف كبير، حرف صغير، رقم):</label><br>
        <input type="password" name="password" value="TestPass123" style="width: 300px; padding: 5px;">
    </div>
    <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none;">اختبار التسجيل</button>
</form>

<div style="margin-top: 20px;">
    <h3>خطوات الإصلاح:</h3>
    <ol>
        <li>تأكد من تشغيل <a href="create_tables.php">create_tables.php</a> أولاً</li>
        <li>جرب النموذج أعلاه لاختبار التسجيل</li>
        <li>تحقق من سجل الأخطاء في PHP</li>
    </ol>
</div>
