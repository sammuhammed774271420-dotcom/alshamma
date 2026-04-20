<?php
$admin_title = 'إدارة الموظفين';
$admin_icon  = 'users';
require_once __DIR__ . '/../includes/admin-check.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM hr_employees WHERE id=?")->execute([$_GET['delete']]);
    header('Location: hr-employees.php?deleted=1'); exit;
}

$search = $_GET['q'] ?? '';
$dept   = $_GET['dept'] ?? '';
$status = $_GET['status'] ?? '';

$where = ['1=1'];
$params = [];
if ($search) { $where[] = "name LIKE ?"; $params[] = "%$search%"; }
if ($dept)   { $where[] = "department = ?"; $params[] = $dept; }
if ($status) { $where[] = "status = ?"; $params[] = $status; }

$sql = "SELECT * FROM hr_employees WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();

$depts = $pdo->query("SELECT DISTINCT department FROM hr_employees WHERE department IS NOT NULL AND department != '' ORDER BY department")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-users"></i> قائمة الموظفين (<?php echo count($employees); ?>)</h3>
        <a href="hr-employee-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> موظف جديد</a>
    </div>
    <div class="admin-card-body">

        <?php if (!empty($_GET['deleted'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> تم حذف الموظف بنجاح.</div>
        <?php endif; ?>
        <?php if (!empty($_GET['saved'])): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> تم حفظ بيانات الموظف بنجاح.</div>
        <?php endif; ?>

        <form method="get" class="search-bar">
            <input type="text" name="q" placeholder="البحث باسم الموظف..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="dept">
                <option value="">كل الأقسام</option>
                <?php foreach ($depts as $d): ?>
                <option value="<?php echo htmlspecialchars($d); ?>" <?php if ($dept==$d) echo 'selected'; ?>><?php echo htmlspecialchars($d); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status">
                <option value="">كل الحالات</option>
                <option value="active"   <?php if ($status=='active') echo 'selected'; ?>>نشط</option>
                <option value="inactive" <?php if ($status=='inactive') echo 'selected'; ?>>غير نشط</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> بحث</button>
            <a href="hr-employees.php" class="btn btn-dark btn-sm"><i class="fas fa-times"></i> إعادة</a>
        </form>

        <?php if (empty($employees)): ?>
        <div class="empty-state"><i class="fas fa-users"></i><p>لا يوجد موظفون بعد</p></div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الموظف</th>
                    <th>الاسم</th>
                    <th>القسم</th>
                    <th>الوظيفة</th>
                    <th>الراتب الأساسي</th>
                    <th>الهاتف</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($employees as $i => $emp): ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><strong><?php echo htmlspecialchars($emp['emp_number']); ?></strong></td>
                <td><?php echo htmlspecialchars($emp['name']); ?></td>
                <td><?php echo htmlspecialchars($emp['department'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($emp['position'] ?? '-'); ?></td>
                <td style="font-weight:700;color:var(--primary);"><?php echo number_format($emp['base_salary'],3); ?> د.أ</td>
                <td><?php echo htmlspecialchars($emp['phone'] ?? '-'); ?></td>
                <td>
                    <span class="badge <?php echo $emp['status']=='active'?'badge-green':'badge-gray'; ?>">
                        <?php echo $emp['status']=='active' ? 'نشط' : 'غير نشط'; ?>
                    </span>
                </td>
                <td>
                    <a href="hr-employee-form.php?id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i></a>
                    <a href="hr-statements.php?emp_id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-blue" title="كشف حساب"><i class="fas fa-file-alt"></i></a>
                    <a href="hr-attendance.php?emp_id=<?php echo $emp['id']; ?>" class="btn btn-sm btn-green" title="الحضور"><i class="fas fa-calendar-check"></i></a>
                    <a href="hr-employees.php?delete=<?php echo $emp['id']; ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('هل تريد حذف الموظف <?php echo htmlspecialchars(addslashes($emp['name'])); ?>؟')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
