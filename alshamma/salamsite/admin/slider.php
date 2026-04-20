<?php
$admin_title = 'عروض الصفحة الرئيسية';
$admin_icon = 'images';
require_once __DIR__ . '/../includes/admin-check.php';

$success = $error = '';

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $slider = $pdo->prepare("SELECT image_path FROM slider_images WHERE id=?")->execute([(int)$_GET['delete']]);
    $slider = $pdo->prepare("SELECT image_path FROM slider_images WHERE id=?");
    $slider->execute([(int)$_GET['delete']]);
    $sl = $slider->fetch();
    if ($sl && !empty($sl['image_path']) && file_exists(__DIR__ . '/../' . $sl['image_path'])) {
        unlink(__DIR__ . '/../' . $sl['image_path']);
    }
    $pdo->prepare("DELETE FROM slider_images WHERE id=?")->execute([(int)$_GET['delete']]);
    $success = 'تم حذف العرض بنجاح';
}

// Toggle active
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $cur = $pdo->prepare("SELECT active FROM slider_images WHERE id=?");
    $cur->execute([(int)$_GET['toggle']]);
    $st = $cur->fetchColumn();
    $pdo->prepare("UPDATE slider_images SET active=? WHERE id=?")->execute([$st ? 0 : 1, (int)$_GET['toggle']]);
    $success = 'تم تحديث حالة العرض';
}

// Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $title    = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $link     = trim($_POST['link'] ?? '');
    $order    = (int)($_POST['order_by'] ?? 0);
    $active   = isset($_POST['active']) ? 1 : 0;

    $image_path = '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
            $fname = 'slider_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $dest = __DIR__ . '/../uploads/slider/' . $fname;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_path = 'uploads/slider/' . $fname;
            }
        }
    }

    if ($id) {
        if ($image_path) {
            $pdo->prepare("UPDATE slider_images SET title=?,subtitle=?,link=?,order_by=?,active=?,image_path=? WHERE id=?")
                ->execute([$title,$subtitle,$link,$order,$active,$image_path,$id]);
        } else {
            $pdo->prepare("UPDATE slider_images SET title=?,subtitle=?,link=?,order_by=?,active=? WHERE id=?")
                ->execute([$title,$subtitle,$link,$order,$active,$id]);
        }
        $success = 'تم تحديث العرض بنجاح';
    } else {
        $pdo->prepare("INSERT INTO slider_images (title,subtitle,image_path,link,order_by,active) VALUES (?,?,?,?,?,?)")
            ->execute([$title,$subtitle,$image_path,$link,$order,$active]);
        $success = 'تم إضافة العرض بنجاح';
    }
}

$edit_item = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM slider_images WHERE id=?");
    $s->execute([(int)$_GET['edit']]);
    $edit_item = $s->fetch();
}

$sliders = $pdo->query("SELECT * FROM slider_images ORDER BY order_by ASC, id ASC")->fetchAll();
$show_form = isset($_GET['action']) && $_GET['action'] === 'add' || $edit_item;

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

<div class="page-actions">
    <h3 style="font-size:16px;font-weight:700;color:var(--dark);">
        <?php echo count($sliders); ?> عرض مضاف
    </h3>
    <?php if (!$show_form): ?>
    <a href="?action=add" class="btn btn-gold"><i class="fas fa-plus"></i> إضافة عرض جديد</a>
    <?php else: ?>
    <a href="/admin/slider.php" class="btn btn-dark"><i class="fas fa-times"></i> إلغاء</a>
    <?php endif; ?>
</div>

<?php if ($show_form): ?>
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-<?php echo $edit_item ? 'edit' : 'plus'; ?>"></i> <?php echo $edit_item ? 'تعديل العرض' : 'إضافة عرض جديد'; ?></h3>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <div class="form-mb">
                        <label class="form-label">العنوان الرئيسي</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>" placeholder="عنوان العرض">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">العنوان الفرعي</label>
                        <input type="text" name="subtitle" class="form-control" value="<?php echo htmlspecialchars($edit_item['subtitle'] ?? ''); ?>" placeholder="وصف قصير">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">رابط الزر (اختياري)</label>
                        <input type="text" name="link" class="form-control" value="<?php echo htmlspecialchars($edit_item['link'] ?? ''); ?>" placeholder="/projects.php">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-mb">
                            <label class="form-label">الترتيب</label>
                            <input type="number" name="order_by" class="form-control" value="<?php echo $edit_item['order_by'] ?? 0; ?>" min="0">
                        </div>
                        <div class="form-mb">
                            <label class="form-label">الحالة</label>
                            <select name="active" class="form-control">
                                <option value="1" <?php echo (!$edit_item || $edit_item['active']) ? 'selected' : ''; ?>>مفعّل</option>
                                <option value="0" <?php echo ($edit_item && !$edit_item['active']) ? 'selected' : ''; ?>>معطّل</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="form-label">صورة العرض (توصية: 1920×900 بكسل)</label>
                    <?php if ($edit_item && !empty($edit_item['image_path']) && file_exists(__DIR__ . '/../' . $edit_item['image_path'])): ?>
                    <div class="img-current">
                        <img src="/<?php echo htmlspecialchars($edit_item['image_path']); ?>" alt="">
                        <small style="color:#888;display:block;margin-top:5px;">الصورة الحالية - ارفع صورة جديدة للتغيير</small>
                    </div>
                    <?php endif; ?>
                    <div class="upload-area" style="margin-top:10px;">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>اضغط لرفع صورة العرض</span>
                        <input type="file" name="image" accept="image/*" class="img-upload-input" data-preview="sliderPreview" style="display:none;">
                    </div>
                    <img id="sliderPreview" class="img-preview" style="display:none;" alt="">
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?php echo $edit_item ? 'تحديث' : 'إضافة'; ?></button>
                <a href="/admin/slider.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-list"></i> العروض المضافة</h3>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($sliders)): ?>
        <div class="empty-state">
            <i class="fas fa-images"></i>
            <p>لا توجد عروض. أضف عرضاً جديداً ليظهر في الصفحة الرئيسية.</p>
        </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>العنوان</th>
                    <th>الوصف</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sliders as $sl): ?>
                <tr>
                    <td>
                        <?php if (!empty($sl['image_path']) && file_exists(__DIR__ . '/../' . $sl['image_path'])): ?>
                        <img src="/<?php echo htmlspecialchars($sl['image_path']); ?>" alt="" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                        <div style="width:80px;height:50px;background:#1a1a1a;border-radius:4px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:rgba(184,150,62,.4);"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($sl['title'] ?? '-'); ?></strong></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#888;font-size:13px;"><?php echo htmlspecialchars($sl['subtitle'] ?? '-'); ?></td>
                    <td><?php echo $sl['order_by']; ?></td>
                    <td>
                        <a href="?toggle=<?php echo $sl['id']; ?>" class="badge <?php echo $sl['active'] ? 'badge-green' : 'badge-red'; ?>" style="text-decoration:none;">
                            <?php echo $sl['active'] ? 'مفعّل' : 'معطّل'; ?>
                        </a>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="?edit=<?php echo $sl['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $sl['id']; ?>" class="btn btn-sm btn-red delete-btn"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
