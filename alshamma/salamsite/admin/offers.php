<?php
$admin_title = 'إدارة العروض';
$admin_icon  = 'tag';
require_once __DIR__ . '/../includes/admin-check.php';

$success = $error = '';

// ── حذف ──────────────────────────────────────────────────────
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $st = $pdo->prepare("SELECT image_path FROM offers WHERE id=?");
    $st->execute([(int)$_GET['delete']]);
    $row = $st->fetch();
    if ($row && !empty($row['image_path']) && file_exists(__DIR__ . '/../' . $row['image_path'])) {
        @unlink(__DIR__ . '/../' . $row['image_path']);
    }
    $pdo->prepare("DELETE FROM offers WHERE id=?")->execute([(int)$_GET['delete']]);
    $success = 'تم حذف العرض بنجاح';
}

// ── تفعيل/تعطيل ──────────────────────────────────────────────
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $st = $pdo->prepare("SELECT active FROM offers WHERE id=?");
    $st->execute([(int)$_GET['toggle']]);
    $cur = (int)$st->fetchColumn();
    $pdo->prepare("UPDATE offers SET active=? WHERE id=?")->execute([$cur ? 0 : 1, (int)$_GET['toggle']]);
    $success = 'تم تحديث حالة العرض';
}

// ── إضافة / تعديل ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $title       = trim($_POST['title']       ?? '');
    $subtitle    = trim($_POST['subtitle']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $badge_text  = trim($_POST['badge_text']  ?? '');
    $price       = trim($_POST['price']       ?? '');
    $link        = trim($_POST['link']        ?? '');
    $order_by    = (int)($_POST['order_by']   ?? 0);
    $active      = isset($_POST['active']) ? 1 : 0;

    if (empty($title)) {
        $error = 'يرجى إدخال عنوان العرض';
    } else {
        // رفع الصورة
        $image_path = '';
        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                if (!is_dir(__DIR__ . '/../uploads/offers')) {
                    @mkdir(__DIR__ . '/../uploads/offers', 0755, true);
                }
                $fname = 'offer_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $dest  = __DIR__ . '/../uploads/offers/' . $fname;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image_path = 'uploads/offers/' . $fname;
                }
            } else {
                $error = 'صيغة الصورة غير مدعومة. يُرجى رفع JPG أو PNG أو WebP.';
            }
        }

        if (!$error) {
            if ($id) {
                if ($image_path) {
                    // حذف الصورة القديمة
                    $old = $pdo->prepare("SELECT image_path FROM offers WHERE id=?");
                    $old->execute([$id]);
                    $oldRow = $old->fetch();
                    if ($oldRow && !empty($oldRow['image_path']) && file_exists(__DIR__ . '/../' . $oldRow['image_path'])) {
                        @unlink(__DIR__ . '/../' . $oldRow['image_path']);
                    }
                    $pdo->prepare("UPDATE offers SET title=?,subtitle=?,description=?,badge_text=?,price=?,link=?,order_by=?,active=?,image_path=? WHERE id=?")
                        ->execute([$title,$subtitle,$description,$badge_text,$price,$link,$order_by,$active,$image_path,$id]);
                } else {
                    $pdo->prepare("UPDATE offers SET title=?,subtitle=?,description=?,badge_text=?,price=?,link=?,order_by=?,active=? WHERE id=?")
                        ->execute([$title,$subtitle,$description,$badge_text,$price,$link,$order_by,$active,$id]);
                }
                $success = 'تم تحديث العرض بنجاح';
            } else {
                $pdo->prepare("INSERT INTO offers (title,subtitle,description,badge_text,price,link,order_by,active,image_path) VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$title,$subtitle,$description,$badge_text,$price,$link,$order_by,$active,$image_path]);
                $success = 'تم إضافة العرض بنجاح';
            }
        }
    }
}

// ── تحميل للتعديل ────────────────────────────────────────────
$edit_item = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM offers WHERE id=?");
    $st->execute([(int)$_GET['edit']]);
    $edit_item = $st->fetch();
}

$offers    = $pdo->query("SELECT * FROM offers ORDER BY order_by ASC, id DESC")->fetchAll();
$show_form = (isset($_GET['action']) && $_GET['action'] === 'add') || $edit_item;

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

<div class="page-actions">
    <h3 style="font-size:16px;font-weight:700;color:var(--dark);"><?php echo count($offers); ?> عرض مضاف</h3>
    <?php if (!$show_form): ?>
    <a href="?action=add" class="btn btn-gold"><i class="fas fa-plus"></i> إضافة عرض جديد</a>
    <?php else: ?>
    <a href="/admin/offers.php" class="btn btn-dark"><i class="fas fa-times"></i> إلغاء</a>
    <?php endif; ?>
</div>

<?php if ($show_form): ?>
<!-- ── نموذج الإضافة/التعديل ── -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-<?php echo $edit_item ? 'edit' : 'plus'; ?>"></i> <?php echo $edit_item ? 'تعديل العرض' : 'إضافة عرض جديد'; ?></h3>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit_item): ?><input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>"><?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <!-- العمود الأيسر: البيانات -->
                <div>
                    <div class="form-mb">
                        <label class="form-label">عنوان العرض *</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_item['title'] ?? ''); ?>" placeholder="مثال: عرض خاص على الفلل الفاخرة" required>
                    </div>
                    <div class="form-mb">
                        <label class="form-label">العنوان الفرعي</label>
                        <input type="text" name="subtitle" class="form-control" value="<?php echo htmlspecialchars($edit_item['subtitle'] ?? ''); ?>" placeholder="مثال: تملّك الآن بأقل الأسعار">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">الوصف التفصيلي</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="تفاصيل العرض، المميزات، الشروط..."><?php echo htmlspecialchars($edit_item['description'] ?? ''); ?></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-mb">
                            <label class="form-label">الشارة (Badge)</label>
                            <input type="text" name="badge_text" class="form-control" value="<?php echo htmlspecialchars($edit_item['badge_text'] ?? ''); ?>" placeholder="مثال: عرض محدود">
                        </div>
                        <div class="form-mb">
                            <label class="form-label">السعر</label>
                            <input type="text" name="price" class="form-control" value="<?php echo htmlspecialchars($edit_item['price'] ?? ''); ?>" placeholder="مثال: يبدأ من 500,000 ر.س">
                        </div>
                    </div>
                    <div class="form-mb">
                        <label class="form-label">رابط زر العرض</label>
                        <input type="text" name="link" class="form-control" value="<?php echo htmlspecialchars($edit_item['link'] ?? ''); ?>" placeholder="/contact.php أو /projects.php">
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

                <!-- العمود الأيمن: الصورة -->
                <div>
                    <label class="form-label">صورة العرض</label>
                    <p style="font-size:12px;color:#888;margin-bottom:10px;">الأبعاد الموصى بها: 800×500 بكسل (أو أي نسبة 16:9)</p>
                    <?php if ($edit_item && !empty($edit_item['image_path']) && file_exists(__DIR__ . '/../' . $edit_item['image_path'])): ?>
                    <div class="img-current" style="margin-bottom:10px;">
                        <img src="/<?php echo htmlspecialchars($edit_item['image_path']); ?>" alt="" style="width:100%;max-height:200px;object-fit:cover;border-radius:6px;">
                        <small style="color:#888;display:block;margin-top:5px;">الصورة الحالية — ارفع صورة جديدة للتغيير</small>
                    </div>
                    <?php endif; ?>
                    <div class="upload-area">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>اضغط لرفع صورة العرض</span>
                        <input type="file" name="image" accept="image/*" class="img-upload-input" data-preview="offerPreview" style="display:none;">
                    </div>
                    <img id="offerPreview" class="img-preview" style="display:none;width:100%;max-height:200px;object-fit:cover;border-radius:6px;margin-top:10px;" alt="">
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:15px;">
                <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?php echo $edit_item ? 'تحديث العرض' : 'إضافة العرض'; ?></button>
                <a href="/admin/offers.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── قائمة العروض ── -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-list"></i> العروض المضافة</h3>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($offers)): ?>
        <div class="empty-state">
            <i class="fas fa-tag"></i>
            <p>لا توجد عروض. أضف عرضاً جديداً ليظهر على الموقع.</p>
        </div>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>العرض</th>
                    <th>السعر</th>
                    <th>الشارة</th>
                    <th>الترتيب</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($offers as $off): ?>
                <tr>
                    <td>
                        <?php if (!empty($off['image_path']) && file_exists(__DIR__ . '/../' . $off['image_path'])): ?>
                        <img src="/<?php echo htmlspecialchars($off['image_path']); ?>" alt="" style="width:90px;height:55px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                        <div style="width:90px;height:55px;background:#f4f4f4;border-radius:4px;display:flex;align-items:center;justify-content:center;border:1px dashed #ddd;">
                            <i class="fas fa-image" style="color:#ccc;font-size:20px;"></i>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="display:block;"><?php echo htmlspecialchars($off['title']); ?></strong>
                        <?php if (!empty($off['subtitle'])): ?>
                        <small style="color:#888;"><?php echo htmlspecialchars($off['subtitle']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--gold);font-weight:700;"><?php echo htmlspecialchars($off['price'] ?? '-'); ?></td>
                    <td>
                        <?php if (!empty($off['badge_text'])): ?>
                        <span style="background:var(--gold);color:var(--dark);padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;"><?php echo htmlspecialchars($off['badge_text']); ?></span>
                        <?php else: ?>
                        <span style="color:#ccc;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $off['order_by']; ?></td>
                    <td>
                        <a href="?toggle=<?php echo $off['id']; ?>" class="badge <?php echo $off['active'] ? 'badge-green' : 'badge-red'; ?>" style="text-decoration:none;cursor:pointer;">
                            <?php echo $off['active'] ? 'مفعّل' : 'معطّل'; ?>
                        </a>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="?edit=<?php echo $off['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i></a>
                            <a href="?delete=<?php echo $off['id']; ?>" class="btn btn-sm btn-red delete-btn"><i class="fas fa-trash"></i></a>
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
