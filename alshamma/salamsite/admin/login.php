<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';

if (isset($_SESSION['admin_id'])) { header('Location: /admin/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'يرجى إدخال البريد الإلكتروني وكلمة المرور';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id']    = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role']  = $user['role'];
            header('Location: /admin/dashboard.php');
            exit();
        } else {
            $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تسجيل الدخول - لوحة التحكم</title>
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
<style>
body{background:linear-gradient(135deg,#a01818 0%,#cc2020 50%,#7a0f0f 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Cairo',sans-serif;direction:rtl;}
.login-box{background:#fff;border-radius:12px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.4);}
.login-header{background:linear-gradient(135deg,#a01818,#cc2020);padding:30px;text-align:center;border-bottom:3px solid #c9a227;}
.login-header .bread-icon{font-size:40px;margin-bottom:8px;display:block;}
.login-header .logo-ar{font-size:20px;font-weight:900;color:#fff;display:block;}
.login-header .logo-en{font-size:10px;color:rgba(255,255,255,0.7);letter-spacing:2px;}
.login-header p{color:rgba(255,255,255,0.8);font-size:13px;margin-top:8px;}
.login-body{padding:30px;}
.login-body h2{font-size:20px;font-weight:700;color:#1a1a1a;margin-bottom:22px;text-align:center;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:13px;font-weight:700;color:#555;margin-bottom:7px;}
.form-group .input-wrap{position:relative;}
.form-group .input-wrap i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#cc2020;}
.form-group input{width:100%;padding:11px 38px 11px 14px;border:1.5px solid #e0dada;border-radius:6px;font-size:14px;font-family:'Cairo',sans-serif;background:#fafafa;transition:all .3s;}
.form-group input:focus{outline:none;border-color:#cc2020;box-shadow:0 0 0 3px rgba(204,32,32,.08);}
.login-btn{width:100%;padding:14px;background:#cc2020;color:#fff;border:none;border-radius:6px;font-size:16px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;transition:all .3s;}
.login-btn:hover{background:#e03535;transform:translateY(-1px);}
.error-msg{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:12px 15px;border-radius:6px;font-size:13px;margin-bottom:18px;text-align:center;}
.login-hint{background:#fff8ef;border:1px solid #e5d4b0;border-radius:6px;padding:12px 15px;font-size:12px;color:#888;text-align:center;margin-top:18px;}
.login-hint strong{color:#cc2020;}
.back-link{display:block;text-align:center;margin-top:15px;font-size:13px;color:#888;}
.back-link a{color:#cc2020;}
</style>
</head>
<body>
<div class="login-box">
    <div class="login-header">
        <span class="bread-icon">🍞</span>
        <span class="logo-ar">مخابز الشام للخبز العربي</span>
        <span class="logo-en">MAAMIL AL SHAM</span>
        <p>لوحة إدارة المخبز</p>
    </div>
    <div class="login-body">
        <h2>تسجيل الدخول</h2>
        <?php if ($error): ?>
        <div class="error-msg"><i class="fas fa-exclamation-circle" style="margin-left:6px;"></i><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="admin@maamil-alsham.com" required autocomplete="email">
                </div>
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt" style="margin-left:8px;"></i>دخول</button>
        </form>
        <div class="login-hint">
            <strong>البيانات الافتراضية:</strong><br>
            admin@maamil-alsham.com / admin123
        </div>
        <div class="back-link"><a href="/index.php"><i class="fas fa-arrow-right" style="margin-left:5px;"></i>العودة للموقع</a></div>
    </div>
</div>
</body>
</html>
