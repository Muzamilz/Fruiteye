<?php
// SSL/HTTPS Setup and Testing Script
// This script helps configure and test HTTPS implementation

require_once 'config.php';

$messages = [];
$errors = [];

// Check current HTTPS status
function check_https_status() {
    $status = [
        'is_https' => is_https(),
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
        'https_header' => $_SERVER['HTTPS'] ?? 'Not set',
        'forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'Not set',
        'forwarded_ssl' => $_SERVER['HTTP_X_FORWARDED_SSL'] ?? 'Not set'
    ];
    return $status;
}

// Generate self-signed certificate for development
function generate_dev_certificate() {
    $cert_dir = __DIR__ . '/ssl/';
    if (!is_dir($cert_dir)) {
        mkdir($cert_dir, 0755, true);
    }
    
    $config = [
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];
    
    $dn = [
        "countryName" => "SA",
        "stateOrProvinceName" => "Riyadh",
        "localityName" => "Riyadh",
        "organizationName" => "Fruit Eye",
        "organizationalUnitName" => "IT Department",
        "commonName" => "localhost",
        "emailAddress" => "admin@fruiteye.local"
    ];
    
    // Generate private key
    $privkey = openssl_pkey_new($config);
    
    // Generate certificate signing request
    $csr = openssl_csr_new($dn, $privkey, $config);
    
    // Generate self-signed certificate
    $x509 = openssl_csr_sign($csr, null, $privkey, 365, $config);
    
    // Export certificate and private key
    openssl_x509_export($x509, $cert_out);
    openssl_pkey_export($privkey, $key_out);
    
    // Save files
    file_put_contents($cert_dir . 'certificate.crt', $cert_out);
    file_put_contents($cert_dir . 'private.key', $key_out);
    
    return [
        'cert_path' => $cert_dir . 'certificate.crt',
        'key_path' => $cert_dir . 'private.key'
    ];
}

// Test HTTPS configuration
function test_https_config() {
    $tests = [];
    
    // Test 1: Check if HTTPS is enabled
    $tests['https_enabled'] = [
        'name' => 'HTTPS Enabled',
        'status' => is_https(),
        'message' => is_https() ? 'HTTPS is enabled' : 'HTTPS is not enabled'
    ];
    
    // Test 2: Check secure cookies
    $tests['secure_cookies'] = [
        'name' => 'Secure Cookies',
        'status' => SECURE_COOKIES,
        'message' => SECURE_COOKIES ? 'Secure cookies enabled' : 'Secure cookies disabled'
    ];
    
    // Test 3: Check HSTS header
    $headers = headers_list();
    $hsts_found = false;
    foreach ($headers as $header) {
        if (stripos($header, 'Strict-Transport-Security') !== false) {
            $hsts_found = true;
            break;
        }
    }
    
    $tests['hsts_header'] = [
        'name' => 'HSTS Header',
        'status' => $hsts_found,
        'message' => $hsts_found ? 'HSTS header is set' : 'HSTS header not found'
    ];
    
    // Test 4: Check .htaccess file
    $htaccess_exists = file_exists(__DIR__ . '/.htaccess');
    $tests['htaccess'] = [
        'name' => '.htaccess File',
        'status' => $htaccess_exists,
        'message' => $htaccess_exists ? '.htaccess file exists' : '.htaccess file missing'
    ];
    
    return $tests;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_cert'])) {
        try {
            $cert_info = generate_dev_certificate();
            $messages[] = "Self-signed certificate generated successfully!";
            $messages[] = "Certificate: " . $cert_info['cert_path'];
            $messages[] = "Private Key: " . $cert_info['key_path'];
            $messages[] = "Configure your web server to use these files.";
        } catch (Exception $e) {
            $errors[] = "Error generating certificate: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['test_https'])) {
        $test_results = test_https_config();
        foreach ($test_results as $test) {
            if ($test['status']) {
                $messages[] = "✓ " . $test['name'] . ": " . $test['message'];
            } else {
                $errors[] = "✗ " . $test['name'] . ": " . $test['message'];
            }
        }
    }
}

$https_status = check_https_status();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد SSL/HTTPS - عين الفاكهة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        .status-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .status-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .status-table th, .status-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: right;
        }
        .status-table th {
            background: #f2f2f2;
        }
        .status-good {
            color: #28a745;
            font-weight: bold;
        }
        .status-bad {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إعداد SSL/HTTPS - عين الفاكهة</h1>
        
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="status-card">
            <h2>حالة HTTPS الحالية</h2>
            <table class="status-table">
                <tr>
                    <th>المعيار</th>
                    <th>القيمة</th>
                    <th>الحالة</th>
                </tr>
                <tr>
                    <td>HTTPS مفعل</td>
                    <td><?php echo $https_status['is_https'] ? 'نعم' : 'لا'; ?></td>
                    <td class="<?php echo $https_status['is_https'] ? 'status-good' : 'status-bad'; ?>">
                        <?php echo $https_status['is_https'] ? '✓' : '✗'; ?>
                    </td>
                </tr>
                <tr>
                    <td>منفذ الخادم</td>
                    <td><?php echo htmlspecialchars($https_status['server_port']); ?></td>
                    <td class="<?php echo $https_status['server_port'] == 443 ? 'status-good' : 'status-bad'; ?>">
                        <?php echo $https_status['server_port'] == 443 ? '✓' : '✗'; ?>
                    </td>
                </tr>
                <tr>
                    <td>HTTPS Header</td>
                    <td><?php echo htmlspecialchars($https_status['https_header']); ?></td>
                    <td class="<?php echo $https_status['https_header'] !== 'Not set' ? 'status-good' : 'status-bad'; ?>">
                        <?php echo $https_status['https_header'] !== 'Not set' ? '✓' : '✗'; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="status-card">
            <h2>الإجراءات</h2>
            <form method="POST" style="display: inline;">
                <button type="submit" name="test_https" class="btn">اختبار إعدادات HTTPS</button>
            </form>
            
            <form method="POST" style="display: inline;">
                <button type="submit" name="generate_cert" class="btn btn-success">إنشاء شهادة تطوير</button>
            </form>
        </div>
        
        <div class="status-card">
            <h2>تعليمات الإعداد</h2>
            <div class="message info">
                <h3>لتفعيل HTTPS في XAMPP:</h3>
                <ol>
                    <li>افتح ملف <code>C:\xampp\apache\conf\extra\httpd-ssl.conf</code></li>
                    <li>تأكد من أن المنفذ 443 مفعل</li>
                    <li>قم بتحديث مسارات الشهادة والمفتاح الخاص</li>
                    <li>أعد تشغيل Apache</li>
                    <li>قم بزيارة <code>https://localhost/fruiteye</code></li>
                </ol>
                
                <h3>لإنتاج:</h3>
                <ol>
                    <li>احصل على شهادة SSL صالحة من مزود معتمد</li>
                    <li>قم بتكوين الخادم لاستخدام الشهادة</li>
                    <li>تأكد من تفعيل إعادة التوجيه من HTTP إلى HTTPS</li>
                    <li>اختبر جميع الوظائف</li>
                </ol>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn">العودة إلى لوحة التحكم</a>
            <a href="index.php" class="btn">الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
