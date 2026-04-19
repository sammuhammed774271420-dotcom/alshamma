<?php
require_once __DIR__ . '/includes/db.php';
$page_title = 'الخدمات';
$services = $pdo->query("SELECT * FROM services WHERE active=1 ORDER BY order_by ASC")->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>خدماتنا <span>المتميزة</span></h1>
        <p>نقدم مجموعة شاملة من الخدمات العقارية لتلبية جميع احتياجاتكم</p>
        <div class="breadcrumb">
            <a href="/index.php">الرئيسية</a>
            <span>›</span>
            <span>الخدمات</span>
        </div>
    </div>
</div>

<section class="services-page">
    <div class="container">
        <?php if (empty($services)): ?>
        <div class="empty-state">
            <i class="fas fa-concierge-bell"></i>
            <p>لا توجد خدمات متاحة حالياً. سيتم إضافتها قريباً.</p>
        </div>
        <?php else: ?>
        <?php foreach ($services as $i => $service): ?>
        <div class="service-detail-card <?php echo $i % 2 !== 0 ? 'reverse' : ''; ?>" style="margin-bottom:30px;">
            <div class="service-detail-img">
                <?php if (!empty($service['image_path']) && file_exists(__DIR__ . '/' . $service['image_path'])): ?>
                    <img src="/<?php echo htmlspecialchars($service['image_path']); ?>" alt="<?php echo htmlspecialchars($service['name']); ?>">
                <?php else: ?>
                    <div class="service-detail-no-img">
                        <i class="fas <?php echo htmlspecialchars($service['icon'] ?? 'fa-building'); ?>"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="service-detail-body">
                <div class="service-detail-icon">
                    <i class="fas <?php echo htmlspecialchars($service['icon'] ?? 'fa-building'); ?>"></i>
                </div>
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($service['description'] ?? '')); ?></p>
                <div style="margin-top:20px;">
                    <a href="/contact.php" style="display:inline-block;padding:10px 24px;background:var(--gold);color:var(--dark);border-radius:4px;font-weight:700;font-size:14px;">
                        استفسر الآن <i class="fas fa-arrow-left" style="margin-right:6px;"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- WHY US -->
<section class="whyus-section">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">لماذا نحن</span>
            <h2>لماذا تختارنا لخدماتك العقارية؟</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
        </div>
        <div class="whyus-grid">
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-clock"></i></div>
                <h4>سرعة التنفيذ</h4>
                <p>نلتزم بالمواعيد والجداول الزمنية المتفق عليها لضمان رضا عملائنا</p>
            </div>
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-tag"></i></div>
                <h4>أسعار تنافسية</h4>
                <p>نقدم خدمات عالية الجودة بأسعار منافسة تناسب جميع الميزانيات</p>
            </div>
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-certificate"></i></div>
                <h4>ضمان الجودة</h4>
                <p>جميع خدماتنا تخضع لمعايير جودة صارمة لضمان أفضل النتائج</p>
            </div>
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-phone-alt"></i></div>
                <h4>دعم على مدار الساعة</h4>
                <p>فريقنا متاح دائماً للإجابة على استفساراتكم وحل مشاكلكم</p>
            </div>
        </div>
    </div>
</section>

<section class="cta-strip">
    <div class="container">
        <h2>هل تحتاج إلى <span>خدمة عقارية؟</span></h2>
        <p>تواصل معنا الآن ودعنا نساعدك في الحصول على ما تحتاجه</p>
        <div class="cta-btns">
            <a href="/contact.php" class="btn-primary" style="display:inline-block;padding:14px 40px;font-weight:700;border-radius:4px;">تواصل معنا الآن</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
