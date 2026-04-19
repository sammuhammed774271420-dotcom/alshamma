<?php
// ════════════════════════════════════════════════════════════
//  قاعدة البيانات — InfinityFree MySQL (الإنتاج)
//  مع وضع احتياطي بـ SQLite للتطوير المحلي
// ════════════════════════════════════════════════════════════

// ── بيانات InfinityFree MySQL ────────────────────────────────
define('DB_HOST',     'sql101.infinityfree.com');
define('DB_NAME',     'if0_41566500_db');
define('DB_USER',     'if0_41566500');
define('DB_PASS',     '3seE3xFhEPk7');
define('DB_CHARSET',  'utf8mb4');

// ── مجلدات الرفع ────────────────────────────────────────────
$upload_dirs = [
    __DIR__ . '/../uploads',
    __DIR__ . '/../uploads/projects',
    __DIR__ . '/../uploads/services',
    __DIR__ . '/../uploads/team',
    __DIR__ . '/../uploads/about',
    __DIR__ . '/../uploads/slider',
    __DIR__ . '/../uploads/offers',
];
foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// ════════════════════════════════════════════════════════════
//  محاولة الاتصال بـ MySQL (InfinityFree)
// ════════════════════════════════════════════════════════════
$pdo = null;
$use_mysql = false;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET . ";connect_timeout=5";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $use_mysql = true;
} catch (PDOException $e) {
    // الاتصال بـ MySQL فشل — الانتقال لـ SQLite (وضع التطوير)
    $sqlite_path = __DIR__ . '/../data/salam.sqlite';
    if (!is_dir(__DIR__ . '/../data')) {
        @mkdir(__DIR__ . '/../data', 0755, true);
    }
    try {
        $pdo = new PDO('sqlite:' . $sqlite_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        die('<div style="font-family:Arial,sans-serif;text-align:center;padding:50px;color:#dc3545;">
            <h2>⚠️ خطأ في قاعدة البيانات</h2>
            <p>' . htmlspecialchars($e2->getMessage()) . '</p>
        </div>');
    }
}

// ════════════════════════════════════════════════════════════
//  تعريف دوال SQL المتوافقة مع كلا النوعين
// ════════════════════════════════════════════════════════════
function db_now() {
    global $use_mysql;
    return $use_mysql ? 'NOW()' : "datetime('now')";
}

// ════════════════════════════════════════════════════════════
//  إنشاء الجداول
// ════════════════════════════════════════════════════════════
if ($use_mysql) {
    // ── MySQL ──
    $pdo->exec("CREATE TABLE IF NOT EXISTS slider_images (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        title      TEXT,
        subtitle   TEXT,
        image_path TEXT NOT NULL,
        link       TEXT,
        order_by   INT DEFAULT 0,
        active     INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(255) NOT NULL,
        location    TEXT,
        status      VARCHAR(50) DEFAULT 'قيد التنفيذ',
        image_path  TEXT,
        gallery     TEXT,
        description TEXT,
        price       TEXT,
        area        TEXT,
        added_date  DATE DEFAULT NULL,
        featured    INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(255) NOT NULL,
        description TEXT,
        icon        VARCHAR(50) DEFAULT 'fa-building',
        image_path  TEXT,
        order_by    INT DEFAULT 0,
        active      INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS team (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(255) NOT NULL,
        position   VARCHAR(255),
        image_path TEXT,
        email      VARCHAR(255),
        phone      VARCHAR(50),
        order_by   INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        name         VARCHAR(255) NOT NULL,
        email        VARCHAR(255),
        phone        VARCHAR(50),
        subject      TEXT,
        message      TEXT,
        contact_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        status       VARCHAR(50) DEFAULT 'جديد'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS about_content (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        title          TEXT,
        content        TEXT,
        vision         TEXT,
        mission        TEXT,
        stats          TEXT,
        image_path     TEXT,
        years_exp      INT DEFAULT 15,
        projects_count INT DEFAULT 200,
        clients_count  INT DEFAULT 500,
        awards_count   INT DEFAULT 30,
        features       TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_info (
        id             INT AUTO_INCREMENT PRIMARY KEY,
        address        TEXT,
        phone          VARCHAR(50),
        email          VARCHAR(255),
        whatsapp       VARCHAR(50),
        social_links   TEXT,
        working_hours  TEXT,
        show_phone     INT DEFAULT 1,
        show_whatsapp  INT DEFAULT 1,
        show_email     INT DEFAULT 1,
        show_address   INT DEFAULT 1,
        show_hours     INT DEFAULT 1,
        show_facebook  INT DEFAULT 1,
        show_twitter   INT DEFAULT 1,
        show_instagram INT DEFAULT 1,
        show_youtube   INT DEFAULT 1,
        show_linkedin  INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        email         VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role          VARCHAR(50) DEFAULT 'Editor',
        created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id               INT AUTO_INCREMENT PRIMARY KEY,
        site_name        VARCHAR(255) DEFAULT 'السلام للعقارات',
        tagline          TEXT,
        logo_path        TEXT,
        primary_color    VARCHAR(10) DEFAULT '#b8963e',
        meta_description TEXT,
        footer_text      TEXT,
        cta_title        TEXT,
        cta_subtitle     TEXT,
        cta_btn1_text    TEXT,
        cta_btn1_link    TEXT,
        cta_btn2_text    TEXT,
        cta_btn2_link    TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS why_us_items (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        icon        VARCHAR(50) DEFAULT 'fa-star',
        title       VARCHAR(255) NOT NULL,
        description TEXT,
        order_by    INT DEFAULT 0,
        active      INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS home_sections (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        section_key  VARCHAR(50) UNIQUE NOT NULL,
        section_name VARCHAR(255) NOT NULL,
        active       INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS offers (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        title       VARCHAR(255) NOT NULL,
        subtitle    TEXT,
        description TEXT,
        image_path  TEXT,
        badge_text  VARCHAR(100),
        price       VARCHAR(100),
        link        TEXT,
        order_by    INT DEFAULT 0,
        active      INT DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // ترحيلات
    $migs = [
        "ALTER TABLE site_settings ADD COLUMN cta_title TEXT",
        "ALTER TABLE site_settings ADD COLUMN cta_subtitle TEXT",
        "ALTER TABLE site_settings ADD COLUMN cta_btn1_text TEXT",
        "ALTER TABLE site_settings ADD COLUMN cta_btn1_link TEXT",
        "ALTER TABLE site_settings ADD COLUMN cta_btn2_text TEXT",
        "ALTER TABLE site_settings ADD COLUMN cta_btn2_link TEXT",
        "ALTER TABLE about_content ADD COLUMN features TEXT",
    ];
    foreach ($migs as $sql) { try { $pdo->exec($sql); } catch(Exception $e){} }

    $vcols = ['show_phone','show_whatsapp','show_email','show_address','show_hours',
              'show_facebook','show_twitter','show_instagram','show_youtube','show_linkedin'];
    foreach ($vcols as $c) { try { $pdo->exec("ALTER TABLE contact_info ADD COLUMN {$c} INT DEFAULT 1"); } catch(Exception $e){} }

} else {
    // ── SQLite (وضع التطوير في Replit) ──
    $pdo->exec("PRAGMA journal_mode=WAL");
    $pdo->exec("PRAGMA foreign_keys=ON");

    $pdo->exec("CREATE TABLE IF NOT EXISTS slider_images (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT, subtitle TEXT, image_path TEXT NOT NULL,
        link TEXT, order_by INTEGER DEFAULT 0, active INTEGER DEFAULT 1
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL, location TEXT,
        status TEXT DEFAULT 'قيد التنفيذ',
        image_path TEXT, gallery TEXT, description TEXT,
        price TEXT, area TEXT, added_date TEXT, featured INTEGER DEFAULT 0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL, description TEXT,
        icon TEXT DEFAULT 'fa-building', image_path TEXT,
        order_by INTEGER DEFAULT 0, active INTEGER DEFAULT 1
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS team (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL, position TEXT,
        image_path TEXT, email TEXT, phone TEXT,
        order_by INTEGER DEFAULT 0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL, email TEXT, phone TEXT,
        subject TEXT, message TEXT,
        contact_date TEXT DEFAULT (datetime('now')),
        status TEXT DEFAULT 'جديد'
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS about_content (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT, content TEXT, vision TEXT, mission TEXT,
        stats TEXT, image_path TEXT,
        years_exp INTEGER DEFAULT 15, projects_count INTEGER DEFAULT 200,
        clients_count INTEGER DEFAULT 500, awards_count INTEGER DEFAULT 30,
        features TEXT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_info (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        address TEXT, phone TEXT, email TEXT, whatsapp TEXT,
        social_links TEXT, working_hours TEXT,
        show_phone INTEGER DEFAULT 1, show_whatsapp INTEGER DEFAULT 1,
        show_email INTEGER DEFAULT 1, show_address INTEGER DEFAULT 1,
        show_hours INTEGER DEFAULT 1, show_facebook INTEGER DEFAULT 1,
        show_twitter INTEGER DEFAULT 1, show_instagram INTEGER DEFAULT 1,
        show_youtube INTEGER DEFAULT 1, show_linkedin INTEGER DEFAULT 1
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL, password_hash TEXT NOT NULL,
        role TEXT DEFAULT 'Editor',
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        site_name TEXT DEFAULT 'السلام للعقارات',
        tagline TEXT, logo_path TEXT,
        primary_color TEXT DEFAULT '#b8963e',
        meta_description TEXT, footer_text TEXT,
        cta_title TEXT, cta_subtitle TEXT,
        cta_btn1_text TEXT, cta_btn1_link TEXT,
        cta_btn2_text TEXT, cta_btn2_link TEXT
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS why_us_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        icon TEXT DEFAULT 'fa-star', title TEXT NOT NULL,
        description TEXT, order_by INTEGER DEFAULT 0, active INTEGER DEFAULT 1
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS home_sections (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        section_key TEXT UNIQUE NOT NULL,
        section_name TEXT NOT NULL, active INTEGER DEFAULT 1
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS offers (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        title       TEXT NOT NULL,
        subtitle    TEXT,
        description TEXT,
        image_path  TEXT,
        badge_text  TEXT,
        price       TEXT,
        link        TEXT,
        order_by    INTEGER DEFAULT 0,
        active      INTEGER DEFAULT 1
    )");
}

// ════════════════════════════════════════════════════════════
//  البيانات الافتراضية (مشتركة بين MySQL و SQLite)
// ════════════════════════════════════════════════════════════

if ((int)$pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn() === 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO admin_users (email,password_hash,role) VALUES (?,?,'Admin')")
        ->execute(['admin@salamsite1.kesug.com', $hash]);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM site_settings")->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO site_settings (site_name,tagline,primary_color) VALUES (?,?,?)")
        ->execute(['السلام للعقارات','نحقق أحلامك العقارية','#b8963e']);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM about_content")->fetchColumn() === 0) {
    $feats = json_encode(['فريق متخصص ذو خبرة واسعة','شفافية كاملة في التعاملات','ضمان أعلى معايير الجودة','دعم ما بعد البيع']);
    $pdo->prepare("INSERT INTO about_content (title,content,vision,mission,years_exp,projects_count,clients_count,awards_count,features) VALUES (?,?,?,?,?,?,?,?,?)")
        ->execute([
            'من نحن - شركة السلام للعقارات',
            'شركة السلام للعقارات شركة رائدة في مجال التطوير العقاري، تأسست بهدف تقديم أفضل الحلول العقارية لعملائنا الكرام.',
            'أن نكون الشركة العقارية الأولى في المنطقة من حيث الجودة والموثوقية وخدمة العملاء.',
            'تقديم خدمات عقارية متكاملة تجمع بين الاحترافية والأمانة لتحقيق رضا عملائنا.',
            15, 200, 500, 30, $feats,
        ]);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM contact_info")->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO contact_info (address,phone,email,whatsapp,working_hours) VALUES (?,?,?,?,?)")
        ->execute(['المملكة العربية السعودية - الرياض','+966 50 000 0000','info@salamsite1.kesug.com','+966 50 000 0000','السبت - الخميس: 8 صباحاً - 6 مساءً']);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn() === 0) {
    $srvs = [
        ['بيع العقارات',        'نقدم أفضل عروض البيع العقاري بأسعار تنافسية وخدمة متميزة',   'fa-home'],
        ['تأجير العقارات',      'خدمات تأجير شاملة للمساكن والمحلات التجارية والمكاتب',        'fa-key'],
        ['إدارة الأملاك',       'ندير أملاكك باحترافية لضمان أعلى عائد استثماري',              'fa-chart-line'],
        ['الاستشارات العقارية', 'فريق من الخبراء لتقديم أفضل الاستشارات والتوجيه',             'fa-handshake'],
        ['تقييم العقارات',      'تقييم دقيق وموثوق للعقارات وفق المعايير الدولية',            'fa-search-dollar'],
        ['التطوير العقاري',     'مشاريع تطوير عقاري متكاملة بأحدث التصاميم',                   'fa-city'],
    ];
    $st = $pdo->prepare("INSERT INTO services (name,description,icon,order_by) VALUES (?,?,?,?)");
    foreach ($srvs as $i => $s) { $st->execute([$s[0],$s[1],$s[2],$i+1]); }
}

if ((int)$pdo->query("SELECT COUNT(*) FROM why_us_items")->fetchColumn() === 0) {
    $items = [
        ['fa-shield-alt','الأمانة والمصداقية','نلتزم بأعلى معايير الأمانة والشفافية في جميع تعاملاتنا مع عملائنا',1],
        ['fa-award',     'الجودة المضمونة',   'نقدم أعلى معايير الجودة في جميع مشاريعنا وخدماتنا العقارية',     2],
        ['fa-users',     'فريق متخصص',        'لدينا فريق من الخبراء المتخصصين في مجال العقارات لخدمتكم',        3],
        ['fa-headset',   'دعم مستمر',          'نوفر دعماً متواصلاً لعملائنا قبل وبعد إتمام الصفقة العقارية',    4],
    ];
    $st = $pdo->prepare("INSERT INTO why_us_items (icon,title,description,order_by) VALUES (?,?,?,?)");
    foreach ($items as $it) { $st->execute($it); }
}

if ((int)$pdo->query("SELECT COUNT(*) FROM home_sections")->fetchColumn() === 0) {
    $secs = [
        ['slider',  'قسم السلايدر (الصور المتحركة)',1],
        ['stats',   'قسم الإحصائيات والأرقام',       1],
        ['services','قسم الخدمات',                   1],
        ['projects','قسم المشاريع المميزة',           1],
        ['offers',  'قسم العروض والصور الترويجية',   1],
        ['about',   'قسم من نحن (معاينة)',            1],
        ['whyus',   'قسم لماذا نحن',                  1],
        ['cta',     'قسم الدعوة للتواصل (CTA)',       1],
    ];
    $st = $pdo->prepare("INSERT INTO home_sections (section_key,section_name,active) VALUES (?,?,?)");
    foreach ($secs as $s) { $st->execute($s); }
} else {
    // ضمان وجود سجل العروض في الجدول (للمستخدمين الحاليين)
    $exists = (int)$pdo->query("SELECT COUNT(*) FROM home_sections WHERE section_key='offers'")->fetchColumn();
    if (!$exists) {
        $pdo->prepare("INSERT INTO home_sections (section_key,section_name,active) VALUES (?,?,?)")
            ->execute(['offers','قسم العروض والصور الترويجية',1]);
    }
}
