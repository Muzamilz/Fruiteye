<?php
// Apache SSL Fix Instructions for Fruit Disease Detection App
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
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1, h2, h3 {
            color: #333;
        }
        .step-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .warning-card {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .success-card {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
            direction: ltr;
            text-align: left;
        }
        .file-path {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 3px;
            font-family: monospace;
            direction: ltr;
            display: inline-block;
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
        .step-number {
            background: #007bff;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 إصلاح Apache SSL - دليل شامل</h1>
        
        <div class="warning-card">
            <h2>⚠️ المشكلة الحالية</h2>
            <p>Apache يتوقف عند تشغيل SSL بسبب تعارض في الإعدادات. تم إنشاء ملفات إعداد جديدة لحل المشكلة.</p>
        </div>

        <div class="step-card">
            <h2><span class="step-number">1</span>إيقاف Apache حالياً</h2>
            <p>في XAMPP Control Panel، اضغط "Stop" لـ Apache إذا كان يعمل.</p>
        </div>

        <div class="step-card">
            <h2><span class="step-number">2</span>نسخ احتياطي من الملفات الحالية</h2>
            <p>انسخ الملفات التالية كنسخة احتياطية:</p>
            <ul>
                <li><span class="file-path">C:\xampp\apache\conf\httpd.conf</span> → <span class="file-path">httpd.conf.backup</span></li>
                <li><span class="file-path">C:\xampp\apache\conf\extra\httpd-ssl.conf</span> → <span class="file-path">httpd-ssl.conf.backup</span></li>
            </ul>
        </div>

        <div class="step-card">
            <h2><span class="step-number">3</span>استبدال ملفات الإعداد</h2>
            <p>استبدل الملفات الحالية بالملفات الجديدة:</p>
            
            <h3>أ. استبدال httpd.conf:</h3>
            <p>انسخ محتوى الملف:</p>
            <div class="code-block">C:\xampp\apache\conf\httpd_complete.conf</div>
            <p>إلى:</p>
            <div class="code-block">C:\xampp\apache\conf\httpd.conf</div>
            
            <h3>ب. استبدال httpd-ssl.conf:</h3>
            <p>انسخ محتوى الملف:</p>
            <div class="code-block">C:\xampp\apache\conf\extra\httpd-ssl_complete.conf</div>
            <p>إلى:</p>
            <div class="code-block">C:\xampp\apache\conf\extra\httpd-ssl.conf</div>
        </div>

        <div class="step-card">
            <h2><span class="step-number">4</span>التحقق من شهادات SSL</h2>
            <p>تأكد من وجود الملفات التالية:</p>
            <ul>
                <li><span class="file-path">C:\xampp\apache\conf\ssl.crt\server.crt</span></li>
                <li><span class="file-path">C:\xampp\apache\conf\ssl.key\server.key</span></li>
            </ul>
            <p>إذا لم تكن موجودة، استخدم <a href="ssl_setup.php">أداة إنشاء الشهادات</a></p>
        </div>

        <div class="step-card">
            <h2><span class="step-number">5</span>بدء تشغيل Apache</h2>
            <p>في XAMPP Control Panel:</p>
            <ol>
                <li>اضغط "Start" لـ Apache</li>
                <li>تحقق من أن المنافذ 80 و 443 تعمل</li>
                <li>إذا فشل التشغيل، اضغط "Logs" لرؤية الأخطاء</li>
            </ol>
        </div>

        <div class="step-card">
            <h2><span class="step-number">6</span>اختبار الإعداد</h2>
            <p>بعد تشغيل Apache بنجاح:</p>
            <ol>
                <li>زر: <code>http://localhost/fruiteye</code></li>
                <li>زر: <code>https://localhost/fruiteye</code></li>
                <li>تأكد من إعادة التوجيه التلقائي من HTTP إلى HTTPS</li>
            </ol>
        </div>

        <div class="success-card">
            <h2>✅ بعد نجاح التشغيل</h2>
            <p>عندما يعمل Apache بنجاح، قم بتفعيل HTTPS في التطبيق:</p>
            <div class="code-block">
// في ملف config.php، غير:
define('FORCE_HTTPS', false);
define('SECURE_COOKIES', false);

// إلى:
define('FORCE_HTTPS', true);
define('SECURE_COOKIES', true);
            </div>
        </div>

        <div class="warning-card">
            <h2>🔍 حل المشاكل المحتملة</h2>
            <h3>إذا استمر فشل Apache:</h3>
            <ul>
                <li><strong>منفذ 443 مستخدم:</strong> أغلق Skype أو أي تطبيق يستخدم المنفذ 443</li>
                <li><strong>شهادات مفقودة:</strong> استخدم ssl_setup.php لإنشاء شهادات جديدة</li>
                <li><strong>مشكلة في الصلاحيات:</strong> شغل XAMPP كمدير</li>
                <li><strong>Windows Firewall:</strong> أضف استثناء للمنفذ 443</li>
            </ul>
            
            <h3>فحص سجلات الأخطاء:</h3>
            <p>راجع هذه الملفات للحصول على تفاصيل الأخطاء:</p>
            <ul>
                <li><span class="file-path">C:\xampp\apache\logs\error.log</span></li>
                <li><span class="file-path">C:\xampp\apache\logs\ssl_error.log</span></li>
            </ul>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn">العودة إلى التطبيق</a>
            <a href="ssl_setup.php" class="btn">أداة SSL</a>
        </div>
    </div>
</body>
</html>
