<?php
$admin_title = 'إدارة الخدمات';
$admin_icon = 'concierge-bell';
require_once __DIR__ . '/../includes/admin-check.php';

$success = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM services WHERE id=?")->execute([(int)$_GET['delete']]);
    $success = 'تم حذف الخدمة بنجاح';
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $cur = $pdo->prepare("SELECT active FROM services WHERE id=?");
    $cur->execute([(int)$_GET['toggle']]);
    $st = $cur->fetchColumn();
    $pdo->prepare("UPDATE services SET active=? WHERE id=?")->execute([$st?0:1,(int)$_GET['toggle']]);
    $success = 'تم تحديث حالة الخدمة';
}

$services = $pdo->query("SELECT * FROM services ORDER BY order_by ASC, id ASC")->fetchAll();
include __DIR__ . '/../includes/admin-header.php';
?>
<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

<div class="page-actions">
    <h3 style="font-size:16px;font-weight:700;color:var(--dark);"><?php echo count($services); ?> خدمة</h3>
    <a href="/admin/service-form.php" class="btn btn-gold"><i class="fas fa-plus"></i> إضافة خدمة جديدة</a>
</div>

<div class="admin-card">
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($services)): ?>
        <div class="empty-state"><i class="fas fa-concierge-bell"></i><p>لا توجد خدمات. ابدأ بإضافة خدمة جديدة.</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الأيقونة</th><th>الاسم</th><th>الوصف</th><th>الترتيب</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
            <tr>
                <td><div style="width:40px;height:40px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="fas <?php echo htmlspecialchars($s['icon']??'fa-building'); ?>" style="color:#fff;font-size:16px;"></i></div></td>
                <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#888;font-size:13px;"><?php echo htmlspecialchars($s['description']??''); ?></td>
                <td><?php echo $s['order_by']; ?></td>
                <td><a href="?toggle=<?php echo $s['id']; ?>" class="badge <?php echo $s['active']?'badge-green':'badge-red'; ?>" style="text-decoration:none;"><?php echo $s['active']?'مفعّل':'معطّل'; ?></a></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="/admin/service-form.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-red delete-btn"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
