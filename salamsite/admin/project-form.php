<?php
require_once __DIR__ . '/../includes/admin-check.php';

$id = (int)($_GET['id'] ?? 0);
$project = ['name'=>'','location'=>'','status'=>'قيد التنفيذ','description'=>'','price'=>'','area'=>'','added_date'=>date('Y-m-d'),'featured'=>0,'image_path'=>'','gallery'=>'[]'];

if ($id) {
    $s = $pdo->prepare("SELECT * FROM projects WHERE id=?");
    $s->execute([$id]);
    $found = $s->fetch();
    if ($found) $project = $found;
    $admin_title = 'تعديل مشروع';
} else {
    $admin_title = 'إضافة مشروع جديد';
}
$admin_icon = 'building';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status   = trim($_POST['status'] ?? 'قيد التنفيذ');
    $desc     = trim($_POST['description'] ?? '');
    $price    = trim($_POST['price'] ?? '');
    $area     = trim($_POST['area'] ?? '');
    $date     = trim($_POST['added_date'] ?? date('Y-m-d'));
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (empty($name)) { $error = 'اسم المشروع مطلوب'; }
    else {
        $image_path = $project['image_path'];
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $fname = 'project_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/projects/' . $fname)) {
                    if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) unlink(__DIR__ . '/../' . $image_path);
                    $image_path = 'uploads/projects/' . $fname;
                }
            }
        }

        $gallery = json_decode($project['gallery'] ?: '[]', true);
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $k => $tmp) {
                if ($_FILES['gallery']['error'][$k] === 0) {
                    $ext2 = strtolower(pathinfo($_FILES['gallery']['name'][$k], PATHINFO_EXTENSION));
                    if (in_array($ext2, ['jpg','jpeg','png','webp','gif'])) {
                        $fn = 'proj_g_' . time() . '_' . $k . '.' . $ext2;
                        if (move_uploaded_file($tmp, __DIR__ . '/../uploads/projects/' . $fn)) {
                            $gallery[] = 'uploads/projects/' . $fn;
                        }
                    }
                }
            }
        }

        if ($id) {
            $pdo->prepare("UPDATE projects SET name=?,location=?,status=?,description=?,price=?,area=?,added_date=?,featured=?,image_path=?,gallery=? WHERE id=?")
                ->execute([$name,$location,$status,$desc,$price,$area,$date,$featured,$image_path,json_encode($gallery),$id]);
            $success = 'تم تحديث المشروع بنجاح';
        } else {
            $pdo->prepare("INSERT INTO projects (name,location,status,description,price,area,added_date,featured,image_path,gallery) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$name,$location,$status,$desc,$price,$area,$date,$featured,$image_path,json_encode($gallery)]);
            $id = $pdo->lastInsertId();
            $success = 'تم إضافة المشروع بنجاح';
        }
        $s = $pdo->prepare("SELECT * FROM projects WHERE id=?"); $s->execute([$id]); $project = $s->fetch();
    }
}

$gallery = json_decode($project['gallery'] ?: '[]', true);
include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?> <a href="/admin/projects.php" style="margin-right:10px;color:var(--gold);">العودة للقائمة</a></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-building"></i> <?php echo $admin_title; ?></h3>
        <a href="/admin/projects.php" class="btn btn-sm btn-dark"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <div class="form-mb">
                        <label class="form-label">اسم المشروع *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($project['name']); ?>" required>
                    </div>
                    <div class="form-mb">
                        <label class="form-label">الموقع</label>
                        <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($project['location'] ?? ''); ?>" placeholder="المدينة، الحي">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-mb">
                            <label class="form-label">حالة المشروع</label>
                            <select name="status" class="form-control">
                                <?php foreach(['قيد التنفيذ','مكتمل','متاح للبيع','تم البيع','قادم قريباً'] as $st): ?>
                                <option value="<?php echo $st; ?>" <?php echo $project['status']==$st?'selected':''; ?>><?php echo $st; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-mb">
                            <label class="form-label">السعر</label>
                            <input type="text" name="price" class="form-control" value="<?php echo htmlspecialchars($project['price'] ?? ''); ?>" placeholder="مثال: 500,000 ريال">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-mb">
                            <label class="form-label">المساحة</label>
                            <input type="text" name="area" class="form-control" value="<?php echo htmlspecialchars($project['area'] ?? ''); ?>" placeholder="مثال: 250 م²">
                        </div>
                        <div class="form-mb">
                            <label class="form-label">تاريخ الإضافة</label>
                            <input type="date" name="added_date" class="form-control" value="<?php echo htmlspecialchars($project['added_date'] ?? date('Y-m-d')); ?>">
                        </div>
                    </div>
                    <div class="form-mb">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:#555;">
                            <input type="checkbox" name="featured" <?php echo $project['featured']?'checked':''; ?> style="width:16px;height:16px;">
                            عرض في الصفحة الرئيسية (مميز)
                        </label>
                    </div>
                    <div class="form-mb">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div>
                    <div class="form-mb">
                        <label class="form-label">الصورة الرئيسية</label>
                        <?php if (!empty($project['image_path']) && file_exists(__DIR__ . '/../' . $project['image_path'])): ?>
                        <div class="img-current"><img src="/<?php echo htmlspecialchars($project['image_path']); ?>" alt=""></div>
                        <?php endif; ?>
                        <div class="upload-area" style="margin-top:10px;">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span><?php echo !empty($project['image_path']) ? 'رفع صورة جديدة للتغيير' : 'اضغط لرفع الصورة الرئيسية'; ?></span>
                            <input type="file" name="image" accept="image/*" class="img-upload-input" data-preview="mainPreview" style="display:none;">
                        </div>
                        <img id="mainPreview" class="img-preview" style="display:none;" alt="">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">معرض الصور (يمكن اختيار عدة صور)</label>
                        <?php if (!empty($gallery)): ?>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                            <?php foreach ($gallery as $gi): ?>
                            <?php if (file_exists(__DIR__.'/../'.$gi)): ?>
                            <img src="/<?php echo htmlspecialchars($gi); ?>" style="width:70px;height:55px;object-fit:cover;border-radius:4px;" alt="">
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <div class="upload-area">
                            <i class="fas fa-images"></i>
                            <span>اضغط لإضافة صور المعرض</span>
                            <input type="file" name="gallery[]" accept="image/*" multiple style="display:none;">
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:10px;padding-top:15px;border-top:1px solid #eee;margin-top:5px;">
                <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?php echo $id ? 'تحديث المشروع' : 'إضافة المشروع'; ?></button>
                <a href="/admin/projects.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
