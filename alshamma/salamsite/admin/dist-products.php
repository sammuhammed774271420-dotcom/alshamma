<?php
$admin_title = 'المنتجات والأسعار';
$admin_icon  = 'bread-slice';
require_once __DIR__ . '/../includes/admin-check.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM dist_products WHERE id=?")->execute([$_GET['delete']]);
    header('Location: dist-products.php?deleted=1'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $pid  = (int)($_POST['pid'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $unit = trim($_POST['unit'] ?? 'ربطة');
        $price = (float)($_POST['price'] ?? 0);
        $desc = trim($_POST['description'] ?? '');
        $active = isset($_POST['active']) ? 1 : 0;
        if ($name) {
            if ($pid) {
                $pdo->prepare("UPDATE dist_products SET name=?,unit=?,price=?,description=?,active=? WHERE id=?")
                    ->execute([$name,$unit,$price,$desc,$active,$pid]);
            } else {
                $pdo->prepare("INSERT INTO dist_products (name,unit,price,description,active) VALUES (?,?,?,?,?)")
                    ->execute([$name,$unit,$price,$desc,$active]);
            }
            header('Location: dist-products.php?saved=1'); exit;
        }
    }
}

$products = $pdo->query("SELECT * FROM dist_products ORDER BY name")->fetchAll();

$edit_prod = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM dist_products WHERE id=?");
    $s->execute([$_GET['edit']]);
    $edit_prod = $s->fetch();
}

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> تم حذف المنتج.</div><?php endif; ?>
<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> تم حفظ المنتج.</div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;">

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-plus-circle"></i> <?php echo $edit_prod ? 'تعديل منتج' : 'إضافة منتج'; ?></h3>
    </div>
    <div class="admin-card-body">
        <form method="post">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="pid" value="<?php echo $edit_prod['id'] ?? 0; ?>">
            <div class="form-group">
                <label>اسم المنتج <span style="color:var(--primary)">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($edit_prod['name'] ?? ''); ?>" required placeholder="مثال: خبز عربي عادي">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>وحدة القياس</label>
                    <select name="unit">
                        <?php foreach (['ربطة','قطعة','كيلو','كرتون','صندوق'] as $u): ?>
                        <option value="<?php echo $u; ?>" <?php if (($edit_prod['unit'] ?? 'ربطة')==$u) echo 'selected'; ?>><?php echo $u; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>السعر (د.أ)</label>
                    <input type="number" name="price" step="0.001" min="0" value="<?php echo $edit_prod['price'] ?? ''; ?>" placeholder="0.150">
                </div>
            </div>
            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" rows="2"><?php echo htmlspecialchars($edit_prod['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="active" <?php if (($edit_prod['active'] ?? 1)) echo 'checked'; ?>> منتج نشط</label>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?php echo $edit_prod ? 'حفظ التعديل' : 'إضافة'; ?></button>
                <?php if ($edit_prod): ?><a href="dist-products.php" class="btn btn-dark">إلغاء</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-bread-slice"></i> قائمة المنتجات (<?php echo count($products); ?>)</h3>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($products)): ?>
        <div class="empty-state"><i class="fas fa-bread-slice"></i><p>لا توجد منتجات بعد</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>المنتج</th><th>الوحدة</th><th>السعر</th><th>الحالة</th><th>إجراء</th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong><?php if ($p['description']): ?><br><small style="color:#888;"><?php echo htmlspecialchars($p['description']); ?></small><?php endif; ?></td>
                <td><?php echo htmlspecialchars($p['unit']); ?></td>
                <td style="font-weight:700;color:var(--primary);"><?php echo number_format($p['price'],3); ?> د.أ</td>
                <td><span class="badge <?php echo $p['active']?'badge-green':'badge-gray'; ?>"><?php echo $p['active']?'نشط':'موقوف'; ?></span></td>
                <td>
                    <a href="dist-products.php?edit=<?php echo $p['id']; ?>" class="btn btn-xs btn-gold"><i class="fas fa-edit"></i></a>
                    <a href="dist-products.php?delete=<?php echo $p['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('حذف المنتج؟')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
