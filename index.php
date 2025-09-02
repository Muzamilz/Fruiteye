<?php
require_once 'config.php';
$page_title = 'الرئيسية';
$body_class = 'landing-page';
include 'includes/header.php';
?>

<main>
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>تقنية الذكاء الاصطناعي للكشف عن جودة الفواكه</h1>
                    <p>طورنا نظامًا ذكيًا يتعرف على أنواع الفواكه المختلفة، يكتشف الأمراض التي قد تصيبها، ويقيم جودتها وحالة نضجها بدقة عالية باستخدام أحدث تقنيات التعلم العميق.</p>
                    <div class="hero-actions">
                        <a href="register.php" class="btn btn-primary btn-large">ابدأ الآن مجاناً</a>
                        <a href="#demo" class="btn btn-secondary btn-large">شاهد العرض التوضيحي</a>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://via.placeholder.com/500x400?text=صورة+توضيحية" alt="نظام تحليل الفواكه">
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title">مميزات النظام</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>التعرف على الفواكه</h3>
                    <p>يتعرف النظام على أكثر من 50 نوعًا من الفواكه المختلفة بدقة تصل إلى 98%.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bug"></i>
                    </div>
                    <h3>كشف الأمراض</h3>
                    <p>يكشف عن الأمراض والآفات التي تصيب الفواكه ويقدم تقريرًا مفصلًا عنها.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>تقييم الجودة</h3>
                    <p>يقيم جودة الفاكهة بناء على معايير الجودة العالمية ونسبة النضج.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>سجل التحليلات</h3>
                    <p>يحفظ جميع تحليلاتك السابقة لتتمكن من مراجعتها ومقارنتها في أي وقت.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <h2 class="section-title">كيف يعمل النظام؟</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>إنشاء حساب</h3>
                    <p>قم بإنشاء حساب شخصي مجاني للبدء في استخدام النظام.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>رفع الصور</h3>
                    <p>أرفع صور الفواكه التي تريد تحليل جودتها وأمراضها.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>التحليل التلقائي</h3>
                    <p>يقوم نظام الذكاء الاصطناعي بتحليل الصور وإعطاء النتائج.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>الحصول على النتائج</h3>
                    <p>استلم تقريرًا مفصلًا عن جودة الفاكهة وأي أمراض موجودة فيها.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="demo" class="demo-section">
        <div class="container">
            <h2 class="section-title">جرب النظام الآن</h2>
            <div class="demo-content">
                <p>يمكنك تجربة النظام بشكل محدود دون إنشاء حساب، ولكن للحصول على الميزات الكاملة وحفظ النتائج يلزم إنشاء حساب.</p>
                <div class="demo-actions">
                    <a href="upload.php" class="btn btn-primary">تجربة بدون حساب</a>
                    <a href="register.php" class="btn btn-secondary">إنشاء حساب للوصول الكامل</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
