<?php
require_once __DIR__ . '/includes/db.php';
$page_title = 'منتجاتنا';
$projects = $pdo->query("SELECT * FROM projects ORDER BY featured DESC, id DESC")->fetchAll();
$statuses = $pdo->query("SELECT DISTINCT status FROM projects")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>منتجاتنا <span>من الخبز العربي</span></h1>
        <p>استعرض مجموعتنا المتميزة من منتجات الخبز العربي الأصيل بأعلى معايير الجودة والنظافة</p>
        <div class="breadcrumb">
            <a href="/index.php">الرئيسية</a>
            <span>›</span>
            <span>منتجاتنا</span>
        </div>
    </div>
</div>

<section class="projects-page">
    <div class="container">
        <?php if (!empty($statuses)): ?>
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">جميع المنتجات</button>
            <?php foreach ($statuses as $status): ?>
            <button class="filter-tab" data-filter="<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($status); ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($projects)): ?>
        <div class="empty-state">
            <i class="fas fa-bread-slice"></i>
            <p>لا توجد منتجات حالياً. سيتم إضافة منتجاتنا قريباً.</p>
        </div>
        <?php else: ?>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
            <div class="project-card" data-status="<?php echo htmlspecialchars($project['status']); ?>">
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
                        <span><i class="fas fa-tag"></i><?php echo htmlspecialchars($project['location']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($project['area'])): ?>
                        <span><i class="fas fa-weight"></i><?php echo htmlspecialchars($project['area']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($project['added_date'])): ?>
                        <span><i class="fas fa-calendar"></i><?php echo htmlspecialchars($project['added_date']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($project['description'])): ?>
                    <p style="color:#666;font-size:13px;line-height:1.7;margin-bottom:10px;"><?php echo htmlspecialchars(mb_substr($project['description'], 0, 100)); ?>...</p>
                    <?php endif; ?>
                    <?php if (!empty($project['price'])): ?>
                    <div class="project-price"><?php echo htmlspecialchars($project['price']); ?></div>
                    <?php endif; ?>
                    <a href="/project-detail.php?id=<?php echo $project['id']; ?>" class="project-btn">عرض التفاصيل <i class="fas fa-arrow-left"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="cta-strip">
    <div class="container">
        <h2>هل تريد الاستفسار عن <span>منتج معين؟</span></h2>
        <p>تواصل معنا الآن للحصول على معلومات مفصلة حول أي منتج أو طلب مخصص</p>
        <div class="cta-btns">
            <a href="/contact.php" class="btn-primary" style="display:inline-block;padding:14px 40px;font-weight:700;border-radius:4px;">تواصل معنا</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
