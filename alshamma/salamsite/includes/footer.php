<?php
if (!isset($pdo)) { require_once __DIR__ . '/db.php'; }
if (!isset($settings))     $settings     = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
if (!isset($contact_info)) $contact_info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
$footer_services = $pdo->query("SELECT name FROM services WHERE active=1 ORDER BY order_by LIMIT 5")->fetchAll();
$social = !empty($contact_info['social_links']) ? json_decode($contact_info['social_links'], true) : [];
$show = function($key) use ($contact_info) { return isset($contact_info[$key]) ? (bool)$contact_info[$key] : true; };
?>
<footer class="main-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo"><i class="fas fa-bread-slice"></i> <span>مخابز الشام</span></div>
                <div style="font-size:13px;color:#aaa;margin:3px 0 10px;">Maamil Al Sham - Arabic Bread</div>
                <p>جودة الخبز العربي الأصيل، نصنع لكم أشهى أنواع الخبز العربي بأعلى معايير الجودة والنظافة.</p>
                <div class="footer-social">
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

            <div class="footer-col">
                <h4>روابط سريعة</h4>
                <ul class="footer-links">
                    <li><a href="/index.php"><i class="fas fa-chevron-left"></i> الرئيسية</a></li>
                    <li><a href="/about.php"><i class="fas fa-chevron-left"></i> من نحن</a></li>
                    <li><a href="/projects.php"><i class="fas fa-chevron-left"></i> منتجاتنا</a></li>
                    <li><a href="/services.php"><i class="fas fa-chevron-left"></i> خدماتنا</a></li>
                    <li><a href="/contact.php"><i class="fas fa-chevron-left"></i> تواصل معنا</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>منتجاتنا</h4>
                <ul class="footer-links">
                    <?php foreach ($footer_services as $s): ?>
                    <li><a href="/services.php"><i class="fas fa-chevron-left"></i> <?php echo htmlspecialchars($s['name']); ?></a></li>
                    <?php endforeach; ?>
                    <?php if (empty($footer_services)): ?>
                    <li><a href="/projects.php"><i class="fas fa-chevron-left"></i> خبز عربي</a></li>
                    <li><a href="/projects.php"><i class="fas fa-chevron-left"></i> خبز تنور</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col">
                <h4>تواصل معنا</h4>
                <ul class="footer-contact">
                    <?php if ($show('show_address') && !empty($contact_info['address'])): ?>
                    <li><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($contact_info['address']); ?></li>
                    <?php endif; ?>
                    <?php if ($show('show_phone') && !empty($contact_info['phone'])): ?>
                    <li><i class="fas fa-phone"></i> <a href="tel:<?php echo $contact_info['phone']; ?>"><?php echo htmlspecialchars($contact_info['phone']); ?></a></li>
                    <?php endif; ?>
                    <?php if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])): ?>
                    <li><i class="fab fa-whatsapp" style="color:#25D366;"></i> <?php echo htmlspecialchars($contact_info['whatsapp']); ?></li>
                    <?php endif; ?>
                    <?php if ($show('show_email') && !empty($contact_info['email'])): ?>
                    <li><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact_info['email']); ?></li>
                    <?php endif; ?>
                    <?php if ($show('show_hours') && !empty($contact_info['working_hours'])): ?>
                    <li><i class="fas fa-clock"></i> <?php echo htmlspecialchars($contact_info['working_hours']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>© <?php echo date('Y'); ?> <span>مخابز الشام للخبز العربي</span> &mdash; جودة الخبز ... سر ثقة عملائنا &nbsp;🌾</p>
        </div>
    </div>
</footer>

<button class="scroll-top-btn" id="scrollTop" aria-label="العودة للأعلى">
    <i class="fas fa-chevron-up"></i>
</button>

<?php if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])): $waNum = preg_replace('/[^0-9]/', '', $contact_info['whatsapp']); ?>
<div class="whatsapp-float">
    <a href="https://wa.me/<?php echo $waNum; ?>" target="_blank" title="تواصل عبر واتساب">
        <i class="fab fa-whatsapp"></i>
    </a>
</div>
<?php endif; ?>

<script src="/assets/js/main.js"></script>
</body>
</html>
