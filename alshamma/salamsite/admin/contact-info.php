<?php
$admin_title = 'معلومات التواصل';
$admin_icon = 'map-marker-alt';
require_once __DIR__ . '/../includes/admin-check.php';

$info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
$success = $error = '';

// Quick toggle via GET
if (isset($_GET['toggle']) && !empty($_GET['toggle'])) {
    $col = $_GET['toggle'];
    $allowed = ['show_phone','show_whatsapp','show_email','show_address','show_hours','show_facebook','show_twitter','show_instagram','show_youtube','show_linkedin'];
    if (in_array($col, $allowed) && $info) {
        $cur = (int)($info[$col] ?? 1);
        $pdo->prepare("UPDATE contact_info SET {$col}=? WHERE id=?")->execute([$cur ? 0 : 1, $info['id']]);
        $info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
        $success = 'تم تحديث الإعداد بنجاح';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address  = trim($_POST['address'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $hours    = trim($_POST['working_hours'] ?? '');
    $social   = json_encode([
        'facebook'  => trim($_POST['facebook'] ?? ''),
        'twitter'   => trim($_POST['twitter'] ?? ''),
        'instagram' => trim($_POST['instagram'] ?? ''),
        'youtube'   => trim($_POST['youtube'] ?? ''),
        'linkedin'  => trim($_POST['linkedin'] ?? ''),
    ]);

    // Visibility checkboxes
    $show_phone    = isset($_POST['show_phone'])    ? 1 : 0;
    $show_whatsapp = isset($_POST['show_whatsapp']) ? 1 : 0;
    $show_email    = isset($_POST['show_email'])    ? 1 : 0;
    $show_address  = isset($_POST['show_address'])  ? 1 : 0;
    $show_hours    = isset($_POST['show_hours'])    ? 1 : 0;
    $show_facebook = isset($_POST['show_facebook']) ? 1 : 0;
    $show_twitter  = isset($_POST['show_twitter'])  ? 1 : 0;
    $show_instagram= isset($_POST['show_instagram'])? 1 : 0;
    $show_youtube  = isset($_POST['show_youtube'])  ? 1 : 0;
    $show_linkedin = isset($_POST['show_linkedin']) ? 1 : 0;

    if ($info) {
        $pdo->prepare("UPDATE contact_info SET address=?,phone=?,email=?,whatsapp=?,social_links=?,working_hours=?,
            show_phone=?,show_whatsapp=?,show_email=?,show_address=?,show_hours=?,
            show_facebook=?,show_twitter=?,show_instagram=?,show_youtube=?,show_linkedin=?
            WHERE id=?")
            ->execute([$address,$phone,$email,$whatsapp,$social,$hours,
                $show_phone,$show_whatsapp,$show_email,$show_address,$show_hours,
                $show_facebook,$show_twitter,$show_instagram,$show_youtube,$show_linkedin,
                $info['id']]);
    } else {
        $pdo->prepare("INSERT INTO contact_info (address,phone,email,whatsapp,social_links,working_hours,
            show_phone,show_whatsapp,show_email,show_address,show_hours,
            show_facebook,show_twitter,show_instagram,show_youtube,show_linkedin)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$address,$phone,$email,$whatsapp,$social,$hours,
                $show_phone,$show_whatsapp,$show_email,$show_address,$show_hours,
                $show_facebook,$show_twitter,$show_instagram,$show_youtube,$show_linkedin]);
    }
    $success = 'تم حفظ معلومات التواصل بنجاح';
    $info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
}

$social = !empty($info['social_links']) ? json_decode($info['social_links'], true) : [];

// Helper: get visibility value
$vis = function($key) use ($info) { return isset($info[$key]) ? (int)$info[$key] : 1; };

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>

<form method="POST">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

<!-- ── معلومات التواصل الأساسية ── -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-address-card"></i> معلومات التواصل الأساسية</h3>
    </div>
    <div class="admin-card-body">

        <!-- Phone -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon phone"><i class="fas fa-phone"></i></div>
                <div>
                    <strong>رقم الهاتف</strong>
                    <span class="field-note">يظهر في الهيدر والفوتر وصفحة التواصل</span>
                </div>
                <label class="toggle-switch" title="<?php echo $vis('show_phone')?'إخفاء':'إظهار'; ?>">
                    <input type="checkbox" name="show_phone" <?php echo $vis('show_phone')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($info['phone'] ?? ''); ?>" placeholder="+966 5X XXX XXXX">
        </div>

        <!-- WhatsApp -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon whatsapp"><i class="fab fa-whatsapp"></i></div>
                <div>
                    <strong>واتساب</strong>
                    <span class="field-note">يظهر في الفوتر وصفحة التواصل</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_whatsapp" <?php echo $vis('show_whatsapp')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="text" name="whatsapp" class="form-control" value="<?php echo htmlspecialchars($info['whatsapp'] ?? ''); ?>" placeholder="+966 5X XXX XXXX">
        </div>

        <!-- Email -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon email"><i class="fas fa-envelope"></i></div>
                <div>
                    <strong>البريد الإلكتروني</strong>
                    <span class="field-note">يظهر في الهيدر والفوتر وصفحة التواصل</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_email" <?php echo $vis('show_email')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($info['email'] ?? ''); ?>" placeholder="info@example.com">
        </div>

        <!-- Address -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon address"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <strong>العنوان</strong>
                    <span class="field-note">يظهر في الفوتر وصفحة التواصل</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_address" <?php echo $vis('show_address')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <textarea name="address" class="form-control" rows="2" placeholder="المملكة العربية السعودية - الرياض"><?php echo htmlspecialchars($info['address'] ?? ''); ?></textarea>
        </div>

        <!-- Working hours -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon hours"><i class="fas fa-clock"></i></div>
                <div>
                    <strong>ساعات العمل</strong>
                    <span class="field-note">يظهر في الفوتر وصفحة التواصل</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_hours" <?php echo $vis('show_hours')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="text" name="working_hours" class="form-control" value="<?php echo htmlspecialchars($info['working_hours'] ?? ''); ?>" placeholder="السبت - الخميس: 8 صباحاً - 6 مساءً">
        </div>

    </div>
</div>

<!-- ── التواصل الاجتماعي ── -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-share-alt"></i> قنوات التواصل الاجتماعي</h3>
    </div>
    <div class="admin-card-body">

        <!-- Facebook -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon facebook"><i class="fab fa-facebook"></i></div>
                <div>
                    <strong>فيسبوك</strong>
                    <span class="field-note">يظهر في الهيدر والفوتر</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_facebook" <?php echo $vis('show_facebook')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="url" name="facebook" class="form-control" value="<?php echo htmlspecialchars($social['facebook'] ?? ''); ?>" placeholder="https://facebook.com/...">
        </div>

        <!-- Twitter/X -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon twitter"><i class="fab fa-twitter"></i></div>
                <div>
                    <strong>تويتر / X</strong>
                    <span class="field-note">يظهر في الهيدر والفوتر</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_twitter" <?php echo $vis('show_twitter')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="url" name="twitter" class="form-control" value="<?php echo htmlspecialchars($social['twitter'] ?? ''); ?>" placeholder="https://twitter.com/...">
        </div>

        <!-- Instagram -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon instagram"><i class="fab fa-instagram"></i></div>
                <div>
                    <strong>انستقرام</strong>
                    <span class="field-note">يظهر في الهيدر والفوتر</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_instagram" <?php echo $vis('show_instagram')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="url" name="instagram" class="form-control" value="<?php echo htmlspecialchars($social['instagram'] ?? ''); ?>" placeholder="https://instagram.com/...">
        </div>

        <!-- YouTube -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon youtube"><i class="fab fa-youtube"></i></div>
                <div>
                    <strong>يوتيوب</strong>
                    <span class="field-note">يظهر في الفوتر</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_youtube" <?php echo $vis('show_youtube')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="url" name="youtube" class="form-control" value="<?php echo htmlspecialchars($social['youtube'] ?? ''); ?>" placeholder="https://youtube.com/...">
        </div>

        <!-- LinkedIn -->
        <div class="contact-field-row">
            <div class="contact-field-header">
                <div class="contact-field-icon linkedin"><i class="fab fa-linkedin"></i></div>
                <div>
                    <strong>لينكدان</strong>
                    <span class="field-note">يظهر في الفوتر</span>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="show_linkedin" <?php echo $vis('show_linkedin')?'checked':''; ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <input type="url" name="linkedin" class="form-control" value="<?php echo htmlspecialchars($social['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/...">
        </div>

    </div>
</div>
</div>

<!-- ── معاينة الحالة الراهنة ── -->
<div class="admin-card" style="margin-bottom:20px;">
    <div class="admin-card-header">
        <h3><i class="fas fa-eye"></i> الحالة الراهنة — ما يظهر الآن في الموقع</h3>
    </div>
    <div class="admin-card-body">
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php
            $channels = [
                'show_phone'     => ['fa-phone',      'الهاتف',        '#007bff'],
                'show_whatsapp'  => ['fab fa-whatsapp','واتساب',        '#25D366'],
                'show_email'     => ['fa-envelope',    'البريد',         '#fd7e14'],
                'show_address'   => ['fa-map-marker-alt','العنوان',      '#6f42c1'],
                'show_hours'     => ['fa-clock',       'ساعات العمل',   '#6c757d'],
                'show_facebook'  => ['fab fa-facebook','فيسبوك',        '#1877F2'],
                'show_twitter'   => ['fab fa-twitter', 'تويتر',         '#1DA1F2'],
                'show_instagram' => ['fab fa-instagram','انستقرام',      '#E4405F'],
                'show_youtube'   => ['fab fa-youtube', 'يوتيوب',        '#FF0000'],
                'show_linkedin'  => ['fab fa-linkedin','لينكدان',       '#0077B5'],
            ];
            foreach ($channels as $key => [$icon, $label, $color]):
                $active = $vis($key);
            ?>
            <div style="display:flex;align-items:center;gap:7px;padding:8px 14px;background:<?php echo $active?'#f0fff4':'#fff5f5'; ?>;border:1.5px solid <?php echo $active?'#28a745':'#dc3545'; ?>;border-radius:20px;">
                <i class="fas <?php echo $icon; ?>" style="color:<?php echo $active?$color:'#ccc'; ?>;font-size:15px;"></i>
                <span style="font-size:13px;font-weight:700;color:<?php echo $active?'#333':'#bbb'; ?>;"><?php echo $label; ?></span>
                <i class="fas <?php echo $active?'fa-check-circle':'fa-times-circle'; ?>" style="color:<?php echo $active?'#28a745':'#dc3545'; ?>;font-size:13px;"></i>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div style="padding-bottom:20px;">
    <button type="submit" class="btn btn-gold" style="padding:12px 35px;font-size:15px;"><i class="fas fa-save"></i> حفظ جميع التغييرات</button>
    <a href="/contact.php" class="btn btn-dark" target="_blank" style="margin-right:10px;"><i class="fas fa-eye"></i> معاينة صفحة التواصل</a>
</div>

</form>

<style>
.contact-field-row { margin-bottom:20px; }
.contact-field-header { display:flex;align-items:center;gap:12px;margin-bottom:8px; }
.contact-field-header strong { display:block;font-size:14px;font-weight:700;color:#333; }
.contact-field-header .field-note { font-size:11px;color:#999; }
.contact-field-header .toggle-switch { margin-right:auto; }
.contact-field-icon { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0; }
.contact-field-icon.phone     { background:#dbeafe;color:#2563eb; }
.contact-field-icon.whatsapp  { background:#dcfce7;color:#16a34a; }
.contact-field-icon.email     { background:#fff7ed;color:#ea580c; }
.contact-field-icon.address   { background:#f3e8ff;color:#9333ea; }
.contact-field-icon.hours     { background:#f1f5f9;color:#64748b; }
.contact-field-icon.facebook  { background:#e8f0fe;color:#1877F2; }
.contact-field-icon.twitter   { background:#e8f5fe;color:#1DA1F2; }
.contact-field-icon.instagram { background:#fce8f3;color:#E4405F; }
.contact-field-icon.youtube   { background:#fee8e8;color:#FF0000; }
.contact-field-icon.linkedin  { background:#e8f4fd;color:#0077B5; }

/* Toggle switch */
.toggle-switch { position:relative;display:inline-block;width:50px;height:26px;flex-shrink:0; }
.toggle-switch input { opacity:0;width:0;height:0; }
.toggle-slider { position:absolute;cursor:pointer;inset:0;background:#ccc;border-radius:26px;transition:.3s; }
.toggle-slider:before { position:absolute;content:"";height:20px;width:20px;right:3px;top:3px;background:white;border-radius:50%;transition:.3s; }
.toggle-switch input:checked + .toggle-slider { background:var(--gold); }
.toggle-switch input:checked + .toggle-slider:before { transform:translateX(-24px); }
</style>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
