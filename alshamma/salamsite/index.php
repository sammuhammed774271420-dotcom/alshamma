<?php
require_once __DIR__ . '/includes/db.php';

$sliders           = $pdo->query("SELECT * FROM slider_images WHERE active=1 ORDER BY order_by ASC")->fetchAll();
$featured_projects = $pdo->query("SELECT * FROM projects ORDER BY featured DESC, id DESC LIMIT 6")->fetchAll();
$services          = $pdo->query("SELECT * FROM services WHERE active=1 ORDER BY order_by ASC LIMIT 6")->fetchAll();
$about             = $pdo->query("SELECT * FROM about_content LIMIT 1")->fetch();
$settings          = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
$why_us_items      = $pdo->query("SELECT * FROM why_us_items WHERE active=1 ORDER BY order_by ASC")->fetchAll();
$home_offers       = $pdo->query("SELECT * FROM offers WHERE active=1 ORDER BY order_by ASC, id DESC")->fetchAll();

// Sections visibility
$sections_raw = $pdo->query("SELECT section_key, active FROM home_sections")->fetchAll();
$sections = [];
foreach ($sections_raw as $s) $sections[$s['section_key']] = (bool)$s['active'];
$sec = function($key) use ($sections) { return $sections[$key] ?? true; };

// About features
$about_features = [];
if (!empty($about['features'])) {
    $about_features = json_decode($about['features'], true) ?: [];
}
if (empty($about_features)) {
    $about_features = ['فريق متخصص ذو خبرة واسعة','شفافية كاملة في التعاملات','ضمان أعلى معايير الجودة','دعم ما بعد البيع'];
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<?php if ($sec('slider')): ?>
<!-- ═══ HERO SLIDER ═══════════════════════════════════════ -->
<section class="hero-slider">
    <?php if (empty($sliders)): ?>
    <div class="slide active">
        <div class="slide-no-img"><i class="fas fa-city hero-icon"></i></div>
        <div class="slide-content">
            <h2>مرحباً بكم في <span><?php echo htmlspecialchars($settings['site_name'] ?? 'معامل الشام للخبز العربي'); ?></span></h2>
            <p><?php echo htmlspecialchars($settings['tagline'] ?? 'جودة الخبز العربي الأصيل بأعلى مستويات النظافة والاحترافية'); ?></p>
            <div class="slide-btns">
                <a href="/projects.php" class="btn-primary">استعرض منتجاتنا</a>
                <a href="/contact.php" class="btn-outline">تواصل معنا</a>
            </div>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($sliders as $i => $slide): ?>
    <div class="slide<?php echo $i===0?' active':''; ?>">
        <?php if (!empty($slide['image_path']) && file_exists(__DIR__ . '/' . $slide['image_path'])): ?>
            <img src="/<?php echo htmlspecialchars($slide['image_path']); ?>" alt="<?php echo htmlspecialchars($slide['title'] ?? ''); ?>" class="slide-bg">
        <?php else: ?>
            <div class="slide-no-img"><i class="fas fa-city hero-icon"></i></div>
        <?php endif; ?>
        <div class="slide-content" style="animation: fadeInUp 1s ease-out;">
            <?php if (!empty($slide['title'])): ?>
            <h2><?php echo nl2br(htmlspecialchars($slide['title'])); ?></h2>
            <?php else: ?>
            <h2>مرحباً بكم في <span><?php echo htmlspecialchars($settings['site_name'] ?? 'معامل الشام للخبز العربي'); ?></span></h2>
            <?php endif; ?>
            <?php if (!empty($slide['subtitle'])): ?>
            <p><?php echo htmlspecialchars($slide['subtitle']); ?></p>
            <?php endif; ?>
            <div class="slide-btns" style="animation: fadeInUp 1.2s ease-out;">
                <a href="<?php echo !empty($slide['link']) ? htmlspecialchars($slide['link']) : '/projects.php'; ?>" class="btn-primary">اكتشف المزيد</a>
                <a href="/contact.php" class="btn-outline">تواصل معنا</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <?php if (count($sliders) > 1): ?>
    <button class="slider-arrow prev"><i class="fas fa-chevron-right"></i></button>
    <button class="slider-arrow next"><i class="fas fa-chevron-left"></i></button>
    <div class="slider-controls">
        <?php for ($i = 0; $i < count($sliders); $i++): ?>
        <button class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>"></button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($sec('stats')): ?>
<!-- ═══ STATS ══════════════════════════════════════════════ -->
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
<?php endif; ?>

<?php if ($sec('services') && !empty($services)): ?>
<!-- ═══ SERVICES ════════════════════════════════════════════ -->
<section class="services-section">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">ما نقدمه</span>
            <h2>خدماتنا المتميزة</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
            <p>نقدم مجموعة متكاملة من منتجات الخبز العربي الطازج والخدمات المتميزة</p>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
            <div class="service-card">
                <div class="service-icon"><i class="fas <?php echo htmlspecialchars($service['icon'] ?? 'fa-building'); ?>"></i></div>
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo htmlspecialchars($service['description'] ?? ''); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="/services.php" class="btn-primary" style="display:inline-block;padding:14px 40px;font-size:16px;font-weight:700;background:var(--gold);color:var(--dark);border-radius:4px;">عرض جميع الخدمات</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($sec('projects') && !empty($featured_projects)): ?>
<!-- ═══ PROJECTS ═════════════════════════════════════════════ -->
<section class="projects-section">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">أعمالنا</span>
            <h2>أبرز منتجاتنا</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
            <p>نخبة من منتجاتنا المتميزة من الخبز العربي الطازج التي تشهد على خبرتنا وجودة عملنا</p>
        </div>
        <div class="projects-grid">
            <?php foreach ($featured_projects as $project): ?>
            <div class="project-card">
                <div class="project-img">
                    <?php if (!empty($project['image_path']) && file_exists(__DIR__ . '/' . $project['image_path'])): ?>
                        <img src="/<?php echo htmlspecialchars($project['image_path']); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>">
                    <?php else: ?>
                        <div class="project-no-img"><i class="fas fa-bread-slice"></i></div>
                    <?php endif; ?>
                    <div class="project-overlay"></div>
                    <span class="project-status"><?php echo htmlspecialchars($project['status']); ?></span>
                </div>
                <div class="project-info">
                    <h3><?php echo htmlspecialchars($project['name']); ?></h3>
                    <div class="project-meta">
                        <?php if (!empty($project['location'])): ?>
                        <span><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($project['location']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($project['area'])): ?>
                        <span><i class="fas fa-ruler-combined"></i><?php echo htmlspecialchars($project['area']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($project['price'])): ?>
                    <div class="project-price"><?php echo htmlspecialchars($project['price']); ?></div>
                    <?php endif; ?>
                    <a href="/project-detail.php?id=<?php echo $project['id']; ?>" class="project-btn">عرض التفاصيل <i class="fas fa-arrow-left"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:40px;">
            <a href="/projects.php" class="btn-primary" style="display:inline-block;padding:14px 40px;font-size:16px;font-weight:700;background:var(--dark);color:var(--white);border-radius:4px;">عرض جميع المنتجات</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($sec('offers') && !empty($home_offers)): ?>
<!-- ═══ OFFERS CAROUSEL ══════════════════════════════════════ -->
<section class="offers-section">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">فرص لا تُفوَّت</span>
            <h2>عروضنا الحصرية</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
            <p>أحدث العروض والأصناف المتاحة من معامل الشام — استفد منها الآن</p>
        </div>

        <div class="offers-carousel-wrap" id="offersCarouselWrap">
            <button class="offers-arrow prev" id="offersPrev" aria-label="السابق">
                <i class="fas fa-chevron-right"></i>
            </button>

            <div class="offers-track-outer" id="offersTrackOuter">
                <div class="offers-track" id="offersTrack">
                    <?php foreach ($home_offers as $off): ?>
                    <div class="offer-card" data-link="<?php echo htmlspecialchars(!empty($off['link']) ? $off['link'] : '/contact.php'); ?>">
                        <?php if (!empty($off['image_path']) && file_exists(__DIR__ . '/' . $off['image_path'])): ?>
                            <img class="offer-card-img" src="/<?php echo htmlspecialchars($off['image_path']); ?>" alt="<?php echo htmlspecialchars($off['title']); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="offer-card-no-img"><i class="fas fa-tag"></i></div>
                        <?php endif; ?>
                        <div class="offer-card-overlay"></div>

                        <?php if (!empty($off['badge_text'])): ?>
                        <span class="offer-badge"><?php echo htmlspecialchars($off['badge_text']); ?></span>
                        <?php endif; ?>

                        <?php if (!empty($off['price'])): ?>
                        <div class="offer-price-tag"><?php echo htmlspecialchars($off['price']); ?></div>
                        <?php endif; ?>

                        <div class="offer-card-body">
                            <h3 class="offer-card-title"><?php echo htmlspecialchars($off['title']); ?></h3>
                            <?php if (!empty($off['subtitle'])): ?>
                            <p class="offer-card-sub"><?php echo htmlspecialchars($off['subtitle']); ?></p>
                            <?php endif; ?>
                            <a href="<?php echo htmlspecialchars(!empty($off['link']) ? $off['link'] : '/contact.php'); ?>" class="offer-card-btn" onclick="event.stopPropagation()">
                                <i class="fas fa-phone-alt"></i> اطلب الآن
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button class="offers-arrow next" id="offersNext" aria-label="التالي">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <!-- نقاط التنقل -->
        <div class="offers-dots" id="offersDots"></div>

        <!-- رابط كل العروض -->
        <div class="offers-view-all">
            <a href="/offers.php">
                <i class="fas fa-th-large"></i> كل العروض
                <i class="fas fa-arrow-left" style="font-size:12px;"></i>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($sec('about')): ?>
<!-- ═══ ABOUT PREVIEW ════════════════════════════════════════ -->
<section class="about-preview">
    <div class="container">
        <div class="about-grid">
            <?php if (!empty($about['image_path']) && file_exists(__DIR__ . '/' . $about['image_path'])): ?>
            <div class="about-img-wrap">
                <img src="/<?php echo htmlspecialchars($about['image_path']); ?>" alt="من نحن">
                <div class="about-img-badge">
                    <strong><?php echo $about['years_exp'] ?? 15; ?>+</strong>
                    سنة خبرة
                </div>
            </div>
            <?php endif; ?>
            <div class="about-content-text">
                <div class="section-header" style="text-align:right;">
                    <span class="subtitle">تعرف علينا</span>
                    <h2><?php echo htmlspecialchars($about['title'] ?? 'من نحن'); ?></h2>
                    <div class="divider" style="justify-content:flex-start;margin:15px 0;"><i class="fas fa-gem"></i></div>
                </div>
                <p><?php echo nl2br(htmlspecialchars(mb_substr($about['content'] ?? 'معامل الشام للخبز العربي، شركة رائدة في مجال صناعة وتوزيع الخبز العربي الأصيل.', 0, 300))); ?>...</p>
                <?php if (!empty($about_features)): ?>
                <ul class="about-features">
                    <?php foreach ($about_features as $feat): ?>
                    <li><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($feat); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <a href="/about.php" class="btn-primary" style="display:inline-block;padding:14px 35px;font-weight:700;border-radius:4px;">اعرف المزيد عنا</a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($sec('whyus') && !empty($why_us_items)): ?>
<!-- ═══ WHY US ═══════════════════════════════════════════════ -->
<section class="whyus-section">
    <div class="container">
        <div class="section-header">
            <span class="subtitle">لماذا نحن</span>
            <h2>لماذا تختار <?php echo htmlspecialchars($settings['site_name'] ?? 'معامل الشام للخبز العربي'); ?>؟</h2>
            <div class="divider"><i class="fas fa-gem"></i></div>
        </div>
        <div class="whyus-grid">
            <?php foreach ($why_us_items as $item): ?>
            <div class="whyus-card">
                <div class="whyus-icon"><i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i></div>
                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                <p><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($sec('cta')): ?>
<!-- ═══ CTA ══════════════════════════════════════════════════ -->
<section class="cta-strip">
    <div class="container">
        <h2><?php echo htmlspecialchars($settings['cta_title'] ?? 'هل تريد خبزاً طازجاً يومياً لمطعمك أو محلك؟'); ?></h2>
        <p><?php echo htmlspecialchars($settings['cta_subtitle'] ?? 'تواصل معنا الآن واحصل على عرض التوزيع اليومي من معامل الشام'); ?></p>
        <div class="cta-btns">
            <?php if (!empty($settings['cta_btn1_text'])): ?>
            <a href="<?php echo htmlspecialchars($settings['cta_btn1_link'] ?? '/contact.php'); ?>" class="btn-primary" style="display:inline-block;padding:14px 40px;font-weight:700;border-radius:4px;"><?php echo htmlspecialchars($settings['cta_btn1_text']); ?></a>
            <?php endif; ?>
            <?php if (!empty($settings['cta_btn2_text'])): ?>
            <a href="<?php echo htmlspecialchars($settings['cta_btn2_link'] ?? '/projects.php'); ?>" class="btn-outline" style="display:inline-block;padding:12px 40px;font-weight:700;border-radius:4px;border:2px solid white;color:white;"><?php echo htmlspecialchars($settings['cta_btn2_text']); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
