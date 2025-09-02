<?php
require_once 'config.php';
require_login();
$page_title = 'سجل التحليلات';
$nav_type = 'dashboard';
$user = get_logged_user();

// Get database connection
$pdo = get_db_connection();

// Get user data with avatar
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();

// Get upload statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_uploads FROM user_activity WHERE user_id = ? AND action = 'file_upload'");
$stmt->execute([$_SESSION['user_id']]);
$total_uploads = $stmt->fetchColumn();

// Get this month's uploads
$stmt = $pdo->prepare("SELECT COUNT(*) as month_uploads FROM user_activity WHERE user_id = ? AND action = 'file_upload' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$stmt->execute([$_SESSION['user_id']]);
$month_uploads = $stmt->fetchColumn();

// Get successful uploads (assuming successful uploads have 'success' in description)
$stmt = $pdo->prepare("SELECT COUNT(*) as successful_uploads FROM user_activity WHERE user_id = ? AND action = 'file_upload' AND description LIKE '%success%'");
$stmt->execute([$_SESSION['user_id']]);
$successful_uploads = $stmt->fetchColumn();

// Get failed uploads
$failed_uploads = $total_uploads - $successful_uploads;

// Get all upload activities with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$stmt = $pdo->prepare("SELECT * FROM user_activity WHERE user_id = ? AND action = 'file_upload' ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$_SESSION['user_id'], $per_page, $offset]);
$upload_history = $stmt->fetchAll();

// Get total pages
$stmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity WHERE user_id = ? AND action = 'file_upload'");
$stmt->execute([$_SESSION['user_id']]);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

include 'includes/header.php';
?>

<div class="main-content">
    <aside class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <img src="<?php echo !empty($user_data['avatar']) && file_exists($user_data['avatar']) ? $user_data['avatar'] : 'https://via.placeholder.com/80x80?text=صورة'; ?>" alt="صورة المستخدم">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($user_data['name']); ?></h3>
                <p><?php echo $user_data['is_admin'] ? 'مسؤول' : 'مستخدم'; ?></p>
                <p>عضو منذ: <?php echo date('j F Y', strtotime($user_data['created_at'])); ?></p>
            </div>
        </div>
        
        <div class="sidebar-section">
            <h3 class="sidebar-title">القائمة الرئيسية</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> لوحة التحكم</a></li>
                <li><a href="upload.php"><i class="fas fa-upload"></i> رفع الصور</a></li>
                <li><a href="history.php" class="active"><i class="fas fa-history"></i> السجل</a></li>
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
            <h1 class="page-title">سجل التحليلات</h1>
        </div>

        <div class="history-stats">
            <div class="stat-item">
                <div class="stat-value"><?php echo $total_uploads; ?></div>
                <div class="stat-label">إجمالي الرفعات</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $month_uploads; ?></div>
                <div class="stat-label">هذا الشهر</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $successful_uploads; ?></div>
                <div class="stat-label">رفعات ناجحة</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php echo $failed_uploads; ?></div>
                <div class="stat-label">رفعات فاشلة</div>
            </div>
        </div>

        <div class="results-section">
            <div class="history-filters">
                <div class="search-box">
                    <input type="text" placeholder="ابحث في التحليلات..." id="searchInput">
                    <i class="fas fa-search"></i>
                </div>
                
                <div class="sort-options">
                    <label for="sortBy">ترتيب حسب:</label>
                    <select id="sortBy">
                        <option value="date">التاريخ</option>
                        <option value="name">اسم الفاكهة</option>
                        <option value="status">الحالة</option>
                    </select>
                </div>
            </div>

            <div class="history-list" id="historyList">
                <?php if (!empty($upload_history)): ?>
                    <?php foreach ($upload_history as $upload): ?>
                        <div class="history-item">
                            <div class="history-icon">
                                <i class="fas fa-<?php echo strpos($upload['description'], 'success') !== false ? 'check-circle' : 'times-circle'; ?>"></i>
                            </div>
                            <div class="history-content">
                                <div class="history-title"><?php echo htmlspecialchars($upload['description']); ?></div>
                                <div class="history-details">
                                    <span class="history-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('j F Y - H:i', strtotime($upload['created_at'])); ?>
                                    </span>
                                    <span class="history-status <?php echo strpos($upload['description'], 'success') !== false ? 'success' : 'error'; ?>">
                                        <?php echo strpos($upload['description'], 'success') !== false ? 'نجح' : 'فشل'; ?>
                                    </span>
                                </div>
                                <?php if (!empty($upload['details'])): ?>
                                    <div class="history-extra">
                                        <?php echo htmlspecialchars($upload['details']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-history"></i>
                        <h3>لا يوجد سجل رفعات بعد</h3>
                        <p>ابدأ برفع صور الفواكه لرؤية السجل هنا</p>
                        <a href="upload.php" class="btn btn-primary">
                            <i class="fas fa-upload"></i> رفع صور جديدة
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                            <i class="fas fa-arrow-right"></i> السابق
                        </a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>
                            <i class="fas fa-arrow-right"></i> السابق
                        </button>
                    <?php endif; ?>
                    
                    <span class="pagination-pages">الصفحة <?php echo $page; ?> من <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                            التالي <i class="fas fa-arrow-left"></i>
                        </a>
                    <?php else: ?>
                        <button class="pagination-btn" disabled>
                            التالي <i class="fas fa-arrow-left"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
