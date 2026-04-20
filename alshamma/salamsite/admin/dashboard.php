<?php
$admin_title = 'لوحة التحكم';
$admin_icon  = 'tachometer-alt';
require_once __DIR__ . '/../includes/admin-check.php';

// Website stats
$cnt_messages  = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='جديد'")->fetchColumn();
$cnt_services  = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

// HR stats
$cnt_employees  = 0; $cnt_today_att  = 0; $pending_adv = 0;
try {
    $cnt_employees = $pdo->query("SELECT COUNT(*) FROM hr_employees WHERE status='active'")->fetchColumn();
    $cnt_today_att = $pdo->query("SELECT COUNT(*) FROM hr_attendance WHERE att_date='".date('Y-m-d')."' AND status='حضور'")->fetchColumn();
    $pending_adv   = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM hr_advances WHERE deducted=0")->fetchColumn();
} catch(Exception $e) {}

// Distribution stats
$cnt_customers = 0; $cnt_receipts = 0; $today_total = 0;
try {
    $cnt_customers = $pdo->query("SELECT COUNT(*) FROM dist_customers WHERE status='active'")->fetchColumn();
    $cnt_receipts  = $pdo->query("SELECT COUNT(*) FROM dist_receipts WHERE receipt_date='".date('Y-m-d')."'")->fetchColumn();
    $today_total   = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM dist_receipts WHERE receipt_date='".date('Y-m-d')."'")->fetchColumn();
    $month_total   = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM dist_receipts WHERE receipt_date LIKE '".date('Y-m')."%'")->fetchColumn();
} catch(Exception $e) { $month_total = 0; }

$recent_receipts = [];
try {
    $recent_receipts = $pdo->query("SELECT r.*, c.name as cust_name FROM dist_receipts r JOIN dist_customers c ON r.cust_id=c.id ORDER BY r.id DESC LIMIT 5")->fetchAll();
} catch(Exception $e) {}

$recent_msg = $pdo->query("SELECT * FROM contact_messages ORDER BY contact_date DESC LIMIT 5")->fetchAll();

include __DIR__ . '/../includes/admin-header.php';
?>

<div style="margin-bottom:8px;">
    <h3 style="font-size:14px;color:#888;margin-bottom:12px;"><i class="fas fa-bread-slice" style="color:var(--primary);"></i> نظرة عامة اليوم - <?php echo date('Y/m/d'); ?></h3>
</div>

<div class="admin-stats-grid">
    <div class="admin-stat-card red-border">
        <div class="admin-stat-icon red"><i class="fas fa-store"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_customers; ?></strong><span>عملاء نشطون</span></div>
    </div>
    <div class="admin-stat-card blue-border">
        <div class="admin-stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_receipts; ?></strong><span>سندات اليوم</span></div>
    </div>
    <div class="admin-stat-card gold-border">
        <div class="admin-stat-icon gold"><i class="fas fa-coins"></i></div>
        <div class="admin-stat-info"><strong><?php echo number_format($today_total,3); ?></strong><span>مبيعات اليوم</span></div>
    </div>
    <div class="admin-stat-card green-border">
        <div class="admin-stat-icon green"><i class="fas fa-users"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_employees; ?></strong><span>موظفون نشطون</span></div>
    </div>
    <div class="admin-stat-card" style="border-right-color:#3b82f6;">
        <div class="admin-stat-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;"><i class="fas fa-calendar-check"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_today_att; ?></strong><span>حضور اليوم</span></div>
    </div>
    <div class="admin-stat-card" style="border-right-color:#f97316;">
        <div class="admin-stat-icon orange"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="admin-stat-info"><strong><?php echo number_format($pending_adv,3); ?></strong><span>سلف معلقة</span></div>
    </div>
</div>

<div class="dashboard-grid-2">

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-file-invoice"></i> آخر سندات التسليم</h3>
        <a href="/admin/dist-receipts.php" class="btn btn-sm btn-primary">كل السندات</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($recent_receipts)): ?>
        <div class="empty-state" style="padding:30px;"><i class="fas fa-file-invoice"></i><p>لا توجد سندات بعد</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>رقم السند</th><th>العميل</th><th>التاريخ</th><th>المجموع</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recent_receipts as $r): ?>
            <tr>
                <td><strong style="color:var(--primary);"><?php echo htmlspecialchars($r['receipt_num']); ?></strong></td>
                <td><?php echo htmlspecialchars($r['cust_name']); ?></td>
                <td style="font-size:12px;color:#888;"><?php echo $r['receipt_date']; ?></td>
                <td style="font-weight:700;"><?php echo number_format($r['total_amount'],3); ?> د.أ</td>
                <td><a href="/admin/dist-receipt-pdf.php?id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-xs btn-primary"><i class="fas fa-print"></i></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-rocket"></i> اختصارات سريعة</h3>
    </div>
    <div class="admin-card-body">
        <div style="display:flex;flex-direction:column;gap:10px;">
            <a href="/admin/dist-receipt-form.php" class="btn btn-primary" style="justify-content:center;"><i class="fas fa-file-invoice"></i> إنشاء سند تسليم جديد</a>
            <a href="/admin/dist-customers.php" class="btn btn-gold" style="justify-content:center;"><i class="fas fa-store"></i> إدارة العملاء</a>
            <a href="/admin/hr-employees.php" class="btn btn-blue" style="justify-content:center;"><i class="fas fa-users"></i> إدارة الموظفين</a>
            <a href="/admin/hr-statements.php" class="btn btn-dark" style="background:#555;justify-content:center;"><i class="fas fa-file-alt"></i> كشوف الحسابات</a>
        </div>

        <div style="margin-top:22px;padding-top:18px;border-top:1px solid #eee;">
            <h4 style="font-size:13px;margin-bottom:12px;color:#666;">مبيعات هذا الشهر</h4>
            <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));border-radius:8px;padding:15px;text-align:center;color:#fff;">
                <div style="font-size:26px;font-weight:900;"><?php echo number_format($month_total, 3); ?></div>
                <div style="font-size:12px;opacity:0.8;">دينار أردني - <?php echo date('Y/m'); ?></div>
            </div>
        </div>
    </div>
</div>

</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-envelope"></i> رسائل التواصل الأخيرة</h3>
        <a href="/admin/messages.php" class="btn btn-sm btn-dark">كل الرسائل</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($recent_msg)): ?>
        <div class="empty-state" style="padding:30px;"><i class="fas fa-inbox"></i><p>لا توجد رسائل</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الاسم</th><th>الموضوع</th><th>التاريخ</th><th>الحالة</th></tr></thead>
            <tbody>
            <?php foreach ($recent_msg as $m): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($m['subject'] ?? 'بدون موضوع'); ?></td>
                <td style="font-size:12px;color:#888;"><?php echo date('Y/m/d', strtotime($m['contact_date'])); ?></td>
                <td><span class="badge <?php echo $m['status']=='جديد'?'badge-red':'badge-green'; ?>"><?php echo htmlspecialchars($m['status']); ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
