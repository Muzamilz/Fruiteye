<?php
require_once 'config.php';

$page_title = 'إنشاء حساب جديد';
$body_class = 'auth-page';
$nav_type = 'auth';
$error_message = '';
$success_message = '';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting for registration attempts
    if (!rate_limit_check('register', 3, 600)) {
        $error_message = 'تم تجاوز عدد المحاولات المسموحة. يرجى المحاولة بعد 10 دقائق';
    } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'رمز الأمان غير صالح. يرجى المحاولة مرة أخرى';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Input validation
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error_message = 'يرجى ملء جميع الحقول المطلوبة';
        } elseif (!validate_name($name)) {
            $error_message = 'الاسم يجب أن يكون بين 2 و 100 حرف ويحتوي على أحرف صالحة فقط';
        } elseif (!validate_email($email)) {
            $error_message = 'البريد الإلكتروني غير صالح';
        } elseif (!validate_password($password)) {
            $error_message = 'كلمة المرور يجب أن تكون بين 6 و 255 حرف';
        } elseif ($password !== $confirm_password) {
            $error_message = 'كلمة المرور غير متطابقة';
        } else {
            // Check password strength
            $strength_check = validate_password_strength($password);
            if (!$strength_check['valid']) {
                $error_message = implode('<br>', $strength_check['errors']);
            } else {
                // Debug: Add temporary logging
                error_log("Registration attempt: name=$name, email=$email");
                
                $result = register_user($name, $email, $password);
                error_log("Registration result: " . json_encode($result));
                
                if ($result['success']) {
                    error_log("Registration successful, attempting auto-login");
                    
                    // Auto-login after successful registration
                    $auth_result = authenticate_user($email, $password);
                    error_log("Auto-login result: " . ($auth_result ? 'success' : 'failed'));
                    
                    if ($auth_result) {
                        // Regenerate session ID for security
                        session_regenerate_id(true);
                        error_log("Redirecting to dashboard");
                        header('Location: dashboard.php');
                        exit();
                    } else {
                        // If auto-login fails, redirect to login page with success message
                        $_SESSION['registration_success'] = 'تم إنشاء الحساب بنجاح. يرجى تسجيل الدخول.';
                        error_log("Auto-login failed, redirecting to login");
                        header('Location: login.php');
                        exit();
                    }
                } else {
                    error_log("Registration failed: " . $result['message']);
                    $error_message = $result['message'];
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<main class="auth-container">
    <div class="auth-card">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">
                <strong>معلومات التشخيص:</strong><br>
                تم إرسال النموذج - تحقق من سجل الأخطاء في PHP للتفاصيل
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="fullName">الاسم الكامل</label>
                <input type="text" id="fullName" name="name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
                <div class="password-toggle">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="password-requirements">
                    <p class="requirements-title">متطلبات كلمة المرور:</p>
                    <ul class="requirements-list">
                        <li id="req-length" class="requirement">
                            <i class="fas fa-times"></i>
                            <span>8 أحرف على الأقل</span>
                        </li>
                        <li id="req-uppercase" class="requirement">
                            <i class="fas fa-times"></i>
                            <span>حرف كبير واحد على الأقل (A-Z)</span>
                        </li>
                        <li id="req-lowercase" class="requirement">
                            <i class="fas fa-times"></i>
                            <span>حرف صغير واحد على الأقل (a-z)</span>
                        </li>
                        <li id="req-number" class="requirement">
                            <i class="fas fa-times"></i>
                            <span>رقم واحد على الأقل (0-9)</span>
                        </li>
                    </ul>
                </div>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                    <span class="strength-text" id="strengthText">ضعيفة</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">تأكيد كلمة المرور</label>
                <input type="password" id="confirmPassword" name="confirm_password" required>
                <div class="password-toggle">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" id="terms" name="terms" required>
                    <span class="checkmark"></span>
                    أوافق على <a href="#">الشروط والأحكام</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full" id="submitBtn" disabled>إنشاء الحساب</button>
        </form>
        
        <div class="auth-footer">
            <p>لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const submitBtn = document.getElementById('submitBtn');
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    const requirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        lowercase: document.getElementById('req-lowercase'),
        number: document.getElementById('req-number')
    };
    
    function updateRequirement(element, met) {
        const icon = element.querySelector('i');
        if (met) {
            element.classList.add('met');
            icon.className = 'fas fa-check';
        } else {
            element.classList.remove('met');
            icon.className = 'fas fa-times';
        }
    }
    
    function checkPasswordStrength(password) {
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password)
        };
        
        // Update requirement indicators
        updateRequirement(requirements.length, checks.length);
        updateRequirement(requirements.uppercase, checks.uppercase);
        updateRequirement(requirements.lowercase, checks.lowercase);
        updateRequirement(requirements.number, checks.number);
        
        // Calculate strength
        const metCount = Object.values(checks).filter(Boolean).length;
        let strength = 'ضعيفة';
        let strengthClass = 'weak';
        
        if (metCount === 4) {
            strength = 'قوية';
            strengthClass = 'strong';
        } else if (metCount >= 2) {
            strength = 'متوسطة';
            strengthClass = 'medium';
        }
        
        strengthBar.className = 'strength-bar ' + strengthClass;
        strengthText.textContent = strength;
        
        return metCount === 4;
    }
    
    function validateForm() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const isStrongPassword = checkPasswordStrength(password);
        const passwordsMatch = password === confirmPassword && password.length > 0;
        
        // Add visual feedback for password match
        if (confirmPassword.length > 0) {
            if (passwordsMatch) {
                confirmPasswordInput.style.borderColor = '#28a745';
            } else {
                confirmPasswordInput.style.borderColor = '#dc3545';
            }
        }
        
        submitBtn.disabled = !(isStrongPassword && passwordsMatch);
    }
    
    passwordInput.addEventListener('input', validateForm);
    confirmPasswordInput.addEventListener('input', validateForm);
});
</script>


<?php include 'includes/footer.php'; ?>
