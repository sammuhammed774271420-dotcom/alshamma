<?php
$admin_title = 'الحضور والغياب';
$admin_icon  = 'calendar-check';
require_once __DIR__ . '/../includes/admin-check.php';

$emp_id = isset($_GET['emp_id']) && is_numeric($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;
$month  = $_GET['month'] ?? date('Y-m');

$employees = $pdo->query("SELECT id,name,emp_number FROM hr_employees WHERE status='active' ORDER BY name")->fetchAll();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save_attendance') {
        $dates   = $_POST['att_date'] ?? [];
        $statuses = $_POST['att_status'] ?? [];
        $notes_arr = $_POST['att_notes'] ?? [];
        $eid = (int)($_POST['emp_id'] ?? 0);
        foreach ($dates as $i => $date) {
            if (!$date) continue;
            $st = $statuses[$i] ?? 'حضور';
            $nt = $notes_arr[$i] ?? '';
            $exists = $pdo->prepare("SELECT id FROM hr_attendance WHERE emp_id=? AND att_date=?");
            $exists->execute([$eid, $date]);
            if ($exists->fetch()) {
                $pdo->prepare("UPDATE hr_attendance SET status=?,notes=? WHERE emp_id=? AND att_date=?")
                    ->execute([$st,$nt,$eid,$date]);
            } else {
                $pdo->prepare("INSERT INTO hr_attendance (emp_id,att_date,status,notes) VALUES (?,?,?,?)")
                    ->execute([$eid,$date,$st,$nt]);
            }
        }
        $msg = 'تم حفظ بيانات الحضور بنجاح';
    } elseif ($action === 'bulk_add') {
        $bulk_emp = (int)($_POST['bulk_emp'] ?? 0);
        $bulk_from = $_POST['bulk_from'] ?? '';
        $bulk_to   = $_POST['bulk_to'] ?? '';
        $bulk_status = $_POST['bulk_status'] ?? 'حضور';
        if ($bulk_emp && $bulk_from && $bulk_to) {
            $d = new DateTime($bulk_from);
            $end = new DateTime($bulk_to);
            while ($d <= $end) {
                $date = $d->format('Y-m-d');
                $exists = $pdo->prepare("SELECT id FROM hr_attendance WHERE emp_id=? AND att_date=?");
                $exists->execute([$bulk_emp, $date]);
                if (!$exists->fetch()) {
                    $pdo->prepare("INSERT INTO hr_attendance (emp_id,att_date,status) VALUES (?,?,?)")
                        ->execute([$bulk_emp,$date,$bulk_status]);
                }
                $d->modify('+1 day');
            }
            $msg = 'تم إضافة بيانات الحضور الجماعية بنجاح';
        }
    }
}

// Load attendance for selected employee & month
$attendance_data = [];
if ($emp_id) {
    $stmt = $pdo->prepare("SELECT * FROM hr_attendance WHERE emp_id=? AND att_date LIKE ? ORDER BY att_date");
    $stmt->execute([$emp_id, $month . '%']);
    foreach ($stmt->fetchAll() as $row) {
        $attendance_data[$row['att_date']] = $row;
    }
}

// Summary
$summary = ['حضور'=>0,'غياب'=>0,'إجازة'=>0,'إجازة مرضية'=>0];
foreach ($attendance_data as $row) { if (isset($summary[$row['status']])) $summary[$row['status']]++; }

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-calendar-check"></i> إدارة الحضور والغياب</h3>
    </div>
    <div class="admin-card-body">
        <!-- Filter -->
        <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <select name="emp_id" onchange="this.form.submit()" style="padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;min-width:200px;">
                <option value="">اختر الموظف</option>
                <?php foreach ($employees as $e): ?>
                <option value="<?php echo $e['id']; ?>" <?php if ($emp_id==$e['id']) echo 'selected'; ?>><?php echo htmlspecialchars($e['name']); ?> (<?php echo $e['emp_number']; ?>)</option>
                <?php endforeach; ?>
            </select>
            <input type="month" name="month" value="<?php echo $month; ?>" onchange="this.form.submit()" style="padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;">
        </form>

        <!-- Bulk Add -->
        <div class="admin-card" style="background:var(--cream);margin-bottom:20px;">
            <div class="admin-card-header" style="background:rgba(201,162,39,0.1);">
                <h3 style="font-size:14px;"><i class="fas fa-plus-circle" style="color:var(--gold);"></i> إضافة جماعية للحضور</h3>
            </div>
            <div class="admin-card-body">
            <form method="post" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                <input type="hidden" name="action" value="bulk_add">
                <div>
                    <label style="font-size:13px;display:block;margin-bottom:4px;">الموظف</label>
                    <select name="bulk_emp" style="padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;min-width:160px;">
                        <option value="">اختر الموظف</option>
                        <?php foreach ($employees as $e): ?>
                        <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px;display:block;margin-bottom:4px;">من تاريخ</label>
                    <input type="date" name="bulk_from" style="padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;">
                </div>
                <div>
                    <label style="font-size:13px;display:block;margin-bottom:4px;">إلى تاريخ</label>
                    <input type="date" name="bulk_to" style="padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;">
                </div>
                <div>
                    <label style="font-size:13px;display:block;margin-bottom:4px;">الحالة</label>
                    <select name="bulk_status" style="padding:9px 14px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;">
                        <option>حضور</option>
                        <option>غياب</option>
                        <option>إجازة</option>
                        <option>إجازة مرضية</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-gold"><i class="fas fa-check"></i> تطبيق</button>
            </form>
            </div>
        </div>

        <?php if ($emp_id): ?>
        <!-- Summary -->
        <div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px;">
            <?php foreach ($summary as $k => $v): ?>
            <div style="background:#f0f0f0;border-radius:8px;padding:12px 20px;text-align:center;min-width:100px;">
                <strong style="font-size:22px;display:block;"><?php echo $v; ?></strong>
                <span style="font-size:12px;color:#666;"><?php echo $k; ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Attendance Table -->
        <?php
        $year_month = explode('-', $month);
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int)$year_month[1], (int)$year_month[0]);
        ?>
        <form method="post">
            <input type="hidden" name="action" value="save_attendance">
            <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
            <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>م</th><th>التاريخ</th><th>اليوم</th><th>الحالة</th><th>ملاحظات</th></tr></thead>
                <tbody>
                <?php for ($d = 1; $d <= $days_in_month; $d++):
                    $date_str = $month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                    $day_name = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'][date('w', strtotime($date_str))];
                    $rec = $attendance_data[$date_str] ?? null;
                    $is_friday = date('w', strtotime($date_str)) == 5;
                ?>
                <tr style="<?php echo $is_friday ? 'background:#fff8ef;' : ''; ?>">
                    <td><?php echo $d; ?></td>
                    <td><?php echo $date_str; ?></td>
                    <td><?php echo $day_name; ?></td>
                    <td>
                        <input type="hidden" name="att_date[]" value="<?php echo $date_str; ?>">
                        <select name="att_status[]" style="padding:5px 10px;border:1px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;font-size:13px;">
                            <?php foreach (['حضور','غياب','إجازة','إجازة مرضية'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php if (($rec['status'] ?? ($is_friday ? 'إجازة' : 'حضور'))==$s) echo 'selected'; ?>><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="att_notes[]" value="<?php echo htmlspecialchars($rec['notes'] ?? ''); ?>" style="padding:5px 10px;border:1px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;font-size:13px;width:200px;"></td>
                </tr>
                <?php endfor; ?>
                </tbody>
            </table>
            </div>
            <div style="margin-top:20px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ بيانات الحضور</button>
            </div>
        </form>
        <?php else: ?>
        <div class="empty-state"><i class="fas fa-user"></i><p>اختر موظفاً لعرض سجل الحضور</p></div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
