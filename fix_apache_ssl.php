<?php
// Apache SSL Fix Script
// This script helps diagnose and fix Apache SSL configuration issues

$fixes = [];
$errors = [];

// Check if SSL certificates exist
function check_ssl_certificates() {
    $cert_paths = [
        'certificate' => 'C:/xampp/apache/conf/ssl.crt/server.crt',
        'private_key' => 'C:/xampp/apache/conf/ssl.key/server.key'
    ];
    
    $status = [];
    foreach ($cert_paths as $type => $path) {
        $status[$type] = [
            'path' => $path,
            'exists' => file_exists($path),
            'readable' => file_exists($path) && is_readable($path)
        ];
    }
    
    return $status;
}

// Generate minimal SSL configuration
function generate_minimal_ssl_config() {
    return '
# Minimal SSL Configuration for XAMPP
Listen 443

<VirtualHost _default_:443>
    DocumentRoot "C:/xampp/htdocs"
    ServerName localhost:443
    
    SSLEngine on
    SSLCertificateFile "conf/ssl.crt/server.crt"
    SSLCertificateKeyFile "conf/ssl.key/server.key"
    
    # Basic security
    SSLProtocol all -SSLv2 -SSLv3
    SSLCipherSuite HIGH:MEDIUM:!aNULL:!MD5
    
    <FilesMatch "\.(cgi|shtml|phtml|php)$">
        SSLOptions +StdEnvVars
    </FilesMatch>
</VirtualHost>
';
}

// Check Apache configuration
function check_apache_config() {
    $httpd_conf = 'C:/xampp/apache/conf/httpd.conf';
    $ssl_conf = 'C:/xampp/apache/conf/extra/httpd-ssl.conf';
    
    $status = [
        'httpd_conf_exists' => file_exists($httpd_conf),
        'ssl_conf_exists' => file_exists($ssl_conf),
        'ssl_module_enabled' => false,
        'ssl_include_enabled' => false
    ];
    
    if (file_exists($httpd_conf)) {
        $content = file_get_contents($httpd_conf);
        $status['ssl_module_enabled'] = strpos($content, 'LoadModule ssl_module modules/mod_ssl.so') !== false;
        $status['ssl_include_enabled'] = strpos($content, 'Include conf/extra/httpd-ssl.conf') !== false;
    }
    
    return $status;
}

$cert_status = check_ssl_certificates();
$apache_status = check_apache_config();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إصلاح Apache SSL - عين الفاكهة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
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
        .error-card {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .success-card {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
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
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 إصلاح Apache SSL</h1>
        
        <div class="error-card">
            <h2>⚠️ مشكلة في تشغيل Apache</h2>
            <p>Apache توقف بشكل غير متوقع بعد تفعيل SSL. هذا أمر شائع ويمكن إصلاحه بسهولة.</p>
        </div>
        
        <div class="status-card">
            <h2>تشخيص المشكلة</h2>
            <table class="status-table">
                <tr>
                    <th>العنصر</th>
                    <th>الحالة</th>
                    <th>الوصف</th>
                </tr>
                <tr>
                    <td>شهادة SSL</td>
                    <td class="<?php echo $cert_status['certificate']['exists'] ? 'status-good' : 'status-bad'; ?>">
                        <?php echo $cert_status['certificate']['exists'] ? '✓ موجودة' : '✗ مفقودة'; ?>
                    </td>
                    <td><?php echo $cert_status['certificate']['path']; ?></td>
                </tr>
                <tr>
                    <td>المفتاح الخاص</td>
                    <td class="<?php echo $cert_status['private_key']['exists'] ? 'status-good' : 'status-bad'; ?>">
                        <?php echo $cert_status['private_key']['exists'] ? '✓ موجود' : '✗ مفقود'; ?>
                    </td>
                    <td><?php echo $cert_status['private_key']['path']; ?></td>
                </tr>
                <tr>
                    <td>ملف httpd.conf</td>
                    <td class="<?php echo $apache_status['httpd_conf_exists'] ? 'status-good' : 'status-bad'; ?>">
                        <?php echo $apache_status['httpd_conf_exists'] ? '✓ موجود' : '✗ مفقود'; ?>
                    </td>
                    <td>C:/xampp/apache/conf/httpd.conf</td>
                </tr>
                <tr>
                    <td>ملف httpd-ssl.conf</td>
                    <td class="<?php echo $apache_status['ssl_conf_exists'] ? 'status-good' : 'status-bad'; ?>">
                        <?php echo $apache_status['ssl_conf_exists'] ? '✓ موجود' : '✗ مفقود'; ?>
                    </td>
                    <td>C:/xampp/apache/conf/extra/httpd-ssl.conf</td>
                </tr>
            </table>
        </div>
        
        <div class="success-card">
            <h2>🔧 الحل السريع</h2>
            <h3>1. تعطيل SSL مؤقتاً لبدء Apache:</h3>
            <p>في ملف <code>C:\xampp\apache\conf\httpd.conf</code>، أضف # في بداية هذا السطر:</p>
            <div class="code-block">
#Include conf/extra/httpd-ssl.conf
            </div>
            
            <h3>2. إعادة تشغيل Apache:</h3>
            <p>أعد تشغيل Apache من XAMPP Control Panel</p>
            
            <h3>3. تشغيل التطبيق بدون HTTPS مؤقتاً:</h3>
            <p>قم بزيارة: <code>http://localhost/fruiteye</code></p>
        </div>
        
        <div class="status-card">
            <h2>🔒 حل بديل: تعطيل HTTPS في التطبيق</h2>
            <p>يمكنك تعطيل إجبار HTTPS في التطبيق مؤقتاً:</p>
            <div class="code-block">
// في ملف config.php، غير هذا السطر:
define('FORCE_HTTPS', true);
// إلى:
define('FORCE_HTTPS', false);
            </div>
        </div>
        
        <div class="error-card">
            <h2>⚠️ أسباب محتملة لفشل Apache</h2>
            <ul>
                <li>منفذ 443 مستخدم من تطبيق آخر</li>
                <li>شهادات SSL مفقودة أو تالفة</li>
                <li>خطأ في تركيب httpd-ssl.conf</li>
                <li>وحدة SSL غير مثبتة بشكل صحيح</li>
                <li>مشكلة في صلاحيات الملفات</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn">العودة إلى التطبيق</a>
        </div>
    </div>
</body>
</html>
