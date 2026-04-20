<?php
require_once __DIR__ . '/includes/db.php';
$page_title   = 'التواصل';
$contact_info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
$social       = !empty($contact_info['social_links']) ? json_decode($contact_info['social_links'], true) : [];
$show = function($key) use ($contact_info) { return isset($contact_info[$key]) ? (bool)$contact_info[$key] : true; };

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($message)) {
        $error = 'يرجى ملء الاسم والرسالة على الأقل.';
    } else {
        $pdo->prepare("INSERT INTO contact_messages (name,email,phone,subject,message,contact_date,status) VALUES (?,?,?,?,?," . db_now() . ",'جديد')")
            ->execute([$name, $email, $phone, $subject, $message]);
        $success = 'تم إرسال رسالتك بنجاح! سنتواصل معك في أقرب وقت.';
    }
}

$preset_project = htmlspecialchars($_GET['project'] ?? '');
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <h1>تواصل <span>معنا</span></h1>
        <p>نحن هنا لمساعدتك، تواصل معنا في أي وقت</p>
        <div class="breadcrumb">
            <a href="/index.php">الرئيسية</a>
            <span>›</span>
            <span>التواصل</span>
        </div>
    </div>
</div>

<section class="contact-page">
    <div class="container">
        <div class="contact-grid">

            <!-- ── معلومات التواصل ── -->
            <div class="contact-info-box">
                <h3>معلومات التواصل</h3>

                <?php if ($show('show_address') && !empty($contact_info['address'])): ?>
                <div class="contact-item">
                    <div class="contact-item-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-item-text">
                        <h4>العنوان</h4>
                        <p><?php echo nl2br(htmlspecialchars($contact_info['address'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($show('show_phone') && !empty($contact_info['phone'])): ?>
                <div class="contact-item">
                    <div class="contact-item-icon"><i class="fas fa-phone"></i></div>
                    <div class="contact-item-text">
                        <h4>الهاتف</h4>
                        <a href="tel:<?php echo $contact_info['phone']; ?>"><?php echo htmlspecialchars($contact_info['phone']); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($show('show_whatsapp') && !empty($contact_info['whatsapp'])): ?>
                <div class="contact-item">
                    <div class="contact-item-icon" style="background:rgba(37,211,102,.15);color:#25D366;"><i class="fab fa-whatsapp"></i></div>
                    <div class="contact-item-text">
                        <h4>واتساب</h4>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/','', $contact_info['whatsapp']); ?>" target="_blank"><?php echo htmlspecialchars($contact_info['whatsapp']); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($show('show_email') && !empty($contact_info['email'])): ?>
                <div class="contact-item">
                    <div class="contact-item-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-item-text">
                        <h4>البريد الإلكتروني</h4>
                        <a href="mailto:<?php echo $contact_info['email']; ?>"><?php echo htmlspecialchars($contact_info['email']); ?></a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($show('show_hours') && !empty($contact_info['working_hours'])): ?>
                <div class="contact-item">
                    <div class="contact-item-icon"><i class="fas fa-clock"></i></div>
                    <div class="contact-item-text">
                        <h4>ساعات العمل</h4>
                        <p><?php echo htmlspecialchars($contact_info['working_hours']); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                $has_social = (
                    ($show('show_facebook')  && !empty($social['facebook']))  ||
                    ($show('show_twitter')   && !empty($social['twitter']))   ||
                    ($show('show_instagram') && !empty($social['instagram'])) ||
                    ($show('show_youtube')   && !empty($social['youtube']))   ||
                    ($show('show_linkedin')  && !empty($social['linkedin']))
                );
                if ($has_social): ?>
                <div class="contact-item" style="border-top:1px solid rgba(255,255,255,.1);margin-top:10px;padding-top:20px;">
                    <div class="contact-item-icon"><i class="fas fa-share-alt"></i></div>
                    <div class="contact-item-text">
                        <h4>تواصل اجتماعي</h4>
                        <div style="display:flex;gap:12px;margin-top:8px;flex-wrap:wrap;">
                            <?php if ($show('show_facebook') && !empty($social['facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($social['facebook']); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#1877F2;color:#fff;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;"><i class="fab fa-facebook"></i> فيسبوك</a>
                            <?php endif; ?>
                            <?php if ($show('show_twitter') && !empty($social['twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($social['twitter']); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#1DA1F2;color:#fff;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;"><i class="fab fa-twitter"></i> تويتر</a>
                            <?php endif; ?>
                            <?php if ($show('show_instagram') && !empty($social['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($social['instagram']); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);color:#fff;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;"><i class="fab fa-instagram"></i> انستقرام</a>
                            <?php endif; ?>
                            <?php if ($show('show_youtube') && !empty($social['youtube'])): ?>
                            <a href="<?php echo htmlspecialchars($social['youtube']); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#FF0000;color:#fff;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;"><i class="fab fa-youtube"></i> يوتيوب</a>
                            <?php endif; ?>
                            <?php if ($show('show_linkedin') && !empty($social['linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($social['linkedin']); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#0077B5;color:#fff;border-radius:20px;font-size:13px;font-weight:700;text-decoration:none;"><i class="fab fa-linkedin"></i> لينكدان</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── نموذج التواصل ── -->
            <div class="contact-form-box">
                <h3>أرسل لنا رسالة</h3>
                <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle" style="margin-left:8px;"></i><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle" style="margin-left:8px;"></i><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="" id="contactForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>الاسم الكامل *</label>
                            <input type="text" name="name" class="form-control" placeholder="اكتب اسمك" required>
                        </div>
                        <div class="form-group">
                            <label>رقم الجوال</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+966 5X XXX XXXX">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" placeholder="example@email.com">
                    </div>
                    <div class="form-group">
                        <label>موضوع الرسالة</label>
                        <input type="text" name="subject" class="form-control" value="<?php echo $preset_project ? 'استفسار عن مشروع: ' . $preset_project : ''; ?>" placeholder="موضوع رسالتك">
                    </div>
                    <div class="form-group">
                        <label>الرسالة *</label>
                        <textarea name="message" class="form-control" placeholder="اكتب رسالتك هنا..." required></textarea>
                    </div>
                    <button type="submit" class="btn-primary form-submit">
                        <i class="fas fa-paper-plane" style="margin-left:8px;"></i>إرسال الرسالة
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
