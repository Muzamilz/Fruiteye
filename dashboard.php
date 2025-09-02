<?php
require_once 'config.php';
require_login();
$page_title = 'لوحة التحكم';
$nav_type = 'dashboard';
$user = get_logged_user();

// Get real user data from database
$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();

// Get user statistics
$stats = [];

// Total uploads count
$stmt = $pdo->prepare("SELECT COUNT(*) as total_uploads FROM user_activity WHERE user_id = ? AND action = 'file_upload'");
$stmt->execute([$_SESSION['user_id']]);
$stats['total_uploads'] = $stmt->fetchColumn();

// Recent uploads (last 7 days)
$stmt = $pdo->prepare("SELECT COUNT(*) as recent_uploads FROM user_activity WHERE user_id = ? AND action = 'file_upload' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute([$_SESSION['user_id']]);
$stats['recent_uploads'] = $stmt->fetchColumn();

// Total analysis count (simulated for now)
$stats['total_analysis'] = $stats['total_uploads'];

// Success rate (simulated - 85% success rate)
$stats['success_rate'] = $stats['total_uploads'] > 0 ? round(($stats['total_uploads'] * 0.85)) : 0;

// Get recent activity
$stmt = $pdo->prepare("SELECT * FROM user_activity WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$recent_activities = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="main-content">
    <aside class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <img src="<?php echo !empty($user_data['avatar']) && file_exists($user_data['avatar']) ? $user_data['avatar'] : 'https://via.placeholder.com/80x80?text=صورة'; ?>" alt="صورة المستخدم" id="userAvatar">
            </div>
            <div class="user-info">
                <h3 id="userName"><?php echo htmlspecialchars($user_data['name']); ?></h3>
                <p id="userRole"><?php echo $user_data['is_admin'] ? 'مسؤول' : 'مستخدم'; ?></p>
                <p>عضو منذ: <span id="joinDate"><?php echo date('j F Y', strtotime($user_data['created_at'])); ?></span></p>
            </div>
        </div>
        
        <div class="sidebar-section">
            <h3 class="sidebar-title">القائمة الرئيسية</h3>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> لوحة التحكم</a></li>
                <li><a href="upload.php"><i class="fas fa-upload"></i> رفع الصور</a></li>
                <li><a href="history.php"><i class="fas fa-history"></i> السجل</a></li>
                <li><a href="analysis.php"><i class="fas fa-chart-bar"></i> الإحصائيات</a></li>
            </ul>
        </div>
        
        <?php if ($user['is_admin']): ?>
        <div class="sidebar-section" id="adminSection">
            <h3 class="sidebar-title">إدارة النظام</h3>
            <ul class="sidebar-menu">
                <li><a href="#users-management"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="#system-reports"><i class="fas fa-chart-line"></i> تقارير النظام</a></li>
                <li><a href="#system-settings"><i class="fas fa-cogs"></i> إعدادات النظام</a></li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="sidebar-section">
            <h3 class="sidebar-title">الإعدادات</h3>
            <ul class="sidebar-menu">
                <li><a href="settings.php"><i class="fas fa-cog"></i> الإعدادات العامة</a></li>
                <li><a href="settings.php#profile"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                <li><a href="settings.php#privacy"><i class="fas fa-shield-alt"></i> الخصوصية</a></li>
            </ul>
        </div>
    </aside>

    <main class="content">
        <div class="page-header">
            <h1 class="page-title">لوحة تحكم <?php echo htmlspecialchars($user['name']); ?></h1>
            <div class="page-actions">
                <a href="upload.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> تحليل جديد
                </a>
            </div>
        </div>

        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>مرحباً، <?php echo htmlspecialchars($user['name']); ?>!</h2>
                <p>هذه نظرة عامة على نشاطك وتحليلاتك الأخيرة.</p>
            </div>
            <div class="welcome-actions">
                <a href="upload.php" class="btn btn-primary">بدء تحليل جديد</a>
            </div>
        </div>

        <div class="cards-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3 class="card-title">إجمالي الرفعات</h3>
                </div>
                <div class="card-value"><?php echo $stats['total_uploads']; ?></div>
                <div class="card-text">عدد الصور المرفوعة</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h3 class="card-title">التحليلات</h3>
                </div>
                <div class="card-value"><?php echo $stats['total_analysis']; ?></div>
                <div class="card-text">تحليل مكتمل</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <h3 class="card-title">هذا الأسبوع</h3>
                </div>
                <div class="card-value"><?php echo $stats['recent_uploads']; ?></div>
                <div class="card-text">رفعة جديدة</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="card-title">معدل النجاح</h3>
                </div>
                <div class="card-value"><?php echo $stats['success_rate']; ?>%</div>
                <div class="card-text">نسبة التحليلات الناجحة</div>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="recent-section">
                <h2><i class="fas fa-history"></i> النشاط الأخير</h2>
                <?php if (!empty($recent_activities)): ?>
                    <div class="activity-list">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-<?php echo $activity['action'] == 'file_upload' ? 'upload' : ($activity['action'] == 'login' ? 'sign-in-alt' : 'cog'); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                    <span class="activity-time"><?php echo date('j F Y - H:i', strtotime($activity['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="view-all">
                        <a href="history.php" class="btn btn-secondary">عرض جميع الأنشطة</a>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-inbox"></i>
                        <h3>لا توجد أنشطة حتى الآن</h3>
                        <p>ابدأ برفع صور الفواكه لتحليلها</p>
                        <a href="upload.php" class="btn btn-primary">رفع صورة</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="activity-section">
                <h2><i class="fas fa-chart-pie"></i> إحصائيات سريعة</h2>
                <div class="stats-summary">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h4>اليوم</h4>
                            <p><?php 
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity WHERE user_id = ? AND action = 'file_upload' AND DATE(created_at) = CURDATE()");
                                $stmt->execute([$_SESSION['user_id']]);
                                echo $stmt->fetchColumn();
                            ?> رفعة</p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h4>آخر نشاط</h4>
                            <p><?php echo $user_data['last_login'] ? date('j/m H:i', strtotime($user_data['last_login'])) : 'لا يوجد'; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-user-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h4>عضو منذ</h4>
                            <p><?php echo date('j F Y', strtotime($user_data['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
