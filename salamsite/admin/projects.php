<?php
$admin_title = 'إدارة المشاريع';
$admin_icon = 'building';
require_once __DIR__ . '/../includes/admin-check.php';

$success = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pr = $pdo->prepare("SELECT image_path FROM projects WHERE id=?");
    $pr->execute([(int)$_GET['delete']]);
    $p = $pr->fetch();
    if ($p && !empty($p['image_path']) && file_exists(__DIR__ . '/../' . $p['image_path'])) unlink(__DIR__ . '/../' . $p['image_path']);
    $pdo->prepare("DELETE FROM projects WHERE id=?")->execute([(int)$_GET['delete']]);
    $success = 'تم حذف المشروع بنجاح';
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
include __DIR__ . '/../includes/admin-header.php';
?>
<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

<div class="page-actions">
    <h3 style="font-size:16px;font-weight:700;color:var(--dark);"><?php echo count($projects); ?> مشروع</h3>
    <a href="/admin/project-form.php" class="btn btn-gold"><i class="fas fa-plus"></i> إضافة مشروع جديد</a>
</div>

<div class="admin-card">
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($projects)): ?>
        <div class="empty-state"><i class="fas fa-building"></i><p>لا توجد مشاريع. ابدأ بإضافة مشروع جديد.</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الصورة</th><th>الاسم</th><th>الموقع</th><th>الحالة</th><th>السعر</th><th>مميز</th><th>الإجراءات</th></tr></thead>
            <tbody>
            <?php foreach ($projects as $p): ?>
            <tr>
                <td><?php if (!empty($p['image_path']) && file_exists(__DIR__.'/../'.$p['image_path'])): ?><img src="/<?php echo htmlspecialchars($p['image_path']); ?>" alt=""><?php else: ?><div style="width:50px;height:40px;background:#eee;border-radius:4px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-building" style="color:#ccc;"></i></div><?php endif; ?></td>
                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($p['location'] ?? '-'); ?></td>
                <td><span class="badge badge-gold"><?php echo htmlspecialchars($p['status']); ?></span></td>
                <td><?php echo htmlspecialchars($p['price'] ?? '-'); ?></td>
                <td><?php echo $p['featured'] ? '<span class="badge badge-green">نعم</span>' : '<span class="badge" style="background:#eee;color:#888;">لا</span>'; ?></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="/admin/project-form.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i> تعديل</a>
                        <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-red delete-btn"><i class="fas fa-trash"></i></a>
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
