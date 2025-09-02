<?php
require_once 'config.php';
require_login();
$page_title = 'تحليل النتائج';
$nav_type = 'dashboard';
$user = get_logged_user();

// Get database connection
$pdo = get_db_connection();

// Get user data with avatar
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();

// Get analysis statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total_uploads FROM user_activity WHERE user_id = ? AND action = 'file_upload'");
$stmt->execute([$_SESSION['user_id']]);
$total_analyses = $stmt->fetchColumn();

// Get successful uploads (healthy fruits)
$stmt = $pdo->prepare("SELECT COUNT(*) as healthy_fruits FROM user_activity WHERE user_id = ? AND action = 'file_upload' AND description LIKE '%success%'");
$stmt->execute([$_SESSION['user_id']]);
$healthy_fruits = $stmt->fetchColumn();

// Calculate diseased fruits
$diseased_fruits = $total_analyses - $healthy_fruits;

// Calculate accuracy rate
$accuracy_rate = $total_analyses > 0 ? round(($healthy_fruits / $total_analyses) * 100) : 0;

// Get monthly statistics for chart data
$stmt = $pdo->prepare("
    SELECT 
        MONTH(created_at) as month,
        YEAR(created_at) as year,
        COUNT(*) as count,
        SUM(CASE WHEN description LIKE '%success%' THEN 1 ELSE 0 END) as successful
    FROM user_activity 
    WHERE user_id = ? AND action = 'file_upload' 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY year DESC, month DESC
    LIMIT 6
");
$stmt->execute([$_SESSION['user_id']]);
$monthly_stats = $stmt->fetchAll();

// Get recent analysis results
$stmt = $pdo->prepare("
    SELECT * FROM user_activity 
    WHERE user_id = ? AND action = 'file_upload' 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_analyses = $stmt->fetchAll();

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
                <li><a href="history.php"><i class="fas fa-history"></i> السجل</a></li>
                <li><a href="analysis.php" class="active"><i class="fas fa-chart-bar"></i> الإحصائيات</a></li>
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
            <h1 class="page-title">تحليل النتائج والإحصائيات</h1>
        </div>

        <div class="analysis-summary">
            <div class="summary-card">
                <h3><i class="fas fa-chart-pie"></i> ملخص التحليلات</h3>
                <div class="summary-content">
                    <div class="summary-item">
                        <span class="label">إجمالي التحليلات:</span>
                        <span class="value"><?php echo $total_analyses; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">الرفعات الناجحة:</span>
                        <span class="value"><?php echo $healthy_fruits; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">الرفعات الفاشلة:</span>
                        <span class="value"><?php echo $diseased_fruits; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">معدل النجاح:</span>
                        <span class="value"><?php echo $accuracy_rate; ?>%</span>
                    </div>
                </div>
            </div>
            
            <div class="chart-container">
                <h3><i class="fas fa-chart-bar"></i> إحصائيات شهرية</h3>
                <?php if (!empty($monthly_stats)): ?>
                    <div class="monthly-chart">
                        <?php foreach ($monthly_stats as $stat): ?>
                            <div class="chart-bar">
                                <div class="bar-container">
                                    <div class="bar-success" style="height: <?php echo $stat['successful'] > 0 ? ($stat['successful'] / max(array_column($monthly_stats, 'count'))) * 100 : 0; ?>%"></div>
                                    <div class="bar-failed" style="height: <?php echo ($stat['count'] - $stat['successful']) > 0 ? (($stat['count'] - $stat['successful']) / max(array_column($monthly_stats, 'count'))) * 100 : 0; ?>%"></div>
                                </div>
                                <div class="bar-label">
                                    <?php 
                                        $months = [1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'];
                                        echo $months[$stat['month']];
                                    ?>
                                </div>
                                <div class="bar-count"><?php echo $stat['count']; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-color success"></span>
                            <span>ناجحة</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color failed"></span>
                            <span>فاشلة</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="chart-placeholder">
                        <i class="fas fa-chart-bar"></i>
                        <p>سيتم عرض الرسم البياني هنا بعد إجراء الرفعات</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="results-section">
            <h2 class="section-title"><i class="fas fa-microscope"></i> نتائج التحليل التفصيلية</h2>
            
            <div id="analysisResults">
                <?php if (!empty($recent_analyses)): ?>
                    <?php foreach ($recent_analyses as $analysis): ?>
                        <div class="analysis-item">
                            <div class="analysis-icon">
                                <i class="fas fa-<?php echo strpos($analysis['description'], 'success') !== false ? 'check-circle' : 'times-circle'; ?>"></i>
                            </div>
                            <div class="analysis-content">
                                <div class="analysis-title"><?php echo htmlspecialchars($analysis['description']); ?></div>
                                <div class="analysis-details">
                                    <span class="analysis-date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('j F Y - H:i', strtotime($analysis['created_at'])); ?>
                                    </span>
                                    <span class="analysis-status <?php echo strpos($analysis['description'], 'success') !== false ? 'success' : 'error'; ?>">
                                        <?php echo strpos($analysis['description'], 'success') !== false ? 'ناجح' : 'فاشل'; ?>
                                    </span>
                                </div>
                                <?php if (!empty($analysis['details'])): ?>
                                    <div class="analysis-extra">
                                        <?php echo htmlspecialchars($analysis['details']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="view-all-analyses">
                        <a href="history.php" class="btn btn-secondary">عرض جميع الرفعات</a>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-search"></i>
                        <h3>لا توجد نتائج رفع بعد</h3>
                        <p>قم برفع صور الفواكه لبدء التحليل والحصول على النتائج المفصلة</p>
                        <a href="upload.php" class="btn btn-primary">
                            <i class="fas fa-upload"></i> رفع الصور
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($recent_analyses)): ?>
            <div class="results-section">
                <h2 class="section-title"><i class="fas fa-history"></i> آخر الرفعات</h2>
                
                <?php foreach (array_slice($recent_analyses, 0, 3) as $analysis): ?>
                    <div class="result-item detailed">
                        <div class="result-icon">
                            <i class="fas fa-<?php echo strpos($analysis['description'], 'success') !== false ? 'check-circle' : 'times-circle'; ?>"></i>
                        </div>
                        <div class="result-info">
                            <div class="result-title"><?php echo htmlspecialchars($analysis['description']); ?></div>
                            <div class="result-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('Y-m-d', strtotime($analysis['created_at'])); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($analysis['created_at'])); ?></span>
                            </div>
                            <?php if (!empty($analysis['details'])): ?>
                                <div class="result-details">
                                    <p><?php echo htmlspecialchars($analysis['details']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="result-actions">
                            <span class="status-badge <?php echo strpos($analysis['description'], 'success') !== false ? 'status-success' : 'status-error'; ?>">
                                <?php echo strpos($analysis['description'], 'success') !== false ? 'ناجح' : 'فاشل'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="view-all-results">
                    <a href="history.php" class="btn btn-secondary">عرض جميع النتائج</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
