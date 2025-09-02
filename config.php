<?php
// config.php - Configuration file for the Fruit Analysis System

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fruit_analysis');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'عين الفاكهة');
define('SITE_URL', 'https://localhost');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Theme settings
define('DEFAULT_THEME', 'dark');

// Security configuration
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 300); // 5 minutes
define('SESSION_TIMEOUT', 7200); // 2 hours

// HTTPS/SSL Configuration
define('FORCE_HTTPS', false); // Temporarily disabled due to Apache SSL issue
define('SECURE_COOKIES', false); // Disabled until HTTPS is working
define('HSTS_MAX_AGE', 31536000); // 1 year

// JWT settings
define('JWT_SECRET', 'your-super-secret-jwt-key-change-this-in-production');
define('JWT_SECRET_KEY', 'your-super-secret-jwt-key-change-this-in-production'); // Backward compatibility
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 3600); // 1 hour
define('JWT_REFRESH_EXPIRATION', 86400); // 24 hours

// Load JWT library
require_once __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load security functions
require_once __DIR__ . '/security.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
function get_db_connection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Security and validation functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function sanitize_sql_like($string) {
    return str_replace(['%', '_'], ['\%', '\_'], $string);
}

function rate_limit_check($action, $max_attempts = 5, $time_window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $action . '_' . $ip;
    
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    $now = time();
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = ['count' => 1, 'first_attempt' => $now];
        return true;
    }
    
    $rate_data = $_SESSION['rate_limits'][$key];
    
    // Reset if time window has passed
    if ($now - $rate_data['first_attempt'] > $time_window) {
        $_SESSION['rate_limits'][$key] = ['count' => 1, 'first_attempt' => $now];
        return true;
    }
    
    // Check if limit exceeded
    if ($rate_data['count'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION['rate_limits'][$key]['count']++;
    return true;
}

function validate_email($email) {
    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
    return $email && strlen($email) <= 255;
}

function validate_password($password) {
    return strlen($password) >= 6 && strlen($password) <= 255;
}

function validate_name($name) {
    $name = trim($name);
    return strlen($name) >= 2 && strlen($name) <= 100 && preg_match('/^[\p{L}\p{N}\s\-_.]+$/u', $name);
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function escape_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function sanitize_filename($filename) {
    // Remove path traversal attempts and dangerous characters
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
    return substr($filename, 0, 255);
}

function validate_file_upload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = MAX_FILE_SIZE;
    
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'خطأ في رفع الملف'];
    }
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'message' => 'حجم الملف كبير جداً'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['valid' => false, 'message' => 'نوع الملف غير مدعوم'];
    }
    
    return ['valid' => true, 'message' => 'الملف صالح'];
}

function redirect($url) {
    header("Location: $url");
    exit();
}

// JWT Helper Functions
function generate_jwt_token($user_data) {
    $payload = [
        'iss' => SITE_URL,
        'aud' => SITE_URL,
        'iat' => time(),
        'exp' => time() + JWT_EXPIRATION,
        'user_id' => $user_data['id'],
        'user_name' => $user_data['name'],
        'user_email' => $user_data['email'],
        'is_admin' => $user_data['is_admin']
    ];
    
    return JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
}

function verify_jwt_token($token) {
    try {
        // Simple decode without Key class - older JWT library compatibility
        $decoded = Firebase\JWT\JWT::decode($token, JWT_SECRET, [JWT_ALGORITHM]);
        return (array) $decoded;
    } catch (Firebase\JWT\ExpiredException $e) {
        error_log("JWT expired: " . $e->getMessage());
        return false;
    } catch (Firebase\JWT\SignatureInvalidException $e) {
        error_log("JWT signature invalid: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("JWT verification error: " . $e->getMessage());
        return false;
    }
}

function get_jwt_from_request() {
    if (isset($_COOKIE['jwt_token'])) {
        return $_COOKIE['jwt_token'];
    }
    
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                return $matches[1];
            }
        }
    }
    
    return null;
}

function set_jwt_cookie($token) {
    $cookie_options = [
        'expires' => time() + JWT_EXPIRATION,
        'path' => '/',
        'domain' => '',
        'secure' => SECURE_COOKIES, // Only send over HTTPS when enabled
        'httponly' => true, // Not accessible via JavaScript
        'samesite' => 'Strict' // CSRF protection
    ];
    
    setcookie('jwt_token', $token, $cookie_options);
}

function clear_jwt_cookie() {
    setcookie('jwt_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => SECURE_COOKIES,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

function is_logged_in() {
    $token = get_jwt_from_request();
    if (!$token) {
        return false;
    }
    
    $decoded = verify_jwt_token($token);
    if (!$decoded) {
        clear_jwt_cookie();
        return false;
    }
    
    $_SESSION['user_id'] = $decoded['user_id'];
    $_SESSION['user_name'] = $decoded['user_name'];
    $_SESSION['user_email'] = $decoded['user_email'];
    $_SESSION['is_admin'] = $decoded['is_admin'];
    
    return true;
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function get_logged_user() {
    if (is_logged_in()) {
        try {
            start_secure_session();
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT id, name, email, is_admin, avatar, phone FROM users WHERE id = ? AND is_active = 1");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                return $user;
            }
        } catch (PDOException $e) {
            error_log("Error fetching user data: " . $e->getMessage());
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'is_admin' => $_SESSION['is_admin'] ?? false
        ];
    }
    return null;
}

// Start secure session with HTTPS enforcement
function start_secure_session() {
    // Enforce HTTPS before starting session
    enforce_https();
    
    if (session_status() == PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', SECURE_COOKIES ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        session_start();
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// HTTPS enforcement function
function enforce_https() {
    if (FORCE_HTTPS && !is_https()) {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirect_url, true, 301);
        exit();
    }
}

// Check if connection is HTTPS
function is_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || $_SERVER['SERVER_PORT'] == 443
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
}

function authenticate_user($email, $password) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->execute([$user['id']]);
            
            $jwt_token = generate_jwt_token($user);
            set_jwt_cookie($jwt_token);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            log_user_activity($user['id'], 'login', 'User logged in successfully with JWT');
            
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

function register_user($name, $email, $password) {
    try {
        // Additional validation
        if (!validate_name($name)) {
            return ['success' => false, 'message' => 'الاسم غير صالح'];
        }
        if (!validate_email($email)) {
            return ['success' => false, 'message' => 'البريد الإلكتروني غير صالح'];
        }
        if (!validate_password($password)) {
            return ['success' => false, 'message' => 'كلمة المرور غير صالحة'];
        }
        
        $pdo = get_db_connection();
        
        // Check if email already exists (SQL injection safe)
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        if ($check_stmt->fetch()) {
            return ['success' => false, 'message' => 'البريد الإلكتروني مسجل مسبقاً'];
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user (SQL injection safe with prepared statements)
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            filter_var($email, FILTER_SANITIZE_EMAIL),
            $hashed_password
        ]);
        
        $user_id = $pdo->lastInsertId();
        
        log_user_activity($user_id, 'register', 'New user registered');
        
        return ['success' => true, 'message' => 'تم إنشاء الحساب بنجاح', 'user_id' => $user_id];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'حدث خطأ أثناء إنشاء الحساب'];
    }
}

function log_user_activity($user_id, $action, $description = null) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            (int)$user_id, // Type casting for security
            htmlspecialchars($action, ENT_QUOTES, 'UTF-8'),
            $description ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : null,
            filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: 'unknown',
            htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? '', ENT_QUOTES, 'UTF-8')
        ]);
    } catch (PDOException $e) {
        error_log("Activity logging error: " . $e->getMessage());
    }
}

function logout_user() {
    if (is_logged_in()) {
        log_user_activity($_SESSION['user_id'], 'logout', 'User logged out');
    }
    
    clear_jwt_cookie();
    session_destroy();
    session_start();
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
