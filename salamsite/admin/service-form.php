<?php
require_once __DIR__ . '/../includes/admin-check.php';

$id = (int)($_GET['id'] ?? 0);
$service = ['name'=>'','description'=>'','icon'=>'fa-building','image_path'=>'','order_by'=>0,'active'=>1];

if ($id) {
    $s = $pdo->prepare("SELECT * FROM services WHERE id=?");
    $s->execute([$id]);
    $found = $s->fetch();
    if ($found) $service = $found;
    $admin_title = 'تعديل خدمة';
} else {
    $admin_title = 'إضافة خدمة جديدة';
}
$admin_icon = 'concierge-bell';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $icon  = trim($_POST['icon'] ?? 'fa-building');
    $order = (int)($_POST['order_by'] ?? 0);
    $active= isset($_POST['active']) ? 1 : 0;

    if (empty($name)) { $error = 'اسم الخدمة مطلوب'; }
    else {
        $image_path = $service['image_path'];
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $fname = 'service_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/services/' . $fname)) {
                    if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) unlink(__DIR__ . '/../' . $image_path);
                    $image_path = 'uploads/services/' . $fname;
                }
            }
        }

        if ($id) {
            $pdo->prepare("UPDATE services SET name=?,description=?,icon=?,image_path=?,order_by=?,active=? WHERE id=?")
                ->execute([$name,$desc,$icon,$image_path,$order,$active,$id]);
            $success = 'تم تحديث الخدمة بنجاح';
        } else {
            $pdo->prepare("INSERT INTO services (name,description,icon,image_path,order_by,active) VALUES (?,?,?,?,?,?)")
                ->execute([$name,$desc,$icon,$image_path,$order,$active]);
            $id = $pdo->lastInsertId();
            $success = 'تم إضافة الخدمة بنجاح';
        }
        $s = $pdo->prepare("SELECT * FROM services WHERE id=?"); $s->execute([$id]); $service = $s->fetch();
    }
}

$icons = ['fa-home','fa-key','fa-building','fa-city','fa-chart-line','fa-handshake','fa-search-dollar','fa-hard-hat','fa-map-marker-alt','fa-users','fa-star','fa-award','fa-shield-alt','fa-balance-scale','fa-calculator'];
include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?> <a href="/admin/services.php" style="margin-right:10px;color:var(--gold);">العودة للقائمة</a></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-concierge-bell"></i> <?php echo $admin_title; ?></h3>
        <a href="/admin/services.php" class="btn btn-sm btn-dark"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <div class="form-mb">
                        <label class="form-label">اسم الخدمة *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($service['name']); ?>" required>
                    </div>
                    <div class="form-mb">
                        <label class="form-label">أيقونة الخدمة</label>
                        <select name="icon" class="form-control" id="iconSelect">
                            <?php foreach ($icons as $icon): ?>
                            <option value="<?php echo $icon; ?>" <?php echo $service['icon']==$icon?'selected':''; ?>><?php echo $icon; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div style="margin-top:10px;display:flex;align-items:center;gap:10px;">
                            <div id="iconPreview" style="width:50px;height:50px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fas <?php echo htmlspecialchars($service['icon']); ?>" style="color:white;font-size:20px;"></i>
                            </div>
                            <span style="font-size:13px;color:#888;">معاينة الأيقونة</span>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-mb">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="order_by" class="form-control" value="<?php echo $service['order_by']; ?>" min="0">
                        </div>
                        <div class="form-mb">
                            <label class="form-label">الحالة</label>
                            <select name="active" class="form-control">
                                <option value="1" <?php echo $service['active']?'selected':''; ?>>مفعّل</option>
                                <option value="0" <?php echo !$service['active']?'selected':''; ?>>معطّل</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-mb">
                        <label class="form-label">وصف الخدمة</label>
                        <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div>
                    <label class="form-label">صورة الخدمة (اختياري)</label>
                    <?php if (!empty($service['image_path']) && file_exists(__DIR__ . '/../' . $service['image_path'])): ?>
                    <div class="img-current"><img src="/<?php echo htmlspecialchars($service['image_path']); ?>" alt=""></div>
                    <?php endif; ?>
                    <div class="upload-area" style="margin-top:10px;">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>اضغط لرفع صورة الخدمة</span>
                        <input type="file" name="image" accept="image/*" class="img-upload-input" data-preview="svcPreview" style="display:none;">
                    </div>
                    <img id="svcPreview" class="img-preview" style="display:none;" alt="">
                </div>
            </div>
            <div style="display:flex;gap:10px;padding-top:15px;border-top:1px solid #eee;margin-top:5px;">
                <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?php echo $id ? 'تحديث الخدمة' : 'إضافة الخدمة'; ?></button>
                <a href="/admin/services.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('iconSelect').addEventListener('change', function() {
    const prev = document.querySelector('#iconPreview i');
    prev.className = 'fas ' + this.value;
    prev.style.fontSize = '20px';
    prev.style.color = 'white';
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
