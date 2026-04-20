<?php
$admin_title = 'بيانات الموظف';
$admin_icon  = 'user-edit';
require_once __DIR__ . '/../includes/admin-check.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$emp = $id ? $pdo->prepare("SELECT * FROM hr_employees WHERE id=?")->execute([$id]) && ($tmp = $pdo->prepare("SELECT * FROM hr_employees WHERE id=?")) && $tmp->execute([$id]) ? $tmp->fetch() : [] : [];

// Fetch properly
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM hr_employees WHERE id=?");
    $stmt->execute([$id]);
    $emp = $stmt->fetch();
    if (!$emp) { header('Location: hr-employees.php'); exit; }
} else {
    $emp = [];
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $emp_number = trim($_POST['emp_number'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $position   = trim($_POST['position'] ?? '');
    $hire_date  = $_POST['hire_date'] ?? '';
    $base_salary = (float)($_POST['base_salary'] ?? 0);
    $phone      = trim($_POST['phone'] ?? '');
    $national_id = trim($_POST['national_id'] ?? '');
    $notes      = trim($_POST['notes'] ?? '');
    $status     = $_POST['status'] ?? 'active';

    if (!$name) $errors[] = 'اسم الموظف مطلوب';
    if (!$emp_number) $errors[] = 'رقم الموظف مطلوب';

    if (empty($errors)) {
        if ($id) {
            $pdo->prepare("UPDATE hr_employees SET name=?,emp_number=?,department=?,position=?,hire_date=?,base_salary=?,phone=?,national_id=?,notes=?,status=? WHERE id=?")
                ->execute([$name,$emp_number,$department,$position,$hire_date,$base_salary,$phone,$national_id,$notes,$status,$id]);
        } else {
            $pdo->prepare("INSERT INTO hr_employees (name,emp_number,department,position,hire_date,base_salary,phone,national_id,notes,status) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$name,$emp_number,$department,$position,$hire_date,$base_salary,$phone,$national_id,$notes,$status]);
        }
        header('Location: hr-employees.php?saved=1'); exit;
    }
    $emp = compact('name','emp_number','department','position','hire_date','base_salary','phone','national_id','notes','status');
}

// Auto-generate employee number
if (!$id) {
    $last = (int)$pdo->query("SELECT COUNT(*) FROM hr_employees")->fetchColumn();
    $auto_num = 'EMP-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
} else {
    $auto_num = $emp['emp_number'] ?? '';
}

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-user-edit"></i> <?php echo $id ? 'تعديل بيانات الموظف' : 'إضافة موظف جديد'; ?></h3>
        <a href="hr-employees.php" class="btn btn-dark btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
    </div>
    <div class="admin-card-body">
        <?php foreach ($errors as $e): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $e; ?></div><?php endforeach; ?>

        <form method="post" class="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label>اسم الموظف <span style="color:var(--primary)">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($emp['name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>رقم الموظف <span style="color:var(--primary)">*</span></label>
                    <input type="text" name="emp_number" value="<?php echo htmlspecialchars($emp['emp_number'] ?? $auto_num); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>القسم</label>
                    <input type="text" name="department" value="<?php echo htmlspecialchars($emp['department'] ?? ''); ?>" placeholder="مثال: الإنتاج، التوزيع...">
                </div>
                <div class="form-group">
                    <label>المسمى الوظيفي</label>
                    <input type="text" name="position" value="<?php echo htmlspecialchars($emp['position'] ?? ''); ?>" placeholder="مثال: عامل مخبز، سائق...">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>تاريخ التعيين</label>
                    <input type="date" name="hire_date" value="<?php echo htmlspecialchars($emp['hire_date'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>الراتب الأساسي (دينار)</label>
                    <input type="number" name="base_salary" step="0.001" min="0" value="<?php echo htmlspecialchars($emp['base_salary'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>رقم الهاتف</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($emp['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>رقم الهوية الوطنية</label>
                    <input type="text" name="national_id" value="<?php echo htmlspecialchars($emp['national_id'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status">
                        <option value="active"   <?php if (($emp['status'] ?? 'active')=='active') echo 'selected'; ?>>نشط</option>
                        <option value="inactive" <?php if (($emp['status'] ?? '')=='inactive') echo 'selected'; ?>>غير نشط</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes"><?php echo htmlspecialchars($emp['notes'] ?? ''); ?></textarea>
            </div>
            <div style="display:flex;gap:12px;align-items:center;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ البيانات</button>
                <a href="hr-employees.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
