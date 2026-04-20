<?php
$admin_title = 'رسائل التواصل';
$admin_icon = 'envelope';
require_once __DIR__ . '/../includes/admin-check.php';

$success = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $pdo->prepare("DELETE FROM contact_messages WHERE id=?")->execute([(int)$_GET['delete']]);
    $success = 'تم حذف الرسالة بنجاح';
}
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $allowed = ['جديد','قيد المراجعة','تمت الإجابة'];
    if (in_array($_GET['status'], $allowed)) {
        $pdo->prepare("UPDATE contact_messages SET status=? WHERE id=?")->execute([$_GET['status'],(int)$_GET['id']]);
        $success = 'تم تحديث حالة الرسالة';
    }
}

$view_id = (int)($_GET['view'] ?? 0);
$view_msg = null;
if ($view_id) {
    $s = $pdo->prepare("SELECT * FROM contact_messages WHERE id=?");
    $s->execute([$view_id]);
    $view_msg = $s->fetch();
    if ($view_msg && $view_msg['status'] === 'جديد') {
        $pdo->prepare("UPDATE contact_messages SET status='قيد المراجعة' WHERE id=?")->execute([$view_id]);
        $view_msg['status'] = 'قيد المراجعة';
    }
}

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY contact_date DESC")->fetchAll();
include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

<?php if ($view_msg): ?>
<div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card-header">
        <h3><i class="fas fa-envelope-open"></i> تفاصيل الرسالة</h3>
        <a href="/admin/messages.php" class="btn btn-sm btn-dark"><i class="fas fa-times"></i> إغلاق</a>
    </div>
    <div class="admin-card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:20px;background:#fafaf8;border-radius:6px;padding:18px;margin-bottom:20px;">
            <div><span style="font-size:12px;color:#888;display:block;margin-bottom:4px;">الاسم</span><strong><?php echo htmlspecialchars($view_msg['name']); ?></strong></div>
            <div><span style="font-size:12px;color:#888;display:block;margin-bottom:4px;">البريد</span><a href="mailto:<?php echo $view_msg['email']; ?>" style="color:var(--gold);"><?php echo htmlspecialchars($view_msg['email']??'-'); ?></a></div>
            <div><span style="font-size:12px;color:#888;display:block;margin-bottom:4px;">الهاتف</span><?php echo htmlspecialchars($view_msg['phone']??'-'); ?></div>
            <div><span style="font-size:12px;color:#888;display:block;margin-bottom:4px;">التاريخ</span><?php echo substr($view_msg['contact_date'],0,16); ?></div>
        </div>
        <div style="margin-bottom:15px;"><strong style="font-size:15px;"><?php echo htmlspecialchars($view_msg['subject']??'(بدون موضوع)'); ?></strong></div>
        <div style="background:#fff;border:1px solid #eee;border-radius:6px;padding:20px;font-size:15px;line-height:1.9;color:#444;"><?php echo nl2br(htmlspecialchars($view_msg['message']??'')); ?></div>
        <div style="display:flex;gap:10px;margin-top:15px;">
            <a href="?id=<?php echo $view_msg['id']; ?>&status=تمت الإجابة" class="btn btn-green"><i class="fas fa-check"></i> تمت الإجابة</a>
            <a href="?id=<?php echo $view_msg['id']; ?>&status=قيد المراجعة" class="btn btn-blue"><i class="fas fa-clock"></i> قيد المراجعة</a>
            <?php if (!empty($view_msg['email'])): ?><a href="mailto:<?php echo $view_msg['email']; ?>" class="btn btn-gold"><i class="fas fa-reply"></i> رد عبر البريد</a><?php endif; ?>
            <a href="?delete=<?php echo $view_msg['id']; ?>" class="btn btn-red delete-btn"><i class="fas fa-trash"></i> حذف</a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-list"></i> جميع الرسائل (<?php echo count($messages); ?>)</h3>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($messages)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><p>لا توجد رسائل حتى الآن</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الاسم</th><th>البريد / الهاتف</th><th>الموضوع</th><th>التاريخ</th><th>الحالة</th><th>الإجراءات</th></tr></thead>
            <tbody>
            <?php foreach ($messages as $m): ?>
            <tr style="<?php echo $m['status']==='جديد'?'font-weight:700;':''; ?>">
                <td><?php echo htmlspecialchars($m['name']); ?></td>
                <td style="font-size:12px;"><?php echo htmlspecialchars($m['email']??''); ?><br><?php echo htmlspecialchars($m['phone']??''); ?></td>
                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($m['subject']??'(بدون موضوع)'); ?></td>
                <td style="font-size:12px;white-space:nowrap;"><?php echo substr($m['contact_date'],0,16); ?></td>
                <td>
                    <span class="badge <?php 
                        echo $m['status']==='جديد' ? 'badge-red' : ($m['status']==='تمت الإجابة' ? 'badge-green' : 'badge-blue'); 
                    ?>"><?php echo htmlspecialchars($m['status']); ?></span>
                </td>
                <td>
                    <div style="display:flex;gap:5px;">
                        <a href="?view=<?php echo $m['id']; ?>" class="btn btn-sm btn-gold"><i class="fas fa-eye"></i></a>
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
