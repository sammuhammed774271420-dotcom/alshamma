<?php
$admin_title = 'بيانات العميل';
$admin_icon  = 'store';
require_once __DIR__ . '/../includes/admin-check.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$cust = [];
if ($id) {
    $s = $pdo->prepare("SELECT * FROM dist_customers WHERE id=?");
    $s->execute([$id]);
    $cust = $s->fetch();
    if (!$cust) { header('Location: dist-customers.php'); exit; }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $cust_number  = trim($_POST['cust_number'] ?? '');
    $type         = $_POST['type'] ?? 'مطعم';
    $address      = trim($_POST['address'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    if (!$name) $errors[] = 'اسم العميل مطلوب';

    if (empty($errors)) {
        if ($id) {
            $pdo->prepare("UPDATE dist_customers SET name=?,cust_number=?,type=?,address=?,phone=?,contact_name=?,notes=? WHERE id=?")
                ->execute([$name,$cust_number,$type,$address,$phone,$contact_name,$notes,$id]);
        } else {
            $pdo->prepare("INSERT INTO dist_customers (name,cust_number,type,address,phone,contact_name,notes) VALUES (?,?,?,?,?,?,?)")
                ->execute([$name,$cust_number,$type,$address,$phone,$contact_name,$notes]);
        }
        header('Location: dist-customers.php?saved=1'); exit;
    }
    $cust = compact('name','cust_number','type','address','phone','contact_name','notes');
}

if (!$id) {
    $last = (int)$pdo->query("SELECT COUNT(*) FROM dist_customers")->fetchColumn();
    $auto_num = 'C-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
}

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-store"></i> <?php echo $id ? 'تعديل بيانات العميل' : 'إضافة عميل جديد'; ?></h3>
        <a href="dist-customers.php" class="btn btn-dark btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
    </div>
    <div class="admin-card-body">
        <?php foreach ($errors as $e): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $e; ?></div><?php endforeach; ?>

        <form method="post" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label>اسم المطعم / المحل / العميل <span style="color:var(--primary)">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($cust['name'] ?? ''); ?>" required placeholder="مثال: مطعم الشام">
                </div>
                <div class="form-group">
                    <label>رقم العميل</label>
                    <input type="text" name="cust_number" value="<?php echo htmlspecialchars($cust['cust_number'] ?? ($auto_num ?? '')); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>نوع العميل</label>
                    <select name="type">
                        <?php foreach (['مطعم','محل','فرد','شركة'] as $t): ?>
                        <option value="<?php echo $t; ?>" <?php if (($cust['type'] ?? 'مطعم')==$t) echo 'selected'; ?>><?php echo $t; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>اسم الشخص المسؤول</label>
                    <input type="text" name="contact_name" value="<?php echo htmlspecialchars($cust['contact_name'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>رقم الهاتف</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($cust['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>العنوان</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($cust['address'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes"><?php echo htmlspecialchars($cust['notes'] ?? ''); ?></textarea>
            </div>
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ</button>
                <a href="dist-customers.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
