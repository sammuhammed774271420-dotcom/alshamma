<?php
require_once __DIR__ . '/includes/db.php';
$page_title = 'من نحن';
$about = $pdo->query("SELECT * FROM about_content LIMIT 1")->fetch();
$team = $pdo->query("SELECT * FROM team ORDER BY order_by ASC")->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>من <span>نحن</span></h1>
        <p>تعرف على شركة السلام للعقارات ورؤيتنا وفريقنا المتميز</p>
        <div class="breadcrumb">
            <a href="/index.php">الرئيسية</a>
            <span>›</span>
            <span>من نحن</span>
        </div>
    </div>
</div>

<!-- ABOUT SECTION -->
<section class="about-section" style="padding:80px 0;">
    <div class="container">
        <div style="display:grid;grid-template-columns:<?php echo (!empty($about['image_path']) && file_exists(__DIR__ . '/' . $about['image_path'])) ? '1fr 1.3fr' : '1fr'; ?>;gap:50px;align-items:center;">
            <?php if (!empty($about['image_path']) && file_exists(__DIR__ . '/' . $about['image_path'])): ?>
            <div style="position:relative;">
                <img src="/<?php echo htmlspecialchars($about['image_path']); ?>" alt="من نحن" style="width:100%;height:420px;object-fit:cover;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.15);">
                <div style="position:absolute;bottom:-20px;right:-20px;background:var(--gold);color:var(--dark);padding:20px 25px;border-radius:8px;font-weight:900;text-align:center;">
                    <strong style="display:block;font-size:40px;line-height:1;"><?php echo $about['years_exp'] ?? 15; ?>+</strong>
                    سنة خبرة
                </div>
            </div>
            <?php endif; ?>
            <div>
                <span style="color:var(--gold);font-size:13px;font-weight:700;letter-spacing:3px;text-transform:uppercase;">تعرف علينا</span>
                <h2 style="font-size:clamp(26px,3.5vw,38px);font-weight:900;color:var(--dark);margin:10px 0 20px;line-height:1.3;"><?php echo htmlspecialchars($about['title'] ?? 'من نحن - شركة السلام للعقارات'); ?></h2>
                <div style="width:80px;height:3px;background:var(--gold);margin-bottom:25px;"></div>
                <div style="color:#555;font-size:15px;line-height:2;"><?php echo nl2br(htmlspecialchars($about['content'] ?? '')); ?></div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $about['years_exp'] ?? 15; ?>" data-suffix="+">0</span>
                <span class="stat-label">سنة خبرة</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $about['projects_count'] ?? 200; ?>" data-suffix="+">0</span>
                <span class="stat-label">مشروع منجز</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $about['clients_count'] ?? 500; ?>" data-suffix="+">0</span>
                <span class="stat-label">عميل راضٍ</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" data-target="<?php echo $about['awards_count'] ?? 30; ?>" data-suffix="+">0</span>
                <span class="stat-label">جائزة تميز</span>
            </div>
        </div>
    </div>
</section>

<!-- VISION & MISSION -->
<section class="vision-mission">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">توجهاتنا</span>
            <h2>رؤيتنا ورسالتنا</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
        </div>
        <div class="vision-mission-grid">
            <div class="vm-card">
                <div class="vm-icon"><i class="fas fa-eye"></i></div>
                <h3>رؤيتنا</h3>
                <p><?php echo nl2br(htmlspecialchars($about['vision'] ?? 'أن نكون الشركة العقارية الأولى في المنطقة من حيث الجودة والموثوقية وخدمة العملاء.')); ?></p>
            </div>
            <div class="vm-card">
                <div class="vm-icon"><i class="fas fa-bullseye"></i></div>
                <h3>رسالتنا</h3>
                <p><?php echo nl2br(htmlspecialchars($about['mission'] ?? 'تقديم خدمات عقارية متكاملة تجمع بين الاحترافية والأمانة لتحقيق رضا عملائنا.')); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- VALUES -->
<section style="padding:80px 0;background:var(--white);">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">قيمنا</span>
            <h2>القيم التي نؤمن بها</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
        </div>
        <div class="whyus-grid">
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-handshake"></i></div>
                <h4>الأمانة</h4>
                <p>نؤمن أن الأمانة والصدق هما أساس أي علاقة ناجحة مع عملائنا وشركائنا.</p>
            </div>
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-star"></i></div>
                <h4>التميز</h4>
                <p>نسعى دائماً نحو التميز في كل ما نقدمه من خدمات ومشاريع عقارية.</p>
            </div>
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-lightbulb"></i></div>
                <h4>الابتكار</h4>
                <p>نبتكر حلولاً عقارية مبتكرة تواكب متطلبات السوق وتوقعات عملائنا.</p>
            </div>
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas fa-leaf"></i></div>
                <h4>الاستدامة</h4>
                <p>نلتزم بمعايير البناء المستدام والمحافظة على البيئة في جميع مشاريعنا.</p>
            </div>
        </div>
    </div>
</section>

<!-- TEAM -->
<?php if (!empty($team)): ?>
<section class="team-section" style="background:var(--gray-light);">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">فريقنا</span>
            <h2>تعرف على فريقنا</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
            <p>فريق متخصص من الخبراء والمحترفين في مجال العقارات</p>
        </div>
        <div class="team-grid">
            <?php foreach ($team as $member): ?>
            <div class="team-card">
                <div class="team-img">
                    <?php if (!empty($member['image_path']) && file_exists(__DIR__ . '/' . $member['image_path'])): ?>
                        <img src="/<?php echo htmlspecialchars($member['image_path']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                    <?php else: ?>
                        <div class="team-no-img"><i class="fas fa-user-circle"></i></div>
                    <?php endif; ?>
                </div>
                <div class="team-info">
                    <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                    <span class="position"><?php echo htmlspecialchars($member['position'] ?? ''); ?></span>
                    <div class="team-links">
                        <?php if (!empty($member['email'])): ?>
                        <a href="mailto:<?php echo $member['email']; ?>"><i class="fas fa-envelope"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($member['phone'])): ?>
                        <a href="tel:<?php echo $member['phone']; ?>"><i class="fas fa-phone"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="cta-strip">
    <div class="container">
        <h2>هل تريد الانضمام إلى <span>عائلتنا؟</span></h2>
        <p>تواصل معنا الآن للحصول على استشارة عقارية مجانية</p>
        <div class="cta-btns">
            <a href="/contact.php" class="btn-primary" style="display:inline-block;padding:14px 40px;font-weight:700;border-radius:4px;">تواصل معنا</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
