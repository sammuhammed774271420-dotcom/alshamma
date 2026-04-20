<?php
if (!isset($pdo)) { require_once __DIR__ . '/db.php'; }
$settings     = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
$contact_info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
$current_page = basename($_SERVER['PHP_SELF']);
$social       = !empty($contact_info['social_links']) ? json_decode($contact_info['social_links'], true) : [];
$show = function($key) use ($contact_info) { return isset($contact_info[$key]) ? (bool)$contact_info[$key] : true; };
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مخابز الشام للخبز العربي<?php echo isset($page_title) ? ' - ' . htmlspecialchars($page_title) : ''; ?></title>
    <meta name="description" content="مخابز الشام للخبز العربي - جودة الخبز سر ثقة عملائنا">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <?php if (isset($extra_head)) echo $extra_head; ?>
</head>
<body>

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
            <?php if ($show('show_facebook') && !empty($social['facebook'])): ?>
            <a href="<?php echo htmlspecialchars($social['facebook']); ?>" target="_blank"><i class="fab fa-facebook"></i></a>
            <?php endif; ?>
            <?php if ($show('show_instagram') && !empty($social['instagram'])): ?>
            <a href="<?php echo htmlspecialchars($social['instagram']); ?>" target="_blank"><i class="fab fa-instagram"></i></a>
            <?php endif; ?>
            <?php if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])): ?>
            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','', $contact_info['whatsapp']); ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="main-header">
    <div class="container header-inner">
        <a href="/index.php" class="logo">
            <div class="logo-bakery-icon"><i class="fas fa-bread-slice"></i></div>
            <div class="logo-text">
                <span class="logo-ar">مخابز الشام للخبز العربي</span>
                <span class="logo-en">Maamil Al Sham - Arabic Bread</span>
            </div>
        </a>

        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="/index.php"    class="<?php echo $current_page=='index.php'   ?'active':''; ?>">الرئيسية</a></li>
                <li><a href="/about.php"    class="<?php echo $current_page=='about.php'   ?'active':''; ?>">من نحن</a></li>
                <li><a href="/projects.php" class="<?php echo $current_page=='projects.php'?'active':''; ?>">منتجاتنا</a></li>
                <li><a href="/services.php" class="<?php echo $current_page=='services.php'?'active':''; ?>">خدماتنا</a></li>
                <li><a href="/offers.php"   class="<?php echo $current_page=='offers.php'  ?'active':''; ?>">العروض</a></li>
                <li><a href="/contact.php"  class="<?php echo $current_page=='contact.php' ?'active':''; ?>">تواصل معنا</a></li>
            </ul>
        </nav>

        <a href="/contact.php" class="header-cta"><i class="fas fa-phone-alt"></i> اتصل بنا</a>
    </div>
    <div class="nav-overlay" id="navOverlay"></div>
</header>
<style>
.logo-bakery-icon {
    width: 55px; height: 55px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(204,32,32,0.3);
}
</style>
