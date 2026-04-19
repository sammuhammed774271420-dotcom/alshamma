# السلام للعقارات — Al-Salam Real Estate

موقع عقاري عربي متكامل بلوحة تحكم كاملة — مُعدّ للرفع على InfinityFree مع MySQL.

---

## هيكل المشروع

```
salamsite/           ← جذر الموقع (Document Root)
├── index.php        ← الصفحة الرئيسية
├── about.php        ← من نحن
├── contact.php      ← التواصل (بدون datetime('now') — يستخدم db_now())
├── contact-form.php ← إعادة توجيه لـ contact.php
├── projects.php     ← قائمة المشاريع
├── project-detail.php
├── services.php
├── config.php       ← يتضمن includes/db.php
├── database.php     ← يتضمن includes/db.php
├── .htaccess        ← إعدادات Apache للـ InfinityFree
├── includes/
│   ├── db.php       ← الاتصال (MySQL أولاً، SQLite احتياطي)
│   ├── header.php
│   ├── footer.php
│   └── admin-check.php
├── admin/
│   ├── .htaccess    ← حماية مجلد الإدارة
│   ├── login.php
│   ├── dashboard.php
│   ├── projects.php / project-form.php
│   ├── services.php / service-form.php
│   ├── team.php / team-form.php
│   ├── slider.php / slider-form.php
│   ├── why-us.php / why-us-form.php
│   ├── about.php
│   ├── contact-info.php
│   ├── messages.php
│   ├── settings.php
│   └── home-sections.php
├── assets/
│   ├── css/style.css
│   └── js/main.js
└── uploads/
    ├── .htaccess    ← يمنع تنفيذ PHP في مجلد الرفع
    ├── projects/
    ├── services/
    ├── team/
    ├── about/
    └── slider/
```

---

## قاعدة البيانات

| البيئة | النوع | التفاصيل |
|--------|-------|---------|
| InfinityFree (الإنتاج) | MySQL | `sql101.infinityfree.com` / `if0_41566500_db` |
| Replit (التطوير) | SQLite | `salamsite/data/salam.sqlite` (تلقائي) |

`db.php` يحاول MySQL أولاً. إذا فشل (مثل Replit)، يتحوّل تلقائياً لـ SQLite.

---

## لوحة التحكم

- **رابط:** `/admin/login.php`
- **البريد:** `admin@salamsite1.kesug.com`
- **كلمة المرور:** `admin123`

### الصلاحيات الكاملة:
- إدارة المشاريع (CRUD + صور + معرض)
- إدارة الخدمات
- إدارة الفريق
- إدارة السلايدر
- إدارة "لماذا نحن"
- محتوى "من نحن"
- معلومات التواصل + روابط السوشال ميديا
- الرسائل الواردة
- إعدادات الموقع (الاسم، الشعار، الألوان، CTA)
- تغيير كلمة المرور
- إدارة أقسام الصفحة الرئيسية

---

## التشغيل

```bash
# Replit
php -S 0.0.0.0:5000 -t salamsite

# InfinityFree: ارفع مجلد salamsite/ كـ Document Root
```

---

## الإصلاحات المطبّقة

1. **db.php** — MySQL مع InfinityFree credentials + SQLite fallback للتطوير
2. **contact.php** — إصلاح `datetime('now')` → دالة `db_now()` المتوافقة
3. **contact-form.php** — إعادة كتابة (كان ملفاً تالفاً ومكرراً)
4. **admin/settings.php** — تغيير "SQLite" → "MySQL (InfinityFree)"
5. **.htaccess** — ملف Apache كامل للـ InfinityFree (أمان، ضغط، cache)
6. **admin/.htaccess** — حماية إضافية للوحة التحكم
7. **uploads/.htaccess** — منع تنفيذ PHP داخل مجلد الرفع

---

## التقنيات

- PHP 8.2
- MySQL (InfinityFree) / SQLite (تطوير)
- HTML/CSS/JS — بدون frameworks محلية
- CDN: Font Awesome 6.4.0، Google Fonts Cairo
- التصميم: RTL عربي، ألوان ذهبية وداكنة
