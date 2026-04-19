<?php
if (!isset($pdo)) { require_once __DIR__ . '/db.php'; }
$settings     = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
$contact_info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
$current_page = basename($_SERVER['PHP_SELF']);
$social       = !empty($contact_info['social_links']) ? json_decode($contact_info['social_links'], true) : [];
// Visibility helper
$show = function($key) use ($contact_info) { return isset($contact_info[$key]) ? (bool)$contact_info[$key] : true; };
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['site_name'] ?? 'السلام للعقارات'); ?><?php echo isset($page_title) ? ' - ' . htmlspecialchars($page_title) : ''; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['meta_description'] ?? 'شركة السلام للعقارات - نحقق أحلامك العقارية'); ?>">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body>

<!-- ── Top Bar ─────────────────────────────────────────── -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-left">
            <?php if ($show('show_phone') && !empty($contact_info['phone'])): ?>
            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact_info['phone']); ?></span>
            <?php endif; ?>
            <?php if ($show('show_email') && !empty($contact_info['email'])): ?>
            <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact_info['email']); ?></span>
            <?php endif; ?>
        </div>
        <div class="top-bar-right">
            <?php if ($show('show_twitter') && !empty($social['twitter'])): ?>
            <a href="<?php echo htmlspecialchars($social['twitter']); ?>" target="_blank" title="تويتر"><i class="fab fa-twitter"></i></a>
            <?php endif; ?>
            <?php if ($show('show_facebook') && !empty($social['facebook'])): ?>
            <a href="<?php echo htmlspecialchars($social['facebook']); ?>" target="_blank" title="فيسبوك"><i class="fab fa-facebook"></i></a>
            <?php endif; ?>
            <?php if ($show('show_instagram') && !empty($social['instagram'])): ?>
            <a href="<?php echo htmlspecialchars($social['instagram']); ?>" target="_blank" title="انستقرام"><i class="fab fa-instagram"></i></a>
            <?php endif; ?>
            <?php if ($show('show_youtube') && !empty($social['youtube'])): ?>
            <a href="<?php echo htmlspecialchars($social['youtube']); ?>" target="_blank" title="يوتيوب"><i class="fab fa-youtube"></i></a>
            <?php endif; ?>
            <?php if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])): ?>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','', $contact_info['whatsapp']); ?>" target="_blank" title="واتساب"><i class="fab fa-whatsapp"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Main Header ─────────────────────────────────────── -->
<header class="main-header">
    <div class="container header-inner">
        <a href="/index.php" class="logo" style="animation: fadeInDown 0.8s ease-out;">
            <?php if (!empty($settings['logo_path']) && file_exists(__DIR__ . '/../' . $settings['logo_path'])): ?>
                <img src="/<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="<?php echo htmlspecialchars($settings['site_name']); ?>">
            <?php else: ?>
                <div class="logo-text">
                    <span class="logo-ar"><?php echo htmlspecialchars($settings['site_name'] ?? 'السلام للعقارات'); ?></span>
                    <span class="logo-en">AL-SALAM REAL ESTATE</span>
                </div>
            <?php endif; ?>
        </a>

        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>

        <nav class="main-nav" id="mainNav" style="animation: fadeInDown 1s ease-out;">
            <ul>
                <li><a href="/index.php"    class="<?php echo $current_page=='index.php'   ?'active':''; ?>">الرئيسية</a></li>
                <li><a href="/about.php"    class="<?php echo $current_page=='about.php'   ?'active':''; ?>">من نحن</a></li>
                <li><a href="/projects.php" class="<?php echo $current_page=='projects.php'?'active':''; ?>">الأعمال</a></li>
                <li><a href="/services.php" class="<?php echo $current_page=='services.php'?'active':''; ?>">الخدمات</a></li>
                <li><a href="/offers.php"   class="<?php echo $current_page=='offers.php'  ?'active':''; ?>">العروض</a></li>
                <li><a href="/contact.php"  class="<?php echo $current_page=='contact.php' ?'active':''; ?>">التواصل</a></li>
            </ul>
        </nav>

        <a href="/contact.php" class="header-cta">تواصل معنا</a>
    </div>
</header>
