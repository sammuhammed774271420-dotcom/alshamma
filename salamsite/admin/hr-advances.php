<?php
$admin_title = 'السلف والاستقطاعات';
$admin_icon  = 'hand-holding-usd';
require_once __DIR__ . '/../includes/admin-check.php';

$emp_id = isset($_GET['emp_id']) && is_numeric($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;

$employees = $pdo->query("SELECT id,name,emp_number FROM hr_employees WHERE status='active' ORDER BY name")->fetchAll();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_advance') {
        $eid  = (int)($_POST['emp_id'] ?? 0);
        $date = $_POST['adv_date'] ?? date('Y-m-d');
        $amount = (float)($_POST['amount'] ?? 0);
        $type = $_POST['type'] ?? 'سلفة';
        $desc = trim($_POST['description'] ?? '');

        $pdo->prepare("INSERT INTO hr_advances (emp_id,adv_date,amount,type,description) VALUES (?,?,?,?,?)")
            ->execute([$eid,$date,$amount,$type,$desc]);

        // Record transaction
        $pdo->prepare("INSERT INTO hr_transactions (emp_id,trans_date,type,description,debit,credit,balance) VALUES (?,?,?,?,?,?,?)")
            ->execute([$eid,$date,$type,$desc,$amount,0,0]);

        $msg = 'تم إضافة السلفة/الاستقطاع بنجاح';
    } elseif ($action === 'mark_deducted') {
        $adv_id = (int)($_POST['adv_id'] ?? 0);
        $pdo->prepare("UPDATE hr_advances SET deducted=1 WHERE id=?")->execute([$adv_id]);
        $msg = 'تم تحديد السلفة كمستقطعة';
    } elseif ($action === 'delete_advance') {
        $adv_id = (int)($_POST['adv_id'] ?? 0);
        $pdo->prepare("DELETE FROM hr_advances WHERE id=?")->execute([$adv_id]);
        $msg = 'تم حذف السلفة';
    }
}

// Load advances
$advances = [];
if ($emp_id) {
    $stmt = $pdo->prepare("SELECT * FROM hr_advances WHERE emp_id=? ORDER BY adv_date DESC");
    $stmt->execute([$emp_id]);
    $advances = $stmt->fetchAll();
}

$total_advances = array_sum(array_column($advances, 'amount'));
$deducted = array_sum(array_map(fn($a) => $a['deducted'] ? $a['amount'] : 0, $advances));
$remaining = $total_advances - $deducted;

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.8fr;gap:20px;">

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-plus-circle"></i> إضافة سلفة / استقطاع</h3>
    </div>
    <div class="admin-card-body">
        <form method="post">
            <input type="hidden" name="action" value="add_advance">
            <div class="form-group">
                <label>الموظف</label>
                <select name="emp_id" required style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;width:100%;">
                    <option value="">اختر الموظف</option>
                    <?php foreach ($employees as $e): ?>
                    <option value="<?php echo $e['id']; ?>" <?php if ($emp_id==$e['id']) echo 'selected'; ?>><?php echo htmlspecialchars($e['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>النوع</label>
                <select name="type" style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;width:100%;">
                    <option>سلفة</option>
                    <option>استقطاع</option>
                    <option>غرامة</option>
                    <option>خصم غياب</option>
                </select>
            </div>
            <div class="form-group">
                <label>التاريخ</label>
                <input type="date" name="adv_date" value="<?php echo date('Y-m-d'); ?>" required style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;width:100%;">
            </div>
            <div class="form-group">
                <label>المبلغ (د.أ)</label>
                <input type="number" name="amount" step="0.001" min="0.001" required style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;width:100%;">
            </div>
            <div class="form-group">
                <label>البيان</label>
                <textarea name="description" rows="2" style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;width:100%;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;"><i class="fas fa-plus"></i> إضافة</button>
        </form>
    </div>
</div>

<div>
    <form method="get" style="margin-bottom:15px;">
        <select name="emp_id" onchange="this.form.submit()" style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;min-width:220px;">
            <option value="">اختر موظفاً لعرض سجله</option>
            <?php foreach ($employees as $e): ?>
            <option value="<?php echo $e['id']; ?>" <?php if ($emp_id==$e['id']) echo 'selected'; ?>><?php echo htmlspecialchars($e['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($emp_id): ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px;">
        <div style="background:var(--cream);border-radius:8px;padding:15px;text-align:center;">
            <strong style="font-size:20px;color:var(--primary);"><?php echo number_format($total_advances,3); ?></strong>
            <div style="font-size:12px;color:#666;">إجمالي السلف</div>
        </div>
        <div style="background:#d1fae5;border-radius:8px;padding:15px;text-align:center;">
            <strong style="font-size:20px;color:#16a34a;"><?php echo number_format($deducted,3); ?></strong>
            <div style="font-size:12px;color:#666;">المستقطع</div>
        </div>
        <div style="background:#fee2e2;border-radius:8px;padding:15px;text-align:center;">
            <strong style="font-size:20px;color:var(--primary);"><?php echo number_format($remaining,3); ?></strong>
            <div style="font-size:12px;color:#666;">المتبقي</div>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-list"></i> سجل السلف والاستقطاعات</h3>
        </div>
        <div class="admin-card-body" style="padding:0;">
            <?php if (empty($advances)): ?>
            <div class="empty-state"><i class="fas fa-hand-holding-usd"></i><p><?php echo $emp_id ? 'لا توجد سجلات' : 'اختر موظفاً'; ?></p></div>
            <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>التاريخ</th><th>النوع</th><th>المبلغ</th><th>البيان</th><th>الحالة</th><th>إجراء</th></tr></thead>
                <tbody>
                <?php foreach ($advances as $adv): ?>
                <tr>
                    <td><?php echo $adv['adv_date']; ?></td>
                    <td><span class="badge badge-orange"><?php echo htmlspecialchars($adv['type']); ?></span></td>
                    <td style="font-weight:700;color:var(--primary);"><?php echo number_format($adv['amount'],3); ?> د.أ</td>
                    <td><?php echo htmlspecialchars($adv['description'] ?? '-'); ?></td>
                    <td><span class="badge <?php echo $adv['deducted']?'badge-green':'badge-red'; ?>"><?php echo $adv['deducted']?'مستقطع':'معلق'; ?></span></td>
                    <td style="display:flex;gap:5px;">
                        <?php if (!$adv['deducted']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="mark_deducted">
                            <input type="hidden" name="adv_id" value="<?php echo $adv['id']; ?>">
                            <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
                            <button type="submit" class="btn btn-xs btn-green"><i class="fas fa-check"></i></button>
                        </form>
                        <?php endif; ?>
                        <form method="post" style="display:inline;" onsubmit="return confirm('حذف؟')">
                            <input type="hidden" name="action" value="delete_advance">
                            <input type="hidden" name="adv_id" value="<?php echo $adv['id']; ?>">
                            <input type="hidden" name="emp_id" value="<?php echo $emp_id; ?>">
                            <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
