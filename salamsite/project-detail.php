<?php
require_once __DIR__ . '/includes/db.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /projects.php'); exit; }
$project = $pdo->prepare("SELECT * FROM projects WHERE id=?");
$project->execute([$id]);
$project = $project->fetch();
if (!$project) { header('Location: /projects.php'); exit; }
$page_title = $project['name'];
$gallery = !empty($project['gallery']) ? json_decode($project['gallery'], true) : [];
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1><?php echo htmlspecialchars($project['name']); ?></h1>
        <div class="breadcrumb">
            <a href="/index.php">الرئيسية</a>
            <span>›</span>
            <a href="/projects.php">الأعمال</a>
            <span>›</span>
            <span><?php echo htmlspecialchars($project['name']); ?></span>
        </div>
    </div>
</div>

<section class="project-detail">
    <div class="container">
        <div class="project-detail-grid">
            <div>
                <div class="project-main-img">
                    <?php if (!empty($project['image_path']) && file_exists(__DIR__ . '/' . $project['image_path'])): ?>
                        <img src="/<?php echo htmlspecialchars($project['image_path']); ?>" alt="<?php echo htmlspecialchars($project['name']); ?>">
                    <?php else: ?>
                        <div style="height:450px;background:var(--dark);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-building" style="font-size:100px;color:rgba(184,150,62,0.3);"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($gallery)): ?>
                <div class="gallery-grid" style="margin-top:15px;">
                    <?php foreach ($gallery as $img): ?>
                        <?php if (file_exists(__DIR__ . '/' . $img)): ?>
                        <img src="/<?php echo htmlspecialchars($img); ?>" alt="صورة" onclick="this.closest('.gallery-grid').previousElementSibling.querySelector('img').src=this.src" style="cursor:pointer;">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($project['description'])): ?>
                <div style="margin-top:30px;background:var(--gray-light);border-radius:8px;padding:30px;">
                    <h3 style="font-size:20px;font-weight:700;color:var(--dark);margin-bottom:15px;">وصف المشروع</h3>
                    <p style="color:#555;font-size:15px;line-height:2;"><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div>
                <div class="project-detail-info">
                    <h1><?php echo htmlspecialchars($project['name']); ?></h1>
                    <div style="width:60px;height:3px;background:var(--gold);margin:15px 0;"></div>
                    <?php if (!empty($project['price'])): ?>
                    <div style="font-size:28px;font-weight:900;color:var(--gold);margin-bottom:20px;"><?php echo htmlspecialchars($project['price']); ?></div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="label"><i class="fas fa-tag" style="color:var(--gold);margin-left:5px;"></i>الحالة</span>
                        <span class="value"><?php echo htmlspecialchars($project['status']); ?></span>
                    </div>
                    <?php if (!empty($project['location'])): ?>
                    <div class="info-row">
                        <span class="label"><i class="fas fa-map-marker-alt" style="color:var(--gold);margin-left:5px;"></i>الموقع</span>
                        <span class="value"><?php echo htmlspecialchars($project['location']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['area'])): ?>
                    <div class="info-row">
                        <span class="label"><i class="fas fa-ruler-combined" style="color:var(--gold);margin-left:5px;"></i>المساحة</span>
                        <span class="value"><?php echo htmlspecialchars($project['area']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($project['added_date'])): ?>
                    <div class="info-row">
                        <span class="label"><i class="fas fa-calendar" style="color:var(--gold);margin-left:5px;"></i>تاريخ الإضافة</span>
                        <span class="value"><?php echo htmlspecialchars($project['added_date']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="margin-top:30px;">
                        <a href="/contact.php?project=<?php echo urlencode($project['name']); ?>" class="btn-primary" style="display:block;text-align:center;padding:15px;font-weight:700;font-size:16px;border-radius:4px;background:var(--gold);color:var(--dark);">
                            <i class="fas fa-phone" style="margin-left:8px;"></i>استفسر عن هذا المشروع
                        </a>
                    </div>
                    <div style="margin-top:15px;">
                        <a href="/projects.php" style="display:block;text-align:center;padding:12px;font-weight:700;font-size:14px;border-radius:4px;background:var(--dark);color:var(--white);">
                            <i class="fas fa-arrow-right" style="margin-left:8px;"></i>العودة للأعمال
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
