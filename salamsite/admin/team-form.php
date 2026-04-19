<?php
require_once __DIR__ . '/../includes/admin-check.php';

$id = (int)($_GET['id'] ?? 0);
$member = ['name'=>'','position'=>'','email'=>'','phone'=>'','image_path'=>'','order_by'=>0];

if ($id) {
    $s = $pdo->prepare("SELECT * FROM team WHERE id=?");
    $s->execute([$id]);
    $found = $s->fetch();
    if ($found) $member = $found;
    $admin_title = 'تعديل عضو الفريق';
} else {
    $admin_title = 'إضافة عضو جديد';
}
$admin_icon = 'user-plus';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $pos   = trim($_POST['position'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $order = (int)($_POST['order_by'] ?? 0);

    if (empty($name)) { $error = 'اسم العضو مطلوب'; }
    else {
        $image_path = $member['image_path'];
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                $fname = 'team_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/team/' . $fname)) {
                    if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) unlink(__DIR__ . '/../' . $image_path);
                    $image_path = 'uploads/team/' . $fname;
                }
            }
        }

        if ($id) {
            $pdo->prepare("UPDATE team SET name=?,position=?,email=?,phone=?,image_path=?,order_by=? WHERE id=?")
                ->execute([$name,$pos,$email,$phone,$image_path,$order,$id]);
            $success = 'تم تحديث بيانات العضو بنجاح';
        } else {
            $pdo->prepare("INSERT INTO team (name,position,email,phone,image_path,order_by) VALUES (?,?,?,?,?,?)")
                ->execute([$name,$pos,$email,$phone,$image_path,$order]);
            $id = $pdo->lastInsertId();
            $success = 'تم إضافة العضو بنجاح';
        }
        $s = $pdo->prepare("SELECT * FROM team WHERE id=?"); $s->execute([$id]); $member = $s->fetch();
    }
}

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?> <a href="/admin/team.php" style="margin-right:10px;color:var(--gold);">العودة للقائمة</a></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-user-plus"></i> <?php echo $admin_title; ?></h3>
        <a href="/admin/team.php" class="btn btn-sm btn-dark"><i class="fas fa-arrow-right"></i> العودة</a>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <div class="form-mb">
                        <label class="form-label">الاسم الكامل *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($member['name']); ?>" required>
                    </div>
                    <div class="form-mb">
                        <label class="form-label">المنصب / الوظيفة</label>
                        <input type="text" name="position" class="form-control" value="<?php echo htmlspecialchars($member['position'] ?? ''); ?>" placeholder="مثال: مدير عام، مستشار عقاري...">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">الترتيب</label>
                        <input type="number" name="order_by" class="form-control" value="<?php echo $member['order_by']; ?>" min="0">
                    </div>
                </div>
                <div>
                    <label class="form-label">الصورة الشخصية</label>
                    <?php if (!empty($member['image_path']) && file_exists(__DIR__ . '/../' . $member['image_path'])): ?>
                    <div class="img-current">
                        <img src="/<?php echo htmlspecialchars($member['image_path']); ?>" alt="" style="border-radius:50%;width:150px;height:150px;object-fit:cover;margin-bottom:10px;">
                    </div>
                    <?php endif; ?>
                    <div class="upload-area">
                        <i class="fas fa-user-circle" style="font-size:40px;margin-bottom:8px;"></i>
                        <span><?php echo !empty($member['image_path']) ? 'رفع صورة جديدة' : 'اضغط لرفع الصورة الشخصية'; ?></span>
                        <input type="file" name="image" accept="image/*" class="img-upload-input" data-preview="teamPreview" style="display:none;">
                    </div>
                    <img id="teamPreview" class="img-preview" style="display:none;border-radius:50%;width:150px;height:150px;object-fit:cover;margin-top:10px;" alt="">
                </div>
            </div>
            <div style="display:flex;gap:10px;padding-top:15px;border-top:1px solid #eee;margin-top:5px;">
                <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?php echo $id ? 'تحديث البيانات' : 'إضافة العضو'; ?></button>
                <a href="/admin/team.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
