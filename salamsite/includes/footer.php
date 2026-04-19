<?php
if (!isset($pdo)) { require_once __DIR__ . '/db.php'; }
if (!isset($settings))     $settings     = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
if (!isset($contact_info)) $contact_info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
$footer_services = $pdo->query("SELECT name FROM services WHERE active=1 ORDER BY order_by LIMIT 5")->fetchAll();
$social = !empty($contact_info['social_links']) ? json_decode($contact_info['social_links'], true) : [];
$show = function($key) use ($contact_info) { return isset($contact_info[$key]) ? (bool)$contact_info[$key] : true; };
?>
<footer class="main-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">

                <!-- ── عمود الشعار والسوشيال ── -->
                <div class="footer-col">
                    <div class="footer-logo">
                        <span class="logo-ar"><?php echo htmlspecialchars($settings['site_name'] ?? 'السلام للعقارات'); ?></span>
                        <span class="logo-en">AL-SALAM REAL ESTATE</span>
                    </div>
                    <p class="footer-about"><?php echo htmlspecialchars($settings['tagline'] ?? 'نحقق أحلامك العقارية'); ?>. شركة متخصصة في مجال التطوير العقاري والاستثمار.</p>
                    <div class="footer-social">
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
                        <?php if ($show('show_linkedin') && !empty($social['linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($social['linkedin']); ?>" target="_blank" title="لينكدان"><i class="fab fa-linkedin"></i></a>
                        <?php endif; ?>
                        <?php if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','', $contact_info['whatsapp']); ?>" target="_blank" title="واتساب"><i class="fab fa-whatsapp"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ── روابط سريعة ── -->
                <div class="footer-col">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="/index.php"><i class="fas fa-chevron-left"></i> الرئيسية</a></li>
                        <li><a href="/about.php"><i class="fas fa-chevron-left"></i> من نحن</a></li>
                        <li><a href="/projects.php"><i class="fas fa-chevron-left"></i> الأعمال</a></li>
                        <li><a href="/services.php"><i class="fas fa-chevron-left"></i> الخدمات</a></li>
                        <li><a href="/contact.php"><i class="fas fa-chevron-left"></i> التواصل</a></li>
                    </ul>
                </div>

                <!-- ── الخدمات ── -->
                <div class="footer-col">
                    <h4>خدماتنا</h4>
                    <ul>
                        <?php foreach ($footer_services as $s): ?>
                        <li><a href="/services.php"><i class="fas fa-chevron-left"></i> <?php echo htmlspecialchars($s['name']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- ── تواصل معنا ── -->
                <div class="footer-col">
                    <h4>تواصل معنا</h4>
                    <ul class="contact-list">
                        <?php if ($show('show_address') && !empty($contact_info['address'])): ?>
                        <li><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($contact_info['address']); ?></li>
                        <?php endif; ?>
                        <?php if ($show('show_phone') && !empty($contact_info['phone'])): ?>
                        <li><i class="fas fa-phone"></i> <a href="tel:<?php echo $contact_info['phone']; ?>"><?php echo htmlspecialchars($contact_info['phone']); ?></a></li>
                        <?php endif; ?>
                        <?php if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])): ?>
                        <li><i class="fab fa-whatsapp" style="color:#25D366;"></i> <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','', $contact_info['whatsapp']); ?>" target="_blank"><?php echo htmlspecialchars($contact_info['whatsapp']); ?></a></li>
                        <?php endif; ?>
                        <?php if ($show('show_email') && !empty($contact_info['email'])): ?>
                        <li><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $contact_info['email']; ?>"><?php echo htmlspecialchars($contact_info['email']); ?></a></li>
                        <?php endif; ?>
                        <?php if ($show('show_hours') && !empty($contact_info['working_hours'])): ?>
                        <li><i class="fas fa-clock"></i> <?php echo htmlspecialchars($contact_info['working_hours']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_name'] ?? 'السلام للعقارات'); ?>. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</footer>

<div class="scroll-to-top" id="scrollTop">
    <i class="fas fa-chevron-up"></i>
</div>

<?php
// Floating WhatsApp button (only if whatsapp is enabled)
if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])):
    $waNum = preg_replace('/[^0-9]/', '', $contact_info['whatsapp']);
?>
<a href="https://wa.me/<?php echo $waNum; ?>" target="_blank" class="whatsapp-float" title="تواصل عبر واتساب">
    <i class="fab fa-whatsapp"></i>
</a>
<?php endif; ?>

<script src="/assets/js/main.js"></script>
</body>
</html>
