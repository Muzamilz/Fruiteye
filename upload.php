<?php
require_once 'config.php';
require_login();
$page_title = 'رفع الصور';
$nav_type = 'dashboard';
$user = get_logged_user();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fruit_images'])) {
    // Debug logging
    error_log("Upload attempt detected");
    error_log("Files array: " . print_r($_FILES, true));
    
    // CSRF token validation
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_message = 'رمز الأمان غير صالح. يرجى المحاولة مرة أخرى';
        error_log("CSRF token validation failed");
    } else {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $uploaded_files = [];
        $files = $_FILES['fruit_images'];
        $error_message = '';
        
        // Check if files array is properly structured
        if (!is_array($files['name'])) {
            $error_message = 'لم يتم اختيار أي ملفات للرفع';
        } else {
            // Validate each file
            for ($i = 0; $i < count($files['name']); $i++) {
                if (empty($files['name'][$i])) {
                    continue; // Skip empty file slots
                }
                
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                // Simple validation instead of complex function
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error_message = 'خطأ في رفع الملف: ' . escape_output($file['name']);
                    error_log("Upload error for file {$file['name']}: " . $file['error']);
                    break;
                }
                
                if ($file['size'] > 5 * 1024 * 1024) { // 5MB
                    $error_message = 'حجم الملف كبير جداً للملف: ' . escape_output($file['name']);
                    break;
                }
                
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_extensions)) {
                    $error_message = 'نوع الملف غير مدعوم للملف: ' . escape_output($file['name']) . ' (المسموح: JPG, PNG, GIF, WebP)';
                    break;
                }
                
                $safe_filename = sanitize_filename($file['name']);
                $file_name = time() . '_' . uniqid() . '_' . $safe_filename;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $uploaded_files[] = [
                        'name' => escape_output($file['name']),
                        'path' => $file_path,
                        'size' => $file['size']
                    ];
                    
                    // Log successful upload
                    log_user_activity($_SESSION['user_id'], 'file_upload', 'Uploaded file: ' . sanitize_input($file['name']));
                } else {
                    $error_message = 'فشل في حفظ الملف: ' . escape_output($file['name']);
                    break;
                }
            }
            
            if (empty($error_message) && !empty($uploaded_files)) {
                $success_message = 'تم رفع ' . count($uploaded_files) . ' ملف بنجاح!';
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
                <li><a href="upload.php" class="active"><i class="fas fa-upload"></i> رفع الصور</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> السجل</a></li>
                <li><a href="analysis.php"><i class="fas fa-chart-bar"></i> الإحصائيات</a></li>
            </ul>
        </div>
        
        <div class="sidebar-section">
            <h3 class="sidebar-title">الإعدادات</h3>
            <ul class="sidebar-menu">
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات العامة</a></li>
            </ul>
        </div>
    </aside>

    <main class="content">
        <div class="page-header">
            <h1 class="page-title">رفع صور الفواكه</h1>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Debug info for upload -->
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;">
                <strong>معلومات التشخيص:</strong><br>
                تم إرسال النموذج - تحقق من سجل الأخطاء في PHP للتفاصيل
            </div>
        <?php endif; ?>

        <div class="upload-section">
            <h2><i class="fas fa-cloud-upload-alt"></i> رفع الصور للتحليل</h2>
            
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="file-input-container">
                    <label for="fruit_images" class="file-input-label">
                        <div class="upload-area">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h3>اختر الصور أو اسحبها هنا</h3>
                            <p>يمكنك رفع عدة صور في نفس الوقت</p>
                        </div>
                    </label>
                    <input type="file" id="fruit_images" name="fruit_images[]" multiple accept="image/*" class="file-input">
                </div>
                
                <div class="selected-files" id="selectedFiles" style="display: none;">
                    <h4><i class="fas fa-images"></i> الملفات المختارة:</h4>
                    <div class="files-preview" id="filesPreview"></div>
                </div>
                
                <div class="upload-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>معلومات مهمة:</strong>
                    <ul>
                        <li>الحد الأقصى لحجم الملف: 5 ميجابايت</li>
                        <li>الصيغ المدعومة: JPG, PNG, GIF, WebP</li>
                        <li>للحصول على أفضل النتائج، استخدم صور واضحة وعالية الجودة</li>
                    </ul>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                        <i class="fas fa-upload"></i> رفع الصور
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="clearSelection()" id="clearBtn" style="display: none;">
                        <i class="fas fa-times"></i> إلغاء التحديد
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($uploaded_files) && !empty($uploaded_files)): ?>
        <div class="analysis-results">
            <h2><i class="fas fa-chart-line"></i> نتائج التحليل</h2>
            <div class="results-grid">
                <?php foreach ($uploaded_files as $file): ?>
                <div class="result-card">
                    <img src="<?php echo $file['path']; ?>" alt="<?php echo $file['name']; ?>" class="result-image">
                    <div class="result-info">
                        <h3><?php echo $file['name']; ?></h3>
                        <p class="analysis-status">جاري التحليل...</p>
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
// Simple file upload handling
const fileInput = document.getElementById('fruit_images');
const selectedFilesDiv = document.getElementById('selectedFiles');
const filesPreview = document.getElementById('filesPreview');
const uploadBtn = document.getElementById('uploadBtn');
const clearBtn = document.getElementById('clearBtn');
const uploadArea = document.querySelector('.upload-area');

// Handle file selection
fileInput.addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 0) {
        showSelectedFiles(files);
        uploadBtn.disabled = false;
        clearBtn.style.display = 'inline-block';
    } else {
        hideSelectedFiles();
    }
});

// Handle drag and drop
uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        showSelectedFiles(files);
        uploadBtn.disabled = false;
        clearBtn.style.display = 'inline-block';
    }
});

function showSelectedFiles(files) {
    selectedFilesDiv.style.display = 'block';
    filesPreview.innerHTML = '';
    
    Array.from(files).forEach(function(file, index) {
        const fileDiv = document.createElement('div');
        fileDiv.className = 'file-preview-item';
        fileDiv.innerHTML = `
            <div class="file-info">
                <i class="fas fa-image"></i>
                <span class="file-name">${file.name}</span>
                <span class="file-size">(${(file.size / 1024).toFixed(1)} KB)</span>
            </div>
        `;
        filesPreview.appendChild(fileDiv);
    });
}

function hideSelectedFiles() {
    selectedFilesDiv.style.display = 'none';
    uploadBtn.disabled = true;
    clearBtn.style.display = 'none';
}

function clearSelection() {
    fileInput.value = '';
    hideSelectedFiles();
}
</script>

<?php include 'includes/footer.php'; ?>
