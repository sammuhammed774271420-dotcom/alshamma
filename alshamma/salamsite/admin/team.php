<?php
$admin_title = 'إدارة الفريق';
$admin_icon = 'users';
require_once __DIR__ . '/../includes/admin-check.php';

$success = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $tm = $pdo->prepare("SELECT image_path FROM team WHERE id=?");
    $tm->execute([(int)$_GET['delete']]);
    $t = $tm->fetch();
    if ($t && !empty($t['image_path']) && file_exists(__DIR__ . '/../' . $t['image_path'])) unlink(__DIR__ . '/../' . $t['image_path']);
    $pdo->prepare("DELETE FROM team WHERE id=?")->execute([(int)$_GET['delete']]);
    $success = 'تم حذف عضو الفريق بنجاح';
}

$team = $pdo->query("SELECT * FROM team ORDER BY order_by ASC, id ASC")->fetchAll();
include __DIR__ . '/../includes/admin-header.php';
?>
<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

<div class="page-actions">
    <h3 style="font-size:16px;font-weight:700;color:var(--dark);"><?php echo count($team); ?> عضو في الفريق</h3>
    <a href="/admin/team-form.php" class="btn btn-gold"><i class="fas fa-plus"></i> إضافة عضو جديد</a>
</div>

<div class="admin-card">
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($team)): ?>
        <div class="empty-state"><i class="fas fa-users"></i><p>لا يوجد أعضاء. ابدأ بإضافة عضو جديد.</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الصورة</th><th>الاسم</th><th>المنصب</th><th>البريد</th><th>الهاتف</th><th>الترتيب</th><th>الإجراءات</th></tr></thead>
            <tbody>
            <?php foreach ($team as $m): ?>
            <tr>
                <td><?php if (!empty($m['image_path']) && file_exists(__DIR__.'/../'.$m['image_path'])): ?><img src="/<?php echo htmlspecialchars($m['image_path']); ?>" alt="" style="width:45px;height:45px;border-radius:50%;object-fit:cover;"><?php else: ?><div style="width:45px;height:45px;background:#eee;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user" style="color:#ccc;"></i></div><?php endif; ?></td>
                <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($m['position'] ?? '-'); ?></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($m['email'] ?? '-'); ?></td>
                <td style="font-size:13px;"><?php echo htmlspecialchars($m['phone'] ?? '-'); ?></td>
                <td><?php echo $m['order_by']; ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="/admin/team-form.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?php echo $m['id']; ?>" class="btn btn-sm btn-red delete-btn"><i class="fas fa-trash"></i></a>
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
