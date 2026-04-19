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
body{background:linear-gradient(135deg,#1a1a1a 0%,#2f2410 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Cairo',sans-serif;direction:rtl;}
.login-box{background:#fff;border-radius:10px;width:100%;max-width:420px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.4);}
.login-header{background:#1a1a1a;padding:35px 30px;text-align:center;border-bottom:3px solid #b8963e;}
.login-header .logo-ar{font-size:24px;font-weight:900;color:#b8963e;display:block;}
.login-header .logo-en{font-size:9px;color:#666;letter-spacing:3px;}
.login-header p{color:#aaa;font-size:13px;margin-top:8px;}
.login-body{padding:35px 30px;}
.login-body h2{font-size:20px;font-weight:700;color:#1a1a1a;margin-bottom:25px;text-align:center;}
.form-group{margin-bottom:18px;}
.form-group label{display:block;font-size:13px;font-weight:700;color:#555;margin-bottom:7px;}
.form-group .input-wrap{position:relative;}
.form-group .input-wrap i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#b8963e;}
.form-group input{width:100%;padding:11px 38px 11px 14px;border:1px solid #e0d8c8;border-radius:5px;font-size:14px;font-family:'Cairo',sans-serif;background:#fafaf8;transition:all .3s;}
.form-group input:focus{outline:none;border-color:#b8963e;box-shadow:0 0 0 3px rgba(184,150,62,.1);}
.login-btn{width:100%;padding:14px;background:#b8963e;color:#1a1a1a;border:none;border-radius:5px;font-size:16px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;transition:all .3s;}
.login-btn:hover{background:#d4a847;transform:translateY(-1px);}
.error-msg{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;padding:12px 15px;border-radius:5px;font-size:13px;margin-bottom:18px;text-align:center;}
.login-hint{background:rgba(184,150,62,.05);border:1px solid rgba(184,150,62,.2);border-radius:5px;padding:12px 15px;font-size:12px;color:#888;text-align:center;margin-top:18px;}
.login-hint strong{color:#b8963e;}
.back-link{display:block;text-align:center;margin-top:15px;font-size:13px;color:#888;}
.back-link a{color:#b8963e;}
</style>
</head>
<body>
<div class="login-box">
    <div class="login-header">
        <span class="logo-ar">السلام للعقارات</span>
        <span class="logo-en">AL-SALAM REAL ESTATE</span>
        <p>لوحة إدارة الموقع</p>
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
                    <input type="email" name="email" placeholder="admin@salamsite1.kesug.com" required autocomplete="email">
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
            admin@salamsite1.kesug.com / admin123
        </div>
        <div class="back-link"><a href="/index.php"><i class="fas fa-arrow-right" style="margin-left:5px;"></i>العودة للموقع</a></div>
    </div>
</div>
</body>
</html>
