<?php
// Security utilities and additional protection functions
require_once 'config.php';

// Content Security Policy headers
function set_security_headers() {
    // Prevent XSS attacks
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
           "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' https://cdnjs.cloudflare.com; " .
           "connect-src 'self'";
    
    header("Content-Security-Policy: " . $csp);
}

// SQL injection prevention for search queries
function safe_search_query($search_term, $table_columns = []) {
    $search_term = sanitize_sql_like($search_term);
    $search_term = trim($search_term);
    
    // Remove potentially dangerous SQL keywords
    $dangerous_keywords = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'UNION', 'SCRIPT'];
    foreach ($dangerous_keywords as $keyword) {
        $search_term = str_ireplace($keyword, '', $search_term);
    }
    
    return '%' . $search_term . '%';
}

// File upload security
function secure_file_upload($file, $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    $validation = validate_file_upload($file);
    if (!$validation['valid']) {
        return $validation;
    }
    
    // Additional security checks
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    
    // Check file extension
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        return ['valid' => false, 'message' => 'امتداد الملف غير مسموح'];
    }
    
    // Check for embedded PHP code in images
    $file_content = file_get_contents($tmp_name);
    if (strpos($file_content, '<?php') !== false || strpos($file_content, '<?=') !== false) {
        return ['valid' => false, 'message' => 'الملف يحتوي على كود ضار'];
    }
    
    // Verify it's actually an image
    $image_info = getimagesize($tmp_name);
    if ($image_info === false) {
        return ['valid' => false, 'message' => 'الملف ليس صورة صالحة'];
    }
    
    return ['valid' => true, 'message' => 'الملف آمن'];
}

// Input sanitization for different contexts
function sanitize_for_html($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize_for_url($data) {
    return urlencode($data);
}

function sanitize_for_js($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}

// Password strength validation
function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'كلمة المرور يجب أن تحتوي على حرف كبير واحد على الأقل';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'كلمة المرور يجب أن تحتوي على حرف صغير واحد على الأقل';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'كلمة المرور يجب أن تحتوي على رقم واحد على الأقل';
    }
    
    return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
}

// Session security
function regenerate_session_id() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// IP validation and blocking
function is_ip_blocked($ip) {
    // Simple IP blocking - in production, use database or cache
    $blocked_ips = ['127.0.0.2']; // Example blocked IP
    return in_array($ip, $blocked_ips);
}

function block_suspicious_activity() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    
    if (is_ip_blocked($ip)) {
        http_response_code(403);
        die('Access denied');
    }
}

// Initialize security
block_suspicious_activity();
set_security_headers();
?>
