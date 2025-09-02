<?php
require_once 'config.php';
require_login();
$page_title = 'الإعدادات';
$nav_type = 'dashboard';
$user = get_logged_user();

// Get user data from database
$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'رمز الأمان غير صالح. يرجى المحاولة مرة أخرى';
    } else {
        if (isset($_POST['update_profile'])) {
            $name = sanitize_input($_POST['name'] ?? '');
            $email = sanitize_input($_POST['email'] ?? '');
            $phone = sanitize_input($_POST['phone'] ?? '');
            
            // Validate inputs
            if (!validate_name($name)) {
                $error_message = 'الاسم يجب أن يكون بين 2 و 100 حرف ويحتوي على أحرف صالحة فقط';
            } elseif (!validate_email($email)) {
                $error_message = 'البريد الإلكتروني غير صالح';
            } else {
                try {
                    // Check if email is already taken by another user
                    $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                    $check_stmt->execute([$email, $_SESSION['user_id']]);
                    
                    if ($check_stmt->fetch()) {
                        $error_message = 'البريد الإلكتروني مستخدم من قبل مستخدم آخر';
                    } else {
                        // Update user profile
                        $update_stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?");
                        $update_stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
                        
                        // Update session data
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        
                        // Refresh user data
                        $stmt->execute([$_SESSION['user_id']]);
                        $user_data = $stmt->fetch();
                        
                        $success_message = 'تم حفظ الإعدادات بنجاح';
                        log_user_activity($_SESSION['user_id'], 'profile_update', 'Profile updated: ' . sanitize_input($name));
                    }
                } catch (PDOException $e) {
                    error_log("Profile update error: " . $e->getMessage());
                    $error_message = 'حدث خطأ أثناء حفظ البيانات';
                }
            }
        } elseif (isset($_POST['upload_avatar'])) {
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatar_file = $_FILES['avatar'];
                
                // Validate file type and size
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                if ($avatar_file['size'] > $max_size) {
                    $error_message = 'حجم الصورة كبير جداً. الحد الأقصى 2 ميجابايت';
                } else {
                    $file_ext = strtolower(pathinfo($avatar_file['name'], PATHINFO_EXTENSION));
                    if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $error_message = 'نوع الملف غير مدعوم. يرجى استخدام JPG, PNG أو GIF';
                    } else {
                        try {
                            // Create avatars directory if it doesn't exist
                            $avatar_dir = 'uploads/avatars/';
                            if (!is_dir($avatar_dir)) {
                                mkdir($avatar_dir, 0755, true);
                            }
                            
                            // Generate unique filename
                            $avatar_filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
                            $avatar_path = $avatar_dir . $avatar_filename;
                            
                            // Delete old avatar if exists
                            if (!empty($user_data['avatar']) && file_exists($user_data['avatar'])) {
                                unlink($user_data['avatar']);
                            }
                            
                            // Move uploaded file
                            if (move_uploaded_file($avatar_file['tmp_name'], $avatar_path)) {
                                // Update database
                                $update_stmt = $pdo->prepare("UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?");
                                $update_stmt->execute([$avatar_path, $_SESSION['user_id']]);
                                
                                // Refresh user data
                                $stmt->execute([$_SESSION['user_id']]);
                                $user_data = $stmt->fetch();
                                
                                $success_message = 'تم تحديث صورة الملف الشخصي بنجاح';
                                log_user_activity($_SESSION['user_id'], 'avatar_update', 'Avatar updated');
                            } else {
                                $error_message = 'فشل في رفع الصورة';
                            }
                        } catch (Exception $e) {
                            error_log("Avatar upload error: " . $e->getMessage());
                            $error_message = 'حدث خطأ أثناء رفع الصورة';
                        }
                    }
                }
            } else {
                $error_message = 'يرجى اختيار صورة للرفع';
            }
        } elseif (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate password change
            if (empty($current_password)) {
                $error_message = 'يرجى إدخال كلمة المرور الحالية';
            } elseif (!password_verify($current_password, $user_data['password'])) {
                $error_message = 'كلمة المرور الحالية غير صحيحة';
            } elseif (!validate_password($new_password)) {
                $error_message = 'كلمة المرور الجديدة يجب أن تكون بين 6 و 255 حرف';
            } elseif ($new_password !== $confirm_password) {
                $error_message = 'كلمة المرور الجديدة غير متطابقة';
            } else {
                // Check password strength
                $strength_check = validate_password_strength($new_password);
                if (!$strength_check['valid']) {
                    $error_message = implode('<br>', $strength_check['errors']);
                } else {
                    try {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                        $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
                        
                        $success_message = 'تم تغيير كلمة المرور بنجاح';
                        log_user_activity($_SESSION['user_id'], 'password_change', 'Password changed successfully');
                    } catch (PDOException $e) {
                        error_log("Password change error: " . $e->getMessage());
                        $error_message = 'حدث خطأ أثناء تغيير كلمة المرور';
                    }
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="main-content">
    <aside class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <img src="https://via.placeholder.com/80x80?text=صورة" alt="صورة المستخدم">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                <p><?php echo $user['is_admin'] ? 'مسؤول' : 'مستخدم'; ?></p>
            </div>
        </div>
        
        <div class="sidebar-section">
            <h3 class="sidebar-title">القائمة الرئيسية</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> لوحة التحكم</a></li>
                <li><a href="upload.php"><i class="fas fa-upload"></i> رفع الصور</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> السجل</a></li>
                <li><a href="analysis.php"><i class="fas fa-chart-bar"></i> الإحصائيات</a></li>
            </ul>
        </div>
        
        <div class="sidebar-section">
            <h3 class="sidebar-title">الإعدادات</h3>
            <ul class="sidebar-menu">
                <li><a href="settings.php" class="active"><i class="fas fa-cog"></i> الإعدادات العامة</a></li>
            </ul>
        </div>
    </aside>

    <main class="content">
        <div class="page-header">
            <h1 class="page-title">الإعدادات</h1>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="settings-container">
            <div class="settings-section">
                <h2><i class="fas fa-user"></i> الملف الشخصي</h2>
                <div class="settings-card">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="avatar-upload">
                            <div class="avatar-preview">
                                <img src="<?php echo !empty($user_data['avatar']) && file_exists($user_data['avatar']) ? $user_data['avatar'] : 'https://via.placeholder.com/100x100?text=صورة'; ?>" alt="صورة الملف الشخصي" id="avatarPreview">
                            </div>
                            <div>
                                <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display: none;">
                                <label for="avatarInput" class="btn btn-secondary" style="cursor: pointer;">تغيير الصورة</label>
                                <p>JPG, PNG أو GIF. الحد الأقصى 2MB</p>
                                <div id="avatarPreviewContainer" style="display: none; margin-top: 10px;">
                                    <img id="newAvatarPreview" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                                    <button type="button" id="uploadAvatarBtn" class="btn btn-primary" style="margin-left: 10px;">رفع الصورة</button>
                                    <button type="button" id="cancelAvatarBtn" class="btn btn-secondary" style="margin-left: 5px;">إلغاء</button>
                                </div>
                            </div>
                        </div>
                        

                        <div class="form-group">
                            <label for="name">الاسم الكامل</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">البريد الإلكتروني</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">رقم الهاتف</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" placeholder="اختياري">
                        </div>
                        
                        <div class="form-group">
                            <label>تاريخ التسجيل</label>
                            <input type="text" value="<?php echo date('Y-m-d H:i', strtotime($user_data['created_at'])); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>آخر تسجيل دخول</label>
                            <input type="text" value="<?php echo $user_data['last_login'] ? date('Y-m-d H:i', strtotime($user_data['last_login'])) : 'لم يسجل دخول من قبل'; ?>" readonly>
                        </div>

                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </form>
                    
                    <!-- Separate avatar upload form -->
                    <form method="POST" enctype="multipart/form-data" id="avatarForm" style="display: none;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="upload_avatar" value="1">
                    </form>
                </div>
            </div>

            <div class="settings-section">
                <h2><i class="fas fa-shield-alt"></i> الأمان والخصوصية</h2>
                <div class="settings-card">
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="form-group">
                            <label for="current_password">كلمة المرور الحالية</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">كلمة المرور الجديدة</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <div class="password-requirements">
                                <small>يجب أن تحتوي على 8 أحرف على الأقل، حرف كبير، حرف صغير، ورقم</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">تحديث كلمة المرور</button>
                    </form>
                </div>
            </div>

            <div class="settings-section">
                <h2><i class="fas fa-bell"></i> الإشعارات</h2>
                <div class="settings-card">
                    <div class="toggle-group">
                        <div>
                            <h4>إشعارات البريد الإلكتروني</h4>
                            <p>تلقي إشعارات عن نتائج التحليل والتحديثات</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="toggle-group">
                        <div>
                            <h4>إشعارات المتصفح</h4>
                            <p>إشعارات فورية في المتصفح</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <div class="toggle-group">
                        <div>
                            <h4>تقارير أسبوعية</h4>
                            <p>ملخص أسبوعي لنشاطك وتحليلاتك</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h2><i class="fas fa-question-circle"></i> المساعدة والدعم</h2>
                <div class="help-links">
                    <a href="#" class="help-link">
                        <i class="fas fa-book"></i>
                        <div>
                            <h4>دليل المستخدم</h4>
                            <p>تعلم كيفية استخدام النظام بفعالية</p>
                        </div>
                    </a>

                    <a href="#" class="help-link">
                        <i class="fas fa-headset"></i>
                        <div>
                            <h4>اتصل بالدعم</h4>
                            <p>تحدث مع فريق الدعم الفني</p>
                        </div>
                    </a>

                    <a href="#" class="help-link">
                        <i class="fas fa-comments"></i>
                        <div>
                            <h4>الأسئلة الشائعة</h4>
                            <p>إجابات للأسئلة الأكثر شيوعاً</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="settings-section">
                <h2><i class="fas fa-info-circle"></i> معلومات التطبيق</h2>
                <div class="app-info">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>الإصدار: 1.0.0</p>
                    <p>آخر تحديث: نوفمبر 2023</p>
                    <p>© 2023 جميع الحقوق محفوظة</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Avatar upload functionality
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('حجم الصورة كبير جداً. الحد الأقصى 2 ميجابايت');
            this.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('نوع الملف غير مدعوم. يرجى استخدام JPG, PNG أو GIF');
            this.value = '';
            return;
        }
        
        // Preview the image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('newAvatarPreview').src = e.target.result;
            document.getElementById('avatarPreviewContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Upload avatar button
document.getElementById('uploadAvatarBtn').addEventListener('click', function() {
    const fileInput = document.getElementById('avatarInput');
    if (fileInput.files.length > 0) {
        // Create FormData and submit via AJAX or form
        const formData = new FormData();
        formData.append('avatar', fileInput.files[0]);
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        formData.append('upload_avatar', '1');
        
        // Submit the avatar form
        const avatarForm = document.getElementById('avatarForm');
        const avatarFileInput = document.createElement('input');
        avatarFileInput.type = 'file';
        avatarFileInput.name = 'avatar';
        avatarFileInput.files = fileInput.files;
        avatarForm.appendChild(avatarFileInput);
        avatarForm.submit();
    }
});

// Cancel avatar upload
document.getElementById('cancelAvatarBtn').addEventListener('click', function() {
    document.getElementById('avatarInput').value = '';
    document.getElementById('avatarPreviewContainer').style.display = 'none';
});
</script>

<?php include 'includes/footer.php'; ?>
