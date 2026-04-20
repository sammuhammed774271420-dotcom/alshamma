<?php
$admin_title = 'إعدادات الموقع';
$admin_icon = 'cog';
require_once __DIR__ . '/../includes/admin-check.php';

$settings = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'settings';

    if ($action === 'settings') {
        $site_name   = trim($_POST['site_name'] ?? '');
        $tagline     = trim($_POST['tagline'] ?? '');
        $meta_desc   = trim($_POST['meta_description'] ?? '');
        $footer_text = trim($_POST['footer_text'] ?? '');

        $logo_path = $settings['logo_path'] ?? '';
        if (!empty($_FILES['logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif','svg'])) {
                $fname = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../uploads/' . $fname)) {
                    if ($logo_path && file_exists(__DIR__ . '/../' . $logo_path)) unlink(__DIR__ . '/../' . $logo_path);
                    $logo_path = 'uploads/' . $fname;
                }
            }
        }

        if ($settings) {
            $pdo->prepare("UPDATE site_settings SET site_name=?,tagline=?,logo_path=?,meta_description=?,footer_text=? WHERE id=?")
                ->execute([$site_name,$tagline,$logo_path,$meta_desc,$footer_text,$settings['id']]);
        } else {
            $pdo->prepare("INSERT INTO site_settings (site_name,tagline,logo_path,meta_description,footer_text) VALUES (?,?,?,?,?)")
                ->execute([$site_name,$tagline,$logo_path,$meta_desc,$footer_text]);
        }
        $success = 'تم حفظ إعدادات الموقع بنجاح';
        $settings = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
    }

    if ($action === 'cta') {
        $cta_title    = trim($_POST['cta_title'] ?? '');
        $cta_subtitle = trim($_POST['cta_subtitle'] ?? '');
        $cta_btn1_text = trim($_POST['cta_btn1_text'] ?? '');
        $cta_btn1_link = trim($_POST['cta_btn1_link'] ?? '');
        $cta_btn2_text = trim($_POST['cta_btn2_text'] ?? '');
        $cta_btn2_link = trim($_POST['cta_btn2_link'] ?? '');

        $pdo->prepare("UPDATE site_settings SET cta_title=?,cta_subtitle=?,cta_btn1_text=?,cta_btn1_link=?,cta_btn2_text=?,cta_btn2_link=? WHERE id=?")
            ->execute([$cta_title,$cta_subtitle,$cta_btn1_text,$cta_btn1_link,$cta_btn2_text,$cta_btn2_link,$settings['id']]);
        $success = 'تم حفظ قسم الدعوة (CTA) بنجاح';
        $settings = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch();
    }

    if ($action === 'password') {
        $old_pw  = $_POST['old_password'] ?? '';
        $new_pw  = $_POST['new_password'] ?? '';
        $conf_pw = $_POST['confirm_password'] ?? '';

        $admin = $pdo->prepare("SELECT * FROM admin_users WHERE id=?");
        $admin->execute([$_SESSION['admin_id']]);
        $admin = $admin->fetch();

        if (!password_verify($old_pw, $admin['password_hash'])) {
            $error = 'كلمة المرور الحالية غير صحيحة';
        } elseif ($new_pw !== $conf_pw) {
            $error = 'كلمة المرور الجديدة غير متطابقة';
        } elseif (strlen($new_pw) < 6) {
            $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } else {
            $hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admin_users SET password_hash=? WHERE id=?")->execute([$hash,$_SESSION['admin_id']]);
            $success = 'تم تغيير كلمة المرور بنجاح';
        }
    }
}

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

<!-- ── الإعدادات العامة ── -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-globe"></i> إعدادات الموقع العامة</h3>
    </div>
    <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="settings">
            <div class="form-mb">
                <label class="form-label">اسم الموقع (يظهر في الهيدر والفوتر)</label>
                <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? 'معامل الشام للخبز العربي'); ?>">
            </div>
            <div class="form-mb">
                <label class="form-label">الشعار الوصفي (يظهر تحت اسم الموقع)</label>
                <input type="text" name="tagline" class="form-control" value="<?php echo htmlspecialchars($settings['tagline'] ?? ''); ?>" placeholder="جودة الخبز العربي الأصيل سر ثقة عملائنا">
            </div>
            <div class="form-mb">
                <label class="form-label">وصف الموقع (للمحركات البحثية)</label>
                <textarea name="meta_description" class="form-control" rows="2"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
            </div>
            <div class="form-mb">
                <label class="form-label">شعار الموقع (Logo)</label>
                <?php if (!empty($settings['logo_path']) && file_exists(__DIR__ . '/../' . $settings['logo_path'])): ?>
                <div style="background:#1a1a1a;border-radius:6px;padding:15px;display:inline-block;margin-bottom:10px;">
                    <img src="/<?php echo htmlspecialchars($settings['logo_path']); ?>" alt="" style="height:55px;object-fit:contain;">
                </div><br>
                <?php endif; ?>
                <div class="upload-area">
                    <i class="fas fa-image"></i>
                    <span>اضغط لرفع شعار الموقع (PNG/SVG موصى بها)</span>
                    <input type="file" name="logo" accept="image/*" class="img-upload-input" data-preview="logoPreview" style="display:none;">
                </div>
                <img id="logoPreview" class="img-preview" style="display:none;max-height:60px;width:auto;margin-top:8px;" alt="">
            </div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> حفظ الإعدادات العامة</button>
        </form>
    </div>
</div>

<div>
    <!-- ── تغيير كلمة المرور ── -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-lock"></i> تغيير كلمة المرور</h3>
        </div>
        <div class="admin-card-body">
            <form method="POST">
                <input type="hidden" name="action" value="password">
                <div class="form-mb">
                    <label class="form-label">كلمة المرور الحالية</label>
                    <input type="password" name="old_password" class="form-control" required>
                </div>
                <div class="form-mb">
                    <label class="form-label">كلمة المرور الجديدة</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <div class="form-mb">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark"><i class="fas fa-key"></i> تغيير كلمة المرور</button>
            </form>
        </div>
    </div>
    <!-- ── معلومات النظام ── -->
    <div class="admin-card" style="margin-top:15px;">
        <div class="admin-card-header"><h3><i class="fas fa-info-circle"></i> معلومات النظام</h3></div>
        <div class="admin-card-body">
            <div style="font-size:13px;color:#888;line-height:2;">
                <p><strong>إصدار PHP:</strong> <?php echo PHP_VERSION; ?></p>
                <p><strong>قاعدة البيانات:</strong> MySQL (InfinityFree)</p>
                <p><strong>المشرف:</strong> <?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
                <p><strong>الصلاحية:</strong> <?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'Admin'); ?></p>
            </div>
        </div>
    </div>
</div>
</div>

<!-- ── قسم CTA ── -->
<div class="admin-card" id="cta">
    <div class="admin-card-header">
        <h3><i class="fas fa-bullhorn"></i> قسم الدعوة للتواصل (CTA) — يظهر في نهاية الصفحة الرئيسية</h3>
        <a href="/index.php#cta" class="btn btn-sm btn-gold" target="_blank"><i class="fas fa-eye"></i> معاينة</a>
    </div>
    <div class="admin-card-body">
        <form method="POST">
            <input type="hidden" name="action" value="cta">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <div class="form-mb">
                        <label class="form-label">العنوان الرئيسي</label>
                        <input type="text" name="cta_title" class="form-control" value="<?php echo htmlspecialchars($settings['cta_title'] ?? 'هل تريد خبزاً طازجاً يومياً لمطعمك؟'); ?>">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">النص الوصفي</label>
                        <textarea name="cta_subtitle" class="form-control" rows="2"><?php echo htmlspecialchars($settings['cta_subtitle'] ?? 'تواصل معنا الآن واحصل على عرض التوزيع اليومي من معامل الشام'); ?></textarea>
                    </div>
                </div>
                <div>
                    <div class="form-mb">
                        <label class="form-label">نص الزر الأول</label>
                        <input type="text" name="cta_btn1_text" class="form-control" value="<?php echo htmlspecialchars($settings['cta_btn1_text'] ?? 'تواصل معنا الآن'); ?>" placeholder="اتركه فارغاً لإخفاء الزر">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">رابط الزر الأول</label>
                        <input type="text" name="cta_btn1_link" class="form-control" value="<?php echo htmlspecialchars($settings['cta_btn1_link'] ?? '/contact.php'); ?>">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">نص الزر الثاني</label>
                        <input type="text" name="cta_btn2_text" class="form-control" value="<?php echo htmlspecialchars($settings['cta_btn2_text'] ?? 'استعرض المشاريع'); ?>" placeholder="اتركه فارغاً لإخفاء الزر">
                    </div>
                    <div class="form-mb">
                        <label class="form-label">رابط الزر الثاني</label>
                        <input type="text" name="cta_btn2_link" class="form-control" value="<?php echo htmlspecialchars($settings['cta_btn2_link'] ?? '/projects.php'); ?>">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-gold"><i class="fas fa-save"></i> حفظ قسم الدعوة</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
