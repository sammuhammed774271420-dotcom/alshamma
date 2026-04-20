<?php
$admin_title = 'العملاء والمطاعم';
$admin_icon  = 'store';
require_once __DIR__ . '/../includes/admin-check.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM dist_customers WHERE id=?")->execute([$_GET['delete']]);
    header('Location: dist-customers.php?deleted=1'); exit;
}

$search = $_GET['q'] ?? '';
$type   = $_GET['type'] ?? '';

$where = ['1=1'];
$params = [];
if ($search) { $where[] = "(name LIKE ? OR phone LIKE ? OR cust_number LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
if ($type)   { $where[] = "type=?"; $params[] = $type; }

$sql = "SELECT * FROM dist_customers WHERE " . implode(' AND ', $where) . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-store"></i> العملاء والمطاعم والمحلات (<?php echo count($customers); ?>)</h3>
        <a href="dist-customer-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> عميل جديد</a>
    </div>
    <div class="admin-card-body">
        <?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> تم حذف العميل بنجاح.</div><?php endif; ?>
        <?php if (!empty($_GET['saved'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> تم حفظ بيانات العميل بنجاح.</div><?php endif; ?>

        <form method="get" class="search-bar">
            <input type="text" name="q" placeholder="بحث بالاسم أو الهاتف أو الرقم..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="type" onchange="this.form.submit()">
                <option value="">كل الأنواع</option>
                <?php foreach (['مطعم','محل','فرد','شركة'] as $t): ?>
                <option value="<?php echo $t; ?>" <?php if ($type==$t) echo 'selected'; ?>><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> بحث</button>
            <a href="dist-customers.php" class="btn btn-dark btn-sm"><i class="fas fa-times"></i> إعادة</a>
        </form>

        <?php if (empty($customers)): ?>
        <div class="empty-state"><i class="fas fa-store"></i><p>لا يوجد عملاء بعد، أضف عميلاً جديداً</p></div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr><th>#</th><th>رقم العميل</th><th>الاسم</th><th>النوع</th><th>الهاتف</th><th>العنوان</th><th>الرصيد</th><th>الإجراءات</th></tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $i => $c): ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><strong><?php echo htmlspecialchars($c['cust_number'] ?? '-'); ?></strong></td>
                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong><?php if ($c['contact_name']): ?><br><small style="color:#888;"><?php echo htmlspecialchars($c['contact_name']); ?></small><?php endif; ?></td>
                <td><span class="badge badge-blue"><?php echo htmlspecialchars($c['type']); ?></span></td>
                <td><?php echo htmlspecialchars($c['phone'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($c['address'] ?? '-'); ?></td>
                <td style="font-weight:700;color:<?php echo ($c['balance']??0) >= 0 ? '#16a34a' : 'var(--primary)'; ?>;"><?php echo number_format($c['balance'] ?? 0, 3); ?> د.أ</td>
                <td style="display:flex;gap:5px;flex-wrap:wrap;">
                    <a href="dist-customer-form.php?id=<?php echo $c['id']; ?>" class="btn btn-xs btn-gold"><i class="fas fa-edit"></i></a>
                    <a href="dist-receipt-form.php?cust_id=<?php echo $c['id']; ?>" class="btn btn-xs btn-primary" title="إنشاء سند"><i class="fas fa-file-invoice"></i></a>
                    <a href="dist-receipts.php?cust_id=<?php echo $c['id']; ?>" class="btn btn-xs btn-blue" title="السندات"><i class="fas fa-list"></i></a>
                    <a href="dist-customers.php?delete=<?php echo $c['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('حذف العميل <?php echo htmlspecialchars(addslashes($c['name'])); ?>؟')"><i class="fas fa-trash"></i></a>
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
