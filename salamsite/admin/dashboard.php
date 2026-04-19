<?php
$admin_title = 'لوحة التحكم';
$admin_icon = 'tachometer-alt';
require_once __DIR__ . '/../includes/admin-check.php';

$cnt_projects  = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$cnt_services  = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$cnt_team      = $pdo->query("SELECT COUNT(*) FROM team")->fetchColumn();
$cnt_messages  = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='جديد'")->fetchColumn();
$cnt_sliders   = $pdo->query("SELECT COUNT(*) FROM slider_images WHERE active=1")->fetchColumn();
$cnt_offers    = $pdo->query("SELECT COUNT(*) FROM offers WHERE active=1")->fetchColumn();

// Additional stats
$total_messages = $pdo->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
$featured_count = $pdo->query("SELECT COUNT(*) FROM projects WHERE featured=1")->fetchColumn();

$recent_msg = $pdo->query("SELECT * FROM contact_messages ORDER BY contact_date DESC LIMIT 5")->fetchAll();
$recent_projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC LIMIT 5")->fetchAll();
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-icon red"><i class="fas fa-envelope"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_messages; ?></strong><span>رسائل جديدة</span></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon blue"><i class="fas fa-building"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_projects; ?></strong><span>إجمالي المشاريع</span></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon gold"><i class="fas fa-star"></i></div>
        <div class="admin-stat-info"><strong><?php echo $featured_count; ?></strong><span>مشاريع مميزة</span></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon green"><i class="fas fa-concierge-bell"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_services; ?></strong><span>الخدمات</span></div>
    </div>
    <div class="admin-stat-card">
        <div class="admin-stat-icon purple"><i class="fas fa-tag"></i></div>
        <div class="admin-stat-info"><strong><?php echo $cnt_offers; ?></strong><span>العروض النشطة</span></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:25px;">

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-envelope"></i> أحدث الرسائل الواردة</h3>
        <a href="/admin/messages.php" class="btn btn-sm btn-gold">إدارة الرسائل</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($recent_msg)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><p>لا توجد رسائل حالياً</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الاسم</th><th>الموضوع</th><th>التاريخ</th><th>الحالة</th></tr></thead>
            <tbody>
            <?php foreach ($recent_msg as $m): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($m['name']); ?></strong><br><small style="color:#888;"><?php echo htmlspecialchars($m['phone'] ?? ''); ?></small></td>
                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($m['subject'] ?? 'بدون موضوع'); ?></td>
                <td style="font-size:12px;color:#888;"><?php echo date('Y/m/d H:i', strtotime($m['contact_date'])); ?></td>
                <td><span class="badge <?php echo $m['status']=='جديد'?'badge-red':'badge-green'; ?>"><?php echo htmlspecialchars($m['status']); ?></span></td>
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
        <div style="display:flex;flex-direction:column;gap:12px;">
            <a href="/admin/project-form.php" class="btn btn-gold" style="justify-content:center;"><i class="fas fa-plus"></i> إضافة مشروع جديد</a>
            <a href="/admin/slider.php?action=add" class="btn btn-dark" style="justify-content:center;"><i class="fas fa-image"></i> إضافة عرض سلايدر</a>
            <a href="/admin/service-form.php" class="btn btn-blue" style="justify-content:center;"><i class="fas fa-plus-circle"></i> إضافة خدمة</a>
            <a href="/admin/settings.php" class="btn btn-dark" style="background:#555;justify-content:center;"><i class="fas fa-cog"></i> إعدادات الموقع</a>
        </div>
        
        <div style="margin-top:25px;padding-top:20px;border-top:1px solid #eee;">
            <h4 style="font-size:14px;margin-bottom:15px;color:#666;">ملخص عام</h4>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
                <span>إجمالي الرسائل:</span>
                <span style="font-weight:700;"><?php echo $total_messages; ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
                <span>أعضاء الفريق:</span>
                <span style="font-weight:700;"><?php echo $cnt_team; ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
                <span>صور السلايدر النشطة:</span>
                <span style="font-weight:700;"><?php echo $cnt_sliders; ?></span>
            </div>
        </div>
    </div>
</div>

</div>

<div class="admin-card" style="margin-top:5px;">
    <div class="admin-card-header">
        <h3><i class="fas fa-building"></i> آخر المشاريع المضافة</h3>
        <a href="/admin/projects.php" class="btn btn-sm btn-dark">عرض كل المشاريع</a>
    </div>
    <div class="admin-card-body" style="padding:0;">
        <?php if (empty($recent_projects)): ?>
        <div class="empty-state"><i class="fas fa-building"></i><p>لا توجد مشاريع مضافة</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>الصورة</th><th>اسم المشروع</th><th>الموقع</th><th>الحالة</th><th>مميز</th></tr></thead>
            <tbody>
            <?php foreach ($recent_projects as $p): ?>
            <tr>
                <td><?php if (!empty($p['image_path'])): ?><img src="/<?php echo htmlspecialchars($p['image_path']); ?>" alt=""><?php else: ?><div style="width:50px;height:40px;background:#eee;border-radius:4px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:#ccc;"></i></div><?php endif; ?></td>
                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($p['location'] ?? '-'); ?></td>
                <td><span class="badge badge-gold"><?php echo htmlspecialchars($p['status']); ?></span></td>
                <td><?php echo $p['featured'] ? '<i class="fas fa-star" style="color:var(--gold);"></i>' : '-'; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
