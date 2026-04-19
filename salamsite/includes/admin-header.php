<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($admin_title) ? htmlspecialchars($admin_title) . ' - ' : ''; ?>لوحة التحكم - السلام للعقارات</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <div class="logo-ar">السلام للعقارات</div>
        <div class="logo-en">AL-SALAM REAL ESTATE</div>
        <small style="display:block;margin-top:8px;color:#888;">لوحة الإدارة</small>
    </div>
    <nav class="admin-nav">
        <?php $cur = basename($_SERVER['PHP_SELF']); ?>
        <a href="/admin/dashboard.php" class="<?php echo $cur=='dashboard.php'?'active':''; ?>"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>
        
        <div class="nav-divider">الصفحة الرئيسية</div>
        <a href="/admin/home-sections.php" class="<?php echo $cur=='home-sections.php'?'active':''; ?>"><i class="fas fa-toggle-on"></i> إظهار/إخفاء الأقسام</a>
        <a href="/admin/slider.php" class="<?php echo $cur=='slider.php'?'active':''; ?>"><i class="fas fa-images"></i> صور السلايدر</a>
        <a href="/admin/why-us.php" class="<?php echo $cur=='why-us.php'?'active':''; ?>"><i class="fas fa-star"></i> قسم لماذا نحن</a>
        <a href="/admin/settings.php#cta" class="<?php echo ($cur=='settings.php')?'active':''; ?>"><i class="fas fa-bullhorn"></i> قسم الدعوة (CTA)</a>

        <div class="nav-divider">المحتوى</div>
        <a href="/admin/projects.php" class="<?php echo in_array($cur,['projects.php','project-form.php'])?'active':''; ?>"><i class="fas fa-building"></i> إدارة المشاريع</a>
        <a href="/admin/services.php" class="<?php echo in_array($cur,['services.php','service-form.php'])?'active':''; ?>"><i class="fas fa-concierge-bell"></i> إدارة الخدمات</a>
        <a href="/admin/offers.php" class="<?php echo $cur=='offers.php'?'active':''; ?>"><i class="fas fa-tag"></i> إدارة العروض</a>
        <a href="/admin/team.php" class="<?php echo in_array($cur,['team.php','team-form.php'])?'active':''; ?>"><i class="fas fa-users"></i> إدارة الفريق</a>
        
        <div class="nav-divider">صفحات الموقع</div>
        <a href="/admin/about.php" class="<?php echo $cur=='about.php'?'active':''; ?>"><i class="fas fa-info-circle"></i> صفحة من نحن</a>
        <a href="/admin/contact-info.php" class="<?php echo $cur=='contact-info.php'?'active':''; ?>"><i class="fas fa-map-marker-alt"></i> معلومات التواصل</a>
        <a href="/admin/messages.php" class="<?php echo $cur=='messages.php'?'active':''; ?>"><i class="fas fa-envelope"></i> رسائل التواصل</a>
        
        <div class="nav-divider">الإعدادات</div>
        <a href="/admin/settings.php" class="<?php echo $cur=='settings.php'?'active':''; ?>"><i class="fas fa-cog"></i> إعدادات الموقع</a>
        <a href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </nav>
</aside>

<div class="admin-main">
<div class="admin-topbar">
    <h2><i class="fas fa-<?php echo $admin_icon ?? 'home'; ?>" style="color:var(--gold);margin-left:8px;"></i><?php echo isset($admin_title) ? htmlspecialchars($admin_title) : 'لوحة التحكم'; ?></h2>
    <div class="admin-topbar-right">
        <a href="/index.php" class="view-site" target="_blank"><i class="fas fa-external-link-alt"></i> عرض الموقع</a>
        <span style="font-size:13px;color:#888;">مرحباً، <?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'المشرف'); ?></span>
    </div>
</div>
<div class="admin-content">
