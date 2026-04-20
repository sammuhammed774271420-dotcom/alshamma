<?php
$admin_title = 'قسم لماذا نحن';
$admin_icon = 'star';
require_once __DIR__ . '/../includes/admin-check.php';

$success = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM why_us_items WHERE id=?")->execute([(int)$_GET['delete']]);
    $success = 'تم حذف العنصر بنجاح';
}
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $cur = $pdo->prepare("SELECT active FROM why_us_items WHERE id=?");
    $cur->execute([(int)$_GET['toggle']]);
    $st = $cur->fetchColumn();
    $pdo->prepare("UPDATE why_us_items SET active=? WHERE id=?")->execute([$st?0:1, (int)$_GET['toggle']]);
    $success = 'تم تحديث الحالة';
}

$id = (int)($_GET['id'] ?? 0);
$item = ['title'=>'','description'=>'','icon'=>'fa-star','order_by'=>0,'active'=>1];
$editing = false;
if ($id) {
    $s = $pdo->prepare("SELECT * FROM why_us_items WHERE id=?");
    $s->execute([$id]);
    $found = $s->fetch();
    if ($found) { $item = $found; $editing = true; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = (int)($_POST['edit_id'] ?? 0);
    $title   = trim($_POST['title'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $icon    = trim($_POST['icon'] ?? 'fa-star');
    $order   = (int)($_POST['order_by'] ?? 0);
    $active  = (int)($_POST['active'] ?? 1);

    if (!empty($title)) {
        if ($post_id) {
            $pdo->prepare("UPDATE why_us_items SET title=?,description=?,icon=?,order_by=?,active=? WHERE id=?")
                ->execute([$title,$desc,$icon,$order,$active,$post_id]);
            $success = 'تم تحديث العنصر بنجاح';
            $id = 0; $editing = false;
            $item = ['title'=>'','description'=>'','icon'=>'fa-star','order_by'=>0,'active'=>1];
        } else {
            $pdo->prepare("INSERT INTO why_us_items (title,description,icon,order_by,active) VALUES (?,?,?,?,?)")
                ->execute([$title,$desc,$icon,$order,$active]);
            $success = 'تم إضافة العنصر بنجاح';
            $item = ['title'=>'','description'=>'','icon'=>'fa-star','order_by'=>0,'active'=>1];
        }
    }
}

$icons = ['fa-shield-alt','fa-award','fa-users','fa-headset','fa-star','fa-check-circle','fa-gem','fa-heart','fa-handshake','fa-chart-line','fa-building','fa-home','fa-key','fa-lock','fa-thumbs-up','fa-trophy','fa-medal','fa-crown'];
$items = $pdo->query("SELECT * FROM why_us_items ORDER BY order_by ASC, id ASC")->fetchAll();
include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;">

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-<?php echo $editing?'edit':'plus'; ?>"></i> <?php echo $editing?'تعديل عنصر':'إضافة عنصر جديد'; ?></h3>
        <?php if ($editing): ?><a href="/admin/why-us.php" class="btn btn-sm btn-dark"><i class="fas fa-times"></i></a><?php endif; ?>
    </div>
    <div class="admin-card-body">
        <form method="POST">
            <?php if ($editing): ?><input type="hidden" name="edit_id" value="<?php echo $id; ?>"><?php endif; ?>
            <div class="form-mb">
                <label class="form-label">العنوان *</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($item['title']); ?>" placeholder="مثال: الأمانة والمصداقية" required>
            </div>
            <div class="form-mb">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-control" rows="3" placeholder="وصف قصير..."><?php echo htmlspecialchars($item['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-mb">
                <label class="form-label">الأيقونة</label>
                <select name="icon" class="form-control" id="wiSelect">
                    <?php foreach ($icons as $ico): ?>
                    <option value="<?php echo $ico; ?>" <?php echo ($item['icon']==$ico)?'selected':''; ?>><?php echo $ico; ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="margin-top:8px;display:flex;align-items:center;gap:10px;">
                    <div id="wiIconPreview" style="width:44px;height:44px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas <?php echo htmlspecialchars($item['icon']); ?>" style="color:white;font-size:18px;"></i>
                    </div>
                    <span style="font-size:12px;color:#888;">معاينة</span>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-mb">
                    <label class="form-label">الترتيب</label>
                    <input type="number" name="order_by" class="form-control" value="<?php echo $item['order_by']; ?>" min="0">
                </div>
                <div class="form-mb">
                    <label class="form-label">الحالة</label>
                    <select name="active" class="form-control">
                        <option value="1" <?php echo $item['active']?'selected':''; ?>>ظاهر</option>
                        <option value="0" <?php echo !$item['active']?'selected':''; ?>>مخفي</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> <?php echo $editing?'تحديث':'إضافة'; ?></button>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-list"></i> العناصر الحالية (<?php echo count($items); ?>)</h3>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($items)): ?>
        <div class="empty-state"><i class="fas fa-star"></i><p>لا توجد عناصر. أضف أول عنصر.</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الأيقونة</th><th>العنوان</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
            <tr>
                <td><div style="width:38px;height:38px;background:var(--gold);border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="fas <?php echo htmlspecialchars($it['icon']); ?>" style="color:white;"></i></div></td>
                <td><strong><?php echo htmlspecialchars($it['title']); ?></strong><br><small style="color:#888;"><?php echo mb_substr(htmlspecialchars($it['description']??''),0,40); ?></small></td>
                <td><a href="?toggle=<?php echo $it['id']; ?>" class="badge <?php echo $it['active']?'badge-green':'badge-red'; ?>" style="text-decoration:none;"><?php echo $it['active']?'ظاهر':'مخفي'; ?></a></td>
                <td>
                    <div style="display:flex;gap:5px;">
                        <a href="?id=<?php echo $it['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-edit"></i></a>
                        <a href="?delete=<?php echo $it['id']; ?>" class="btn btn-sm btn-red delete-btn"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</div>

<script>
document.getElementById('wiSelect').addEventListener('change',function(){
    const i = document.querySelector('#wiIconPreview i');
    i.className='fas '+this.value;
});
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
