<?php
$admin_title = 'سندات التسليم';
$admin_icon  = 'file-invoice';
require_once __DIR__ . '/../includes/admin-check.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM dist_receipt_items WHERE receipt_id=?")->execute([$_GET['delete']]);
    $pdo->prepare("DELETE FROM dist_receipts WHERE id=?")->execute([$_GET['delete']]);
    header('Location: dist-receipts.php?deleted=1'); exit;
}

$cust_id = isset($_GET['cust_id']) && is_numeric($_GET['cust_id']) ? (int)$_GET['cust_id'] : 0;
$search  = $_GET['q'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to   = $_GET['date_to'] ?? '';

$where = ['1=1'];
$params = [];
if ($cust_id) { $where[] = "r.cust_id=?"; $params[] = $cust_id; }
if ($search)  { $where[] = "(r.receipt_num LIKE ? OR c.name LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%"]); }
if ($date_from) { $where[] = "r.receipt_date >= ?"; $params[] = $date_from; }
if ($date_to)   { $where[] = "r.receipt_date <= ?"; $params[] = $date_to; }

$sql = "SELECT r.*, c.name as cust_name, c.type as cust_type FROM dist_receipts r
        JOIN dist_customers c ON r.cust_id = c.id
        WHERE " . implode(' AND ', $where) . " ORDER BY r.receipt_date DESC, r.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$receipts = $stmt->fetchAll();

$customers = $pdo->query("SELECT id,name FROM dist_customers ORDER BY name")->fetchAll();

$total = array_sum(array_column($receipts, 'total_amount'));

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-file-invoice"></i> سندات التسليم (<?php echo count($receipts); ?>) &nbsp;|&nbsp; الإجمالي: <span style="color:var(--primary)"><?php echo number_format($total,3); ?> د.أ</span></h3>
        <a href="dist-receipt-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> سند جديد</a>
    </div>
    <div class="admin-card-body">
        <?php if (!empty($_GET['deleted'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> تم حذف السند.</div><?php endif; ?>
        <?php if (!empty($_GET['saved'])): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> تم إنشاء السند بنجاح.</div><?php endif; ?>

        <form method="get" class="search-bar">
            <input type="text" name="q" placeholder="بحث برقم السند أو اسم العميل..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="cust_id" onchange="this.form.submit()">
                <option value="">كل العملاء</option>
                <?php foreach ($customers as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php if ($cust_id==$c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" value="<?php echo $date_from; ?>" placeholder="من تاريخ">
            <input type="date" name="date_to"   value="<?php echo $date_to; ?>"   placeholder="إلى تاريخ">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> بحث</button>
            <a href="dist-receipts.php" class="btn btn-dark btn-sm">إعادة</a>
        </form>

        <?php if (empty($receipts)): ?>
        <div class="empty-state"><i class="fas fa-file-invoice"></i><p>لا توجد سندات، أنشئ سنداً جديداً</p></div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>رقم السند</th><th>التاريخ</th><th>العميل</th><th>المجموع</th><th>الإجراءات</th></tr></thead>
            <tbody>
            <?php foreach ($receipts as $r): ?>
            <tr>
                <td><strong style="color:var(--primary);"><?php echo htmlspecialchars($r['receipt_num']); ?></strong></td>
                <td><?php echo $r['receipt_date']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($r['cust_name']); ?></strong>
                    <span class="badge badge-blue"><?php echo htmlspecialchars($r['cust_type']); ?></span>
                </td>
                <td style="font-weight:700;color:var(--primary);"><?php echo number_format($r['total_amount'],3); ?> د.أ</td>
                <td style="display:flex;gap:5px;flex-wrap:wrap;">
                    <a href="dist-receipt-pdf.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-xs btn-primary" title="عرض/طباعة"><i class="fas fa-print"></i> طباعة</a>
                    <a href="dist-receipt-form.php?id=<?php echo $r['id']; ?>" class="btn btn-xs btn-gold"><i class="fas fa-edit"></i></a>
                    <a href="dist-receipts.php?delete=<?php echo $r['id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('حذف السند؟')"><i class="fas fa-trash"></i></a>
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
