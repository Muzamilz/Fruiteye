<?php
// Final Apache SSL Fix Script
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الحل النهائي لـ Apache SSL - عين الفاكهة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .solution-card {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .option-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
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
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn-secondary {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ الحل النهائي لمشكلة Apache SSL</h1>
        
        <div class="solution-card">
            <h2>🔍 تشخيص المشكلة</h2>
            <p>تم العثور على السبب الجذري للمشكلة:</p>
            <ul>
                <li>Apache يبدأ التشغيل بنجاح</li>
                <li>المشكلة في عدم تطابق اسم الخادم مع شهادة SSL</li>
                <li>الشهادة مُصدرة لـ <code>www.example.com</code></li>
                <li>الإعداد يستخدم <code>localhost:443</code></li>
            </ul>
        </div>

        <div class="option-card">
            <h2>🚀 الحل الأول: تعديل ServerName (مُطبق)</h2>
            <p>تم تغيير ServerName في httpd-ssl.conf ليتطابق مع الشهادة:</p>
            <div class="code-block">
ServerName www.example.com:443
            </div>
            <p><strong>الآن جرب تشغيل Apache مرة أخرى</strong></p>
        </div>

        <div class="option-card">
            <h2>🔧 الحل الثاني: إنشاء شهادة جديدة لـ localhost</h2>
            <p>إذا كنت تفضل استخدام localhost، يمكن إنشاء شهادة جديدة:</p>
            <div class="code-block">
# في Command Prompt كمدير:
cd C:\xampp\apache\bin
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ..\conf\ssl.key\server.key -out ..\conf\ssl.crt\server.crt -config ..\conf\openssl.cnf -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
            </div>
        </div>

        <div class="option-card">
            <h2>🌐 الحل الثالث: تعديل ملف hosts</h2>
            <p>إضافة www.example.com إلى ملف hosts للإشارة إلى localhost:</p>
            <div class="code-block">
# أضف هذا السطر إلى C:\Windows\System32\drivers\etc\hosts
127.0.0.1    www.example.com
            </div>
            <p>بعدها يمكنك الوصول للتطبيق عبر: <code>https://www.example.com/fruiteye</code></p>
        </div>

        <div class="solution-card">
            <h2>📝 خطوات الاختبار</h2>
            <ol>
                <li>أعد تشغيل Apache في XAMPP Control Panel</li>
                <li>تحقق من عدم وجود أخطاء في سجل الأخطاء</li>
                <li>اختبر الوصول:
                    <ul>
                        <li><code>http://www.example.com/fruiteye</code></li>
                        <li><code>https://www.example.com/fruiteye</code></li>
                    </ul>
                </li>
                <li>إذا نجح، فعّل HTTPS في config.php</li>
            </ol>
        </div>

        <div class="option-card">
            <h2>⚙️ تفعيل HTTPS في التطبيق</h2>
            <p>بعد نجاح تشغيل Apache، غيّر في config.php:</p>
            <div class="code-block">
define('FORCE_HTTPS', true);
define('SECURE_COOKIES', true);
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn">العودة إلى التطبيق</a>
            <a href="ssl_setup.php" class="btn btn-secondary">أداة SSL</a>
        </div>
    </div>
</body>
</html>
