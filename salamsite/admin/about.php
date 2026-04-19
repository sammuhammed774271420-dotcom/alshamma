<?php
$admin_title = 'صفحة من نحن';
$admin_icon = 'info-circle';
require_once __DIR__ . '/../includes/admin-check.php';

$about = $pdo->query("SELECT * FROM about_content LIMIT 1")->fetch();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $vision   = trim($_POST['vision'] ?? '');
    $mission  = trim($_POST['mission'] ?? '');
    $years    = (int)($_POST['years_exp'] ?? 15);
    $projects = (int)($_POST['projects_count'] ?? 200);
    $clients  = (int)($_POST['clients_count'] ?? 500);
    $awards   = (int)($_POST['awards_count'] ?? 30);

    // Features
    $features_raw = array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')));
    $features = json_encode(array_values($features_raw));

    $image_path = $about['image_path'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $fname = 'about_' . time() . '.' . $ext;
            $dest = __DIR__ . '/../uploads/about/' . $fname;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) unlink(__DIR__ . '/../' . $image_path);
                $image_path = 'uploads/about/' . $fname;
            }
        }
    }

    if ($about) {
        $pdo->prepare("UPDATE about_content SET title=?,content=?,vision=?,mission=?,image_path=?,years_exp=?,projects_count=?,clients_count=?,awards_count=?,features=? WHERE id=?")
            ->execute([$title,$content,$vision,$mission,$image_path,$years,$projects,$clients,$awards,$features,$about['id']]);
    } else {
        $pdo->prepare("INSERT INTO about_content (title,content,vision,mission,image_path,years_exp,projects_count,clients_count,awards_count,features) VALUES (?,?,?,?,?,?,?,?,?,?)")
            ->execute([$title,$content,$vision,$mission,$image_path,$years,$projects,$clients,$awards,$features]);
    }
    $success = 'تم حفظ محتوى صفحة "من نحن" بنجاح';
    $about = $pdo->query("SELECT * FROM about_content LIMIT 1")->fetch();
}

$current_features = [];
if (!empty($about['features'])) {
    $current_features = json_decode($about['features'], true) ?: [];
}
if (empty($current_features)) {
    $current_features = ['فريق متخصص ذو خبرة واسعة','شفافية كاملة في التعاملات','ضمان أعلى معايير الجودة','دعم ما بعد البيع'];
}

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-info-circle"></i> تعديل محتوى صفحة "من نحن"</h3>
        <a href="/about.php" class="btn btn-sm btn-gold" target="_blank"><i class="fas fa-external-link-alt"></i> معاينة الصفحة</a>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">
                <div>
                    <div class="form-mb">
                        <label class="form-label">عنوان الصفحة</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($about['title'] ?? 'من نحن - شركة السلام للعقارات'); ?>">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">المحتوى الرئيسي (يظهر في الصفحة الرئيسية أيضاً)</label>
                        <textarea name="content" class="form-control" rows="6"><?php echo htmlspecialchars($about['content'] ?? ''); ?></textarea>
                    </div>

                    <div style="background:#fafaf8;border-radius:8px;padding:18px;margin-bottom:16px;">
                        <h4 style="font-size:13px;font-weight:700;color:var(--gold);margin-bottom:12px;"><i class="fas fa-list-check" style="margin-left:6px;"></i> نقاط الميزات (تظهر في قسم من نحن بالرئيسية)</h4>
                        <p style="font-size:12px;color:#888;margin-bottom:8px;">اكتب كل ميزة في سطر منفصل</p>
                        <textarea name="features" class="form-control" rows="5" placeholder="فريق متخصص ذو خبرة واسعة
شفافية كاملة في التعاملات
ضمان أعلى معايير الجودة
دعم ما بعد البيع"><?php echo htmlspecialchars(implode("\n", $current_features)); ?></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-mb">
                            <label class="form-label">الرؤية</label>
                            <textarea name="vision" class="form-control" rows="4"><?php echo htmlspecialchars($about['vision'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-mb">
                            <label class="form-label">الرسالة</label>
                            <textarea name="mission" class="form-control" rows="4"><?php echo htmlspecialchars($about['mission'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div style="background:#fafaf8;border-radius:8px;padding:18px;">
                        <h4 style="font-size:13px;font-weight:700;color:var(--gold);margin-bottom:15px;"><i class="fas fa-chart-bar" style="margin-left:6px;"></i> الأرقام والإحصائيات (تظهر في الشريط الذهبي)</h4>
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;">
                            <div>
                                <label class="form-label">سنوات الخبرة</label>
                                <input type="number" name="years_exp" class="form-control" value="<?php echo $about['years_exp'] ?? 15; ?>" min="1">
                            </div>
                            <div>
                                <label class="form-label">المشاريع المنجزة</label>
                                <input type="number" name="projects_count" class="form-control" value="<?php echo $about['projects_count'] ?? 200; ?>" min="0">
                            </div>
                            <div>
                                <label class="form-label">العملاء الراضون</label>
                                <input type="number" name="clients_count" class="form-control" value="<?php echo $about['clients_count'] ?? 500; ?>" min="0">
                            </div>
                            <div>
                                <label class="form-label">جوائز التميز</label>
                                <input type="number" name="awards_count" class="form-control" value="<?php echo $about['awards_count'] ?? 30; ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="form-label">الصورة الرئيسية</label>
                    <?php if (!empty($about['image_path']) && file_exists(__DIR__ . '/../' . $about['image_path'])): ?>
                    <div class="img-current"><img src="/<?php echo htmlspecialchars($about['image_path']); ?>" alt="" style="border-radius:8px;"></div>
                    <?php endif; ?>
                    <div class="upload-area" style="margin-top:10px;">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>اضغط لرفع الصورة</span>
                        <input type="file" name="image" accept="image/*" class="img-upload-input" data-preview="aboutPreview" style="display:none;">
                    </div>
                    <img id="aboutPreview" class="img-preview" style="display:none;" alt="">

                    <div style="margin-top:20px;padding:15px;background:#f8f9fa;border-radius:8px;">
                        <h5 style="font-size:13px;color:#555;margin-bottom:10px;"><i class="fas fa-info-circle" style="color:var(--gold);margin-left:5px;"></i> هذه البيانات تتحكم في:</h5>
                        <ul style="font-size:12px;color:#888;padding-right:20px;line-height:1.8;">
                            <li>قسم "من نحن" في الصفحة الرئيسية</li>
                            <li>شريط الإحصائيات والأرقام</li>
                            <li>صفحة "من نحن" الكاملة</li>
                            <li>الشارة الذهبية على الصورة</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;padding-top:15px;border-top:1px solid #eee;margin-top:15px;">
                <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> حفظ جميع التغييرات</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
