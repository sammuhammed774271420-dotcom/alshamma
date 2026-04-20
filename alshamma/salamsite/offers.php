<?php
require_once __DIR__ . '/includes/db.php';
$page_title = 'العروض الحصرية';
$offers     = $pdo->query("SELECT * FROM offers WHERE active=1 ORDER BY order_by ASC, id DESC")->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>عروضنا <span>الحصرية</span></h1>
        <p>أحدث العروض والأصناف المتاحة من معامل الشام — استفد منها الآن قبل نفادها</p>
        <div class="breadcrumb">
            <a href="/index.php">الرئيسية</a>
            <span>›</span>
            <span>العروض</span>
        </div>
    </div>
</div>

<!-- ── قسم العروض ── -->
<section style="padding:70px 0 80px;background:var(--dark);">
    <div class="container">
        <?php if (empty($offers)): ?>
        <div style="text-align:center;padding:60px 20px;color:#555;">
            <i class="fas fa-tag" style="font-size:60px;color:rgba(184,150,62,0.3);display:block;margin-bottom:20px;"></i>
            <p style="font-size:16px;color:#888;">لا توجد عروض متاحة حالياً. سيتم إضافتها قريباً.</p>
            <a href="/index.php" style="display:inline-block;margin-top:20px;padding:12px 30px;background:var(--gold);color:var(--dark);border-radius:4px;font-weight:700;">العودة للرئيسية</a>
        </div>
        <?php else: ?>

        <div class="offers-page-grid">
            <?php foreach ($offers as $off): ?>
            <div class="offer-full-card">
                <!-- الصورة مع التراكب -->
                <div class="offer-full-img" style="position:relative;aspect-ratio:16/9;overflow:hidden;border-radius:12px 12px 0 0;">
                    <?php if (!empty($off['image_path']) && file_exists(__DIR__ . '/' . $off['image_path'])): ?>
                        <img src="/<?php echo htmlspecialchars($off['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($off['title']); ?>"
                             style="width:100%;height:100%;object-fit:cover;transition:transform .5s ease;"
                             onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
                    <?php else: ?>
                        <div class="offer-no-img"><i class="fas fa-tag"></i></div>
                    <?php endif; ?>

                    <!-- التدرج السفلي -->
                    <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,0.65) 100%);z-index:1;pointer-events:none;"></div>

                    <!-- الشارة -->
                    <?php if (!empty($off['badge_text'])): ?>
                    <span class="offer-badge"><?php echo htmlspecialchars($off['badge_text']); ?></span>
                    <?php endif; ?>

                    <!-- السعر -->
                    <?php if (!empty($off['price'])): ?>
                    <div class="offer-price-tag"><?php echo htmlspecialchars($off['price']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- المحتوى -->
                <div class="offer-full-body" style="background:var(--dark2);border-radius:0 0 12px 12px;">
                    <h3 style="color:#fff;font-size:18px;font-weight:800;margin-bottom:6px;"><?php echo htmlspecialchars($off['title']); ?></h3>
                    <?php if (!empty($off['subtitle'])): ?>
                    <p class="offer-sub"><?php echo htmlspecialchars($off['subtitle']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($off['description'])): ?>
                    <p class="offer-desc" style="color:#aaa;"><?php echo nl2br(htmlspecialchars($off['description'])); ?></p>
                    <?php endif; ?>
                    <a href="<?php echo htmlspecialchars(!empty($off['link']) ? $off['link'] : '/contact.php'); ?>" class="offer-cta-btn">
                        <i class="fas fa-phone-alt"></i> اطلب الآن
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</section>

<!-- CTA -->
<section class="cta-strip">
    <div class="container">
        <h2>هل تريد <span>الاستفادة</span> من عروضنا؟</h2>
        <p>تواصل مع فريقنا الآن للحصول على استشارة مجانية وأفضل الأسعار</p>
        <div class="cta-btns">
            <a href="/contact.php" class="btn-primary" style="display:inline-block;padding:14px 40px;font-weight:700;border-radius:4px;">تواصل معنا الآن</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
