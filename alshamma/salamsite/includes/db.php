<?php
// ════════════════════════════════════════════════════════════
//  قاعدة البيانات — InfinityFree MySQL (الإنتاج)
//  مع وضع احتياطي بـ SQLite للتطوير المحلي
// ════════════════════════════════════════════════════════════

// ── بيانات InfinityFree MySQL ────────────────────────────────
define('DB_HOST',     'sql111.infinityfree.com');
define('DB_NAME',     'if0_41703916_XXX');
define('DB_USER',     'if0_41566500');
define('DB_PASS',     'tTc28C1uVB8RMlW');
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
        site_name        VARCHAR(255) DEFAULT 'معامل الشام للخبز العربي',
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
        site_name TEXT DEFAULT 'معامل الشام للخبز العربي',
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
        ->execute(['admin@maamil-alsham.com', $hash]);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM site_settings")->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO site_settings (site_name,tagline,primary_color) VALUES (?,?,?)")
        ->execute(['معامل الشام للخبز العربي','جودة الخبز العربي الأصيل سر ثقة عملائنا','#cc2020']);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM about_content")->fetchColumn() === 0) {
    $feats = json_encode(['خبز عربي طازج يومياً','نظافة وجودة مضمونة','فريق متخصص ذو خبرة','توزيع يومي منتظم']);
    $pdo->prepare("INSERT INTO about_content (title,content,vision,mission,years_exp,projects_count,clients_count,awards_count,features) VALUES (?,?,?,?,?,?,?,?,?)")
        ->execute([
            'من نحن - معامل الشام للخبز العربي',
            'معامل الشام للخبز العربي، شركة رائدة في مجال صناعة وتوزيع الخبز العربي الأصيل، تأسست بهدف تقديم أجود أنواع الخبز العربي الطازج لعملائنا الكرام بأعلى معايير النظافة والجودة.',
            'أن نكون المرجع الأول في صناعة الخبز العربي الأصيل في المنطقة من حيث الجودة والطعم والموثوقية.',
            'تقديم خبز عربي طازج ومميز يومياً يجمع بين الجودة والأصالة لتحقيق رضا عملائنا.',
            10, 50, 500, 15, $feats,
        ]);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM contact_info")->fetchColumn() === 0) {
    $pdo->prepare("INSERT INTO contact_info (address,phone,email,whatsapp,working_hours) VALUES (?,?,?,?,?)")
        ->execute(['الأردن - عمان','+962 79 000 0000','info@maamil-alsham.com','+962 79 000 0000','السبت - الخميس: 6 صباحاً - 4 مساءً']);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM services")->fetchColumn() === 0) {
    $srvs = [
        ['خبز عربي',            'خبز عربي طازج يومياً مصنوع بأجود المكونات وأعلى معايير النظافة',   'fa-bread-slice'],
        ['خبز التنور',           'خبز تنور أصيل بالطريقة التقليدية المتوارثة',                        'fa-fire'],
        ['المعجنات',             'تشكيلة واسعة من المعجنات الطازجة اليومية',                          'fa-cookie'],
        ['التوزيع اليومي',       'خدمة توزيع يومية منتظمة لجميع عملائنا من المطاعم والمحلات',        'fa-truck'],
        ['طلبات مخصصة',         'نوفر طلبات مخصصة بالكميات والمواصفات التي تناسب احتياجاتكم',       'fa-clipboard-list'],
        ['الجودة والنظافة',      'نلتزم بأعلى معايير الجودة والنظافة في جميع مراحل الإنتاج',          'fa-award'],
    ];
    $st = $pdo->prepare("INSERT INTO services (name,description,icon,order_by) VALUES (?,?,?,?)");
    foreach ($srvs as $i => $s) { $st->execute([$s[0],$s[1],$s[2],$i+1]); }
}

if ((int)$pdo->query("SELECT COUNT(*) FROM why_us_items")->fetchColumn() === 0) {
    $items = [
        ['fa-shield-alt','الأمانة والمصداقية','نلتزم بأعلى معايير الأمانة والشفافية في جميع تعاملاتنا مع عملائنا',1],
        ['fa-award',     'جودة مضمونة',        'نستخدم أجود المواد الخام لإنتاج خبز عربي طازج ولذيذ يومياً',    2],
        ['fa-truck',     'توزيع يومي',         'نوفر خدمة توزيع يومية منتظمة وموثوقة لجميع عملائنا',             3],
        ['fa-shield-alt','نظافة تامة',          'نلتزم بأعلى معايير النظافة والصحة في جميع مراحل الإنتاج',        4],
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
    $exists = (int)$pdo->query("SELECT COUNT(*) FROM home_sections WHERE section_key='offers'")->fetchColumn();
    if (!$exists) {
        $pdo->prepare("INSERT INTO home_sections (section_key,section_name,active) VALUES (?,?,?)")
            ->execute(['offers','قسم العروض والصور الترويجية',1]);
    }
}

// ════════════════════════════════════════════════════════════
//  جداول الموارد البشرية (HR)
// ════════════════════════════════════════════════════════════
if ($use_mysql) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_employees (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        emp_number      VARCHAR(50) UNIQUE NOT NULL,
        name            VARCHAR(255) NOT NULL,
        department      VARCHAR(100),
        position        VARCHAR(100),
        hire_date       DATE,
        base_salary     DECIMAL(10,2) DEFAULT 0,
        phone           VARCHAR(50),
        national_id     VARCHAR(50),
        notes           TEXT,
        status          VARCHAR(20) DEFAULT 'active',
        created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_attendance (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        emp_id      INT NOT NULL,
        att_date    DATE NOT NULL,
        status      VARCHAR(20) DEFAULT 'حضور',
        notes       TEXT,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_salaries (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        emp_id      INT NOT NULL,
        month       VARCHAR(7) NOT NULL,
        base_salary DECIMAL(10,2) DEFAULT 0,
        additions   DECIMAL(10,2) DEFAULT 0,
        deductions  DECIMAL(10,2) DEFAULT 0,
        net_salary  DECIMAL(10,2) DEFAULT 0,
        paid        INT DEFAULT 0,
        notes       TEXT,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_advances (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        emp_id      INT NOT NULL,
        adv_date    DATE NOT NULL,
        amount      DECIMAL(10,2) NOT NULL,
        type        VARCHAR(20) DEFAULT 'سلفة',
        description TEXT,
        deducted    INT DEFAULT 0,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_transactions (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        emp_id      INT NOT NULL,
        trans_date  DATE NOT NULL,
        type        VARCHAR(30) NOT NULL,
        description TEXT,
        debit       DECIMAL(10,2) DEFAULT 0,
        credit      DECIMAL(10,2) DEFAULT 0,
        balance     DECIMAL(10,2) DEFAULT 0,
        ref_id      INT DEFAULT NULL,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // جداول التوزيع
    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_customers (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        cust_number VARCHAR(50) UNIQUE,
        name        VARCHAR(255) NOT NULL,
        type        VARCHAR(50) DEFAULT 'مطعم',
        address     TEXT,
        phone       VARCHAR(50),
        contact_name VARCHAR(100),
        notes       TEXT,
        balance     DECIMAL(10,2) DEFAULT 0,
        status      VARCHAR(20) DEFAULT 'active',
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_products (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(255) NOT NULL,
        unit        VARCHAR(50) DEFAULT 'قطعة',
        price       DECIMAL(10,3) DEFAULT 0,
        description TEXT,
        active      INT DEFAULT 1,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_receipts (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        receipt_num  VARCHAR(50) UNIQUE NOT NULL,
        cust_id      INT NOT NULL,
        receipt_date DATE NOT NULL,
        total_amount DECIMAL(10,3) DEFAULT 0,
        notes        TEXT,
        created_by   VARCHAR(100),
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cust_id) REFERENCES dist_customers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_receipt_items (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        receipt_id  INT NOT NULL,
        product_id  INT NOT NULL,
        quantity    DECIMAL(10,3) DEFAULT 0,
        unit_price  DECIMAL(10,3) DEFAULT 0,
        total       DECIMAL(10,3) DEFAULT 0,
        FOREIGN KEY (receipt_id) REFERENCES dist_receipts(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES dist_products(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

} else {
    // SQLite
    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_employees (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emp_number TEXT UNIQUE NOT NULL,
        name TEXT NOT NULL,
        department TEXT,
        position TEXT,
        hire_date TEXT,
        base_salary REAL DEFAULT 0,
        phone TEXT,
        national_id TEXT,
        notes TEXT,
        status TEXT DEFAULT 'active',
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_attendance (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emp_id INTEGER NOT NULL,
        att_date TEXT NOT NULL,
        status TEXT DEFAULT 'حضور',
        notes TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_salaries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emp_id INTEGER NOT NULL,
        month TEXT NOT NULL,
        base_salary REAL DEFAULT 0,
        additions REAL DEFAULT 0,
        deductions REAL DEFAULT 0,
        net_salary REAL DEFAULT 0,
        paid INTEGER DEFAULT 0,
        notes TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_advances (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emp_id INTEGER NOT NULL,
        adv_date TEXT NOT NULL,
        amount REAL NOT NULL,
        type TEXT DEFAULT 'سلفة',
        description TEXT,
        deducted INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hr_transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emp_id INTEGER NOT NULL,
        trans_date TEXT NOT NULL,
        type TEXT NOT NULL,
        description TEXT,
        debit REAL DEFAULT 0,
        credit REAL DEFAULT 0,
        balance REAL DEFAULT 0,
        ref_id INTEGER DEFAULT NULL,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (emp_id) REFERENCES hr_employees(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cust_number TEXT UNIQUE,
        name TEXT NOT NULL,
        type TEXT DEFAULT 'مطعم',
        address TEXT,
        phone TEXT,
        contact_name TEXT,
        notes TEXT,
        balance REAL DEFAULT 0,
        status TEXT DEFAULT 'active',
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        unit TEXT DEFAULT 'قطعة',
        price REAL DEFAULT 0,
        description TEXT,
        active INTEGER DEFAULT 1,
        created_at TEXT DEFAULT (datetime('now'))
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_receipts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        receipt_num TEXT UNIQUE NOT NULL,
        cust_id INTEGER NOT NULL,
        receipt_date TEXT NOT NULL,
        total_amount REAL DEFAULT 0,
        notes TEXT,
        created_by TEXT,
        created_at TEXT DEFAULT (datetime('now')),
        FOREIGN KEY (cust_id) REFERENCES dist_customers(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS dist_receipt_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        receipt_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        quantity REAL DEFAULT 0,
        unit_price REAL DEFAULT 0,
        total REAL DEFAULT 0,
        FOREIGN KEY (receipt_id) REFERENCES dist_receipts(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES dist_products(id)
    )");
}

// بيانات تجريبية للمنتجات
if ((int)$pdo->query("SELECT COUNT(*) FROM dist_products")->fetchColumn() === 0) {
    $prods = [
        ['خبز عربي عادي',  'ربطة',  0.150, 'خبز عربي طازج - 10 قطع في الربطة'],
        ['خبز عربي كبير',  'ربطة',  0.200, 'خبز عربي كبير الحجم - 8 قطع في الربطة'],
        ['خبز تنور',       'قطعة',  0.050, 'خبز تنور طازج يومياً'],
        ['خبز بالسمسم',    'ربطة',  0.250, 'خبز عربي بالسمسم - 10 قطع في الربطة'],
    ];
    $st = $pdo->prepare("INSERT INTO dist_products (name,unit,price,description) VALUES (?,?,?,?)");
    foreach ($prods as $p) { $st->execute($p); }
}

// تحديث بيانات الموقع للمخبز
$pdo->exec("UPDATE site_settings SET site_name='مخابز الشام للخبز العربي', tagline='جودة الخبز ... سر ثقة عملائنا', primary_color='#cc2020' WHERE id=1");
$pdo->exec("UPDATE contact_info SET address='الأردن - عمان', phone='+962 7 9000 0000', email='info@maamil-alsham.com', whatsapp='+962 7 9000 0000', working_hours='السبت - الخميس: 4 صباحاً - 12 ظهراً' WHERE id=1");

