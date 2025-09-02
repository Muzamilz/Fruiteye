<?php
require_once 'config.php';

$page_title = 'تسجيل الدخول';
$nav_type = 'auth';
$error_message = '';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting for login attempts
    if (!rate_limit_check('login', 5, 300)) {
        $error_message = 'تم تجاوز عدد المحاولات المسموحة. يرجى المحاولة بعد 5 دقائق';
    } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'رمز الأمان غير صالح. يرجى المحاولة مرة أخرى';
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Input validation
        if (empty($email) || empty($password)) {
            $error_message = 'يرجى إدخال البريد الإلكتروني وكلمة المرور';
        } elseif (!validate_email($email)) {
            $error_message = 'البريد الإلكتروني غير صالح';
        } elseif (!validate_password($password)) {
            $error_message = 'كلمة المرور يجب أن تكون بين 6 و 255 حرف';
        } else {
            try {
                $pdo = get_db_connection();
                $result = $pdo->query("SHOW TABLES LIKE 'users'");
                if ($result->rowCount() == 0) {
                    $error_message = 'قاعدة البيانات غير مهيأة. يرجى تشغيل create_tables.php أولاً';
                } else {
                    if (authenticate_user($email, $password)) {
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        $error_message = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
                    }
                }
            } catch (Exception $e) {
                error_log("Database error: " . $e->getMessage());
                $error_message = 'خطأ في الاتصال بقاعدة البيانات';
            }
        }
    }
}

include 'includes/header.php';
?>

<main class="auth-container">
    <div class="auth-card">
        <h1>تسجيل الدخول</h1>
        <p class="auth-subtitle">أدخل بياناتك للوصول إلى حسابك</p>
        
        <?php if (isset($_SESSION['logout_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['logout_message']; unset($_SESSION['logout_message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['registration_success']; unset($_SESSION['registration_success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="admin@fruiteye.com">
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required placeholder="password">
                <div class="password-toggle">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" id="remember" name="remember">
                    <span class="checkmark"></span>
                    تذكرني
                </label>
                <a href="#" class="forgot-password">نسيت كلمة المرور؟</a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">تسجيل الدخول</button>
        </form>
        
        
        <div class="auth-footer">
            <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
