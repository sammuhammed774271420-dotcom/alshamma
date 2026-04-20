<?php
$admin_title = 'إدارة الرواتب';
$admin_icon  = 'money-bill-wave';
require_once __DIR__ . '/../includes/admin-check.php';

$emp_id = isset($_GET['emp_id']) && is_numeric($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;
$month  = $_GET['month'] ?? date('Y-m');

$employees = $pdo->query("SELECT id,name,emp_number,base_salary FROM hr_employees WHERE status='active' ORDER BY name")->fetchAll();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_salary') {
        $eid        = (int)($_POST['emp_id'] ?? 0);
        $sal_month  = $_POST['month'] ?? '';
        $base       = (float)($_POST['base_salary'] ?? 0);
        $additions  = (float)($_POST['additions'] ?? 0);
        $deductions = (float)($_POST['deductions'] ?? 0);
        $net        = $base + $additions - $deductions;
        $paid       = isset($_POST['paid']) ? 1 : 0;
        $notes      = trim($_POST['notes'] ?? '');

        $exists = $pdo->prepare("SELECT id FROM hr_salaries WHERE emp_id=? AND month=?");
        $exists->execute([$eid, $sal_month]);
        $old = $exists->fetch();

        if ($old) {
            $pdo->prepare("UPDATE hr_salaries SET base_salary=?,additions=?,deductions=?,net_salary=?,paid=?,notes=? WHERE id=?")
                ->execute([$base,$additions,$deductions,$net,$paid,$notes,$old['id']]);
        } else {
            $pdo->prepare("INSERT INTO hr_salaries (emp_id,month,base_salary,additions,deductions,net_salary,paid,notes) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$eid,$sal_month,$base,$additions,$deductions,$net,$paid,$notes]);
        }

        // Add to transactions
        $pdo->prepare("INSERT INTO hr_transactions (emp_id,trans_date,type,description,credit,debit,balance) VALUES (?,?,?,?,?,?,?)")
            ->execute([$eid, $sal_month . '-01', 'راتب', "راتب شهر $sal_month", $net, 0, 0]);
        $msg = 'تم حفظ بيانات الراتب بنجاح';
    }
}

// Load current salary record
$salary_rec = null;
if ($emp_id) {
    $stmt = $pdo->prepare("SELECT * FROM hr_salaries WHERE emp_id=? AND month=?");
    $stmt->execute([$emp_id, $month]);
    $salary_rec = $stmt->fetch();
}

// Salary history
$salary_history = [];
if ($emp_id) {
    $stmt = $pdo->prepare("SELECT * FROM hr_salaries WHERE emp_id=? ORDER BY month DESC LIMIT 12");
    $stmt->execute([$emp_id]);
    $salary_history = $stmt->fetchAll();
}

// Selected employee info
$emp = null;
if ($emp_id) {
    $stmt = $pdo->prepare("SELECT * FROM hr_employees WHERE id=?");
    $stmt->execute([$emp_id]);
    $emp = $stmt->fetch();
}

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.3fr;gap:20px;">

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-money-bill-wave"></i> تسجيل راتب</h3>
    </div>
    <div class="admin-card-body">
        <form method="get" style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
            <select name="emp_id" onchange="this.form.submit()" style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;">
                <option value="">اختر الموظف</option>
                <?php foreach ($employees as $e): ?>
                <option value="<?php echo $e['id']; ?>" <?php if ($emp_id==$e['id']) echo 'selected'; ?>><?php echo htmlspecialchars($e['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="month" name="month" value="<?php echo $month; ?>" onchange="this.form.submit()" style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;">
        </form>

        <?php if ($emp_id && $emp): ?>
        <div style="background:var(--cream);border-radius:8px;padding:15px;margin-bottom:18px;">
            <div style="font-size:14px;"><strong><?php echo htmlspecialchars($emp['name']); ?></strong></div>
            <div style="font-size:12px;color:#888;"><?php echo htmlspecialchars($emp['position'] ?? ''); ?> - <?php echo htmlspecialchars($emp['department'] ?? ''); ?></div>
            <div style="font-size:13px;color:var(--primary);font-weight:700;margin-top:5px;">الراتب الأساسي: <?php echo number_format($emp['base_salary'],3); ?> د.أ</div>
        </div>

        <form method="post">
            <input type="hidden" name="action" value="save_salary">
            <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
            <div class="form-group">
                <label>الشهر</label>
                <input type="month" name="month" value="<?php echo $month; ?>" required style="padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;width:100%;">
            </div>
            <div class="form-group">
                <label>الراتب الأساسي</label>
                <input type="number" name="base_salary" step="0.001" value="<?php echo $salary_rec['base_salary'] ?? $emp['base_salary']; ?>" id="base_salary" oninput="calcNet()">
            </div>
            <div class="form-group">
                <label>الإضافات</label>
                <input type="number" name="additions" step="0.001" value="<?php echo $salary_rec['additions'] ?? 0; ?>" id="additions" oninput="calcNet()">
            </div>
            <div class="form-group">
                <label>الاستقطاعات (خصومات)</label>
                <input type="number" name="deductions" step="0.001" value="<?php echo $salary_rec['deductions'] ?? 0; ?>" id="deductions" oninput="calcNet()">
            </div>
            <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;border-radius:8px;padding:15px;margin-bottom:15px;text-align:center;">
                <div style="font-size:13px;opacity:0.9;">صافي الراتب</div>
                <div style="font-size:28px;font-weight:900;" id="net_display"><?php echo number_format(($salary_rec['net_salary'] ?? ($emp['base_salary'])), 3); ?> د.أ</div>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="paid" <?php if ($salary_rec['paid'] ?? 0) echo 'checked'; ?>> تم الصرف</label>
            </div>
            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes" rows="2"><?php echo htmlspecialchars($salary_rec['notes'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-save"></i> حفظ الراتب</button>
        </form>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-user"></i><p>اختر موظفاً</p></div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-history"></i> سجل الرواتب</h3>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($salary_history)): ?>
        <div class="empty-state"><i class="fas fa-money-bill-wave"></i><p>لا توجد سجلات رواتب</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الشهر</th><th>الأساسي</th><th>الإضافات</th><th>الاستقطاعات</th><th>الصافي</th><th>الحالة</th></tr></thead>
            <tbody>
            <?php foreach ($salary_history as $s): ?>
            <tr>
                <td><strong><?php echo $s['month']; ?></strong></td>
                <td><?php echo number_format($s['base_salary'],3); ?></td>
                <td style="color:#16a34a;">+<?php echo number_format($s['additions'],3); ?></td>
                <td style="color:var(--primary);">-<?php echo number_format($s['deductions'],3); ?></td>
                <td style="font-weight:700;color:var(--primary);"><?php echo number_format($s['net_salary'],3); ?></td>
                <td><span class="badge <?php echo $s['paid']?'badge-green':'badge-orange'; ?>"><?php echo $s['paid']?'تم الصرف':'لم يُصرف'; ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</div>

<script>
function calcNet() {
    var base = parseFloat(document.getElementById('base_salary').value) || 0;
    var add  = parseFloat(document.getElementById('additions').value) || 0;
    var ded  = parseFloat(document.getElementById('deductions').value) || 0;
    var net  = base + add - ded;
    document.getElementById('net_display').textContent = net.toFixed(3) + ' د.أ';
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
