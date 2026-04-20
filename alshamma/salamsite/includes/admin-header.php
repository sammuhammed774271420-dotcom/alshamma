<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($admin_title) ? htmlspecialchars($admin_title) . ' - ' : ''; ?>لوحة التحكم - مخابز الشام</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body class="admin-body">

<div class="admin-overlay" id="adminOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-header">
        <span class="bread-icon-sidebar"><i class="fas fa-bread-slice"></i></span>
        <div style="color:#fff;font-size:16px;font-weight:900;font-family:'Cairo',sans-serif;">مخابز الشام</div>
        <div style="font-size:10px;color:#aaa;letter-spacing:1px;margin-top:2px;">Maamil Al Sham</div>
        <small style="display:block;margin-top:4px;color:#666;font-size:10.5px;">لوحة الإدارة</small>
    </div>
    <nav class="admin-nav">
        <?php $cur = basename($_SERVER['PHP_SELF']); ?>
        <a href="/admin/dashboard.php" class="<?php echo $cur=='dashboard.php'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>

        <div class="nav-divider">الموقع الإلكتروني</div>
        <a href="/admin/home-sections.php" class="<?php echo $cur=='home-sections.php'?'active':''; ?>"><i class="fas fa-toggle-on"></i> أقسام الصفحة الرئيسية</a>
        <a href="/admin/slider.php"        class="<?php echo $cur=='slider.php'?'active':''; ?>"><i class="fas fa-images"></i> الصور المتحركة</a>
        <a href="/admin/projects.php"      class="<?php echo in_array($cur,['projects.php','project-form.php'])?'active':''; ?>"><i class="fas fa-box-open"></i> إدارة المنتجات</a>
        <a href="/admin/services.php"      class="<?php echo in_array($cur,['services.php','service-form.php'])?'active':''; ?>"><i class="fas fa-concierge-bell"></i> الخدمات</a>
        <a href="/admin/offers.php"        class="<?php echo $cur=='offers.php'?'active':''; ?>"><i class="fas fa-tag"></i> العروض</a>
        <a href="/admin/why-us.php"        class="<?php echo $cur=='why-us.php'?'active':''; ?>"><i class="fas fa-star"></i> لماذا نحن</a>
        <a href="/admin/about.php"         class="<?php echo $cur=='about.php'?'active':''; ?>"><i class="fas fa-info-circle"></i> من نحن</a>
        <a href="/admin/contact-info.php"  class="<?php echo $cur=='contact-info.php'?'active':''; ?>"><i class="fas fa-map-marker-alt"></i> معلومات التواصل</a>
        <a href="/admin/messages.php"      class="<?php echo $cur=='messages.php'?'active':''; ?>"><i class="fas fa-envelope"></i> رسائل العملاء</a>

        <div class="nav-divider" style="background:rgba(201,162,39,0.1);color:var(--gold);">إدارة التوزيع</div>
        <a href="/admin/dist-customers.php" class="<?php echo in_array($cur,['dist-customers.php','dist-customer-form.php'])?'active':''; ?>"><i class="fas fa-store"></i> العملاء والمطاعم</a>
        <a href="/admin/dist-products.php"  class="<?php echo in_array($cur,['dist-products.php','dist-product-form.php'])?'active':''; ?>"><i class="fas fa-bread-slice"></i> المنتجات والأسعار</a>
        <a href="/admin/dist-receipts.php"  class="<?php echo in_array($cur,['dist-receipts.php','dist-receipt-form.php'])?'active':''; ?>"><i class="fas fa-file-invoice"></i> سندات التسليم</a>

        <div class="nav-divider" style="background:rgba(201,162,39,0.1);color:var(--gold);">الموارد البشرية</div>
        <a href="/admin/hr-employees.php"  class="<?php echo in_array($cur,['hr-employees.php','hr-employee-form.php'])?'active':''; ?>"><i class="fas fa-users"></i> إدارة الموظفين</a>
        <a href="/admin/hr-attendance.php" class="<?php echo $cur=='hr-attendance.php'?'active':''; ?>"><i class="fas fa-calendar-check"></i> الحضور والغياب</a>
        <a href="/admin/hr-salaries.php"   class="<?php echo $cur=='hr-salaries.php'?'active':''; ?>"><i class="fas fa-money-bill-wave"></i> الرواتب</a>
        <a href="/admin/hr-advances.php"   class="<?php echo $cur=='hr-advances.php'?'active':''; ?>"><i class="fas fa-hand-holding-usd"></i> السلف والاستقطاعات</a>
        <a href="/admin/hr-statements.php" class="<?php echo $cur=='hr-statements.php'?'active':''; ?>"><i class="fas fa-file-alt"></i> كشوف الحسابات</a>

        <div class="nav-divider">الإعدادات</div>
        <a href="/admin/settings.php" class="<?php echo $cur=='settings.php'?'active':''; ?>"><i class="fas fa-cog"></i> إعدادات الموقع</a>
        <a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </nav>
</aside>

<div class="admin-main">
<div class="admin-topbar">
    <div class="admin-topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" title="القائمة">
            <i class="fas fa-bars"></i>
        </button>
        <h2>
            <i class="fas fa-<?php echo $admin_icon ?? 'home'; ?>" style="color:var(--primary);margin-left:8px;"></i>
            <?php echo isset($admin_title) ? htmlspecialchars($admin_title) : 'لوحة التحكم'; ?>
        </h2>
    </div>
    <div class="admin-topbar-right">
        <a href="/index.php" class="view-site" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>عرض الموقع</span>
        </a>
        <span class="admin-user-badge">
            <i class="fas fa-user-circle" style="color:var(--primary);"></i>
            <?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'المشرف'); ?>
        </span>
    </div>
</div>
<div class="admin-content">
<?php
// Load JS at bottom via admin-footer
if (!function_exists('admin_js_loaded')) {
    function admin_js_loaded() { return true; }
}
?>
