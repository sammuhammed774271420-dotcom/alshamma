<?php
$admin_title = 'أقسام الصفحة الرئيسية';
$admin_icon = 'toggle-on';
require_once __DIR__ . '/../includes/admin-check.php';

$success = '';

// Toggle section
if (isset($_GET['toggle']) && !empty($_GET['toggle'])) {
    $key = $_GET['toggle'];
    $cur = $pdo->prepare("SELECT active FROM home_sections WHERE section_key=?");
    $cur->execute([$key]);
    $st = $cur->fetchColumn();
    $pdo->prepare("UPDATE home_sections SET active=? WHERE section_key=?")->execute([$st ? 0 : 1, $key]);
    $success = 'تم تحديث حالة القسم';
}

// Reorder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder'])) {
    foreach ($_POST['reorder'] as $key => $val) {
        // no ordering in this table, just active/inactive
    }
}

$sections = $pdo->query("SELECT * FROM home_sections ORDER BY id ASC")->fetchAll();
include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-toggle-on"></i> التحكم في أقسام الصفحة الرئيسية</h3>
        <a href="/index.php" class="btn btn-sm btn-gold" target="_blank"><i class="fas fa-external-link-alt"></i> معاينة الصفحة الرئيسية</a>
    </div>
    <div class="admin-card-body">
        <p style="color:#888;font-size:14px;margin-bottom:20px;">يمكنك إظهار أو إخفاء أي قسم من أقسام الصفحة الرئيسية بالضغط على زر التفعيل.</p>
        <div style="display:grid;gap:12px;">
            <?php foreach ($sections as $s): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;background:<?php echo $s['active']?'#fafff8':'#fff8f8'; ?>;border:2px solid <?php echo $s['active']?'#28a74530':'#dc354530'; ?>;border-radius:8px;transition:all .2s;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div style="width:44px;height:44px;background:<?php echo $s['active']?'#28a745':'#dc3545'; ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <?php 
                        $icons = ['slider'=>'fa-images','stats'=>'fa-chart-bar','services'=>'fa-concierge-bell','projects'=>'fa-building','about'=>'fa-info-circle','whyus'=>'fa-star','cta'=>'fa-bullhorn'];
                        $ico = $icons[$s['section_key']] ?? 'fa-layer-group';
                        ?>
                        <i class="fas <?php echo $ico; ?>" style="color:white;font-size:18px;"></i>
                    </div>
                    <div>
                        <strong style="font-size:15px;color:#333;"><?php echo htmlspecialchars($s['section_name']); ?></strong>
                        <span style="display:block;font-size:12px;color:#888;margin-top:2px;">مفتاح القسم: <code><?php echo $s['section_key']; ?></code></span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <span class="badge <?php echo $s['active']?'badge-green':'badge-red'; ?>" style="font-size:13px;padding:5px 14px;"><?php echo $s['active']?'ظاهر':'مخفي'; ?></span>
                    <a href="?toggle=<?php echo urlencode($s['section_key']); ?>" 
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:<?php echo $s['active']?'#dc3545':'#28a745'; ?>;color:white;border-radius:6px;text-decoration:none;font-size:13px;font-weight:700;">
                        <i class="fas <?php echo $s['active']?'fa-eye-slash':'fa-eye'; ?>"></i>
                        <?php echo $s['active']?'إخفاء':'إظهار'; ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="margin-top:25px;padding:16px;background:#fffbf0;border:1px solid #f0c040;border-radius:6px;">
            <p style="margin:0;font-size:13px;color:#856404;"><i class="fas fa-lightbulb" style="margin-left:6px;"></i>
            <strong>ملاحظة:</strong> إخفاء قسم لا يحذف بياناته، بل يوقف ظهوره في الصفحة الرئيسية فقط.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
