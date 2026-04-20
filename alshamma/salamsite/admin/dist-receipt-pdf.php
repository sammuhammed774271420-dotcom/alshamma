<?php
require_once __DIR__ . '/../includes/admin-check.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: dist-receipts.php'); exit; }

$s = $pdo->prepare("SELECT r.*, c.name as cust_name, c.type as cust_type, c.phone as cust_phone FROM dist_receipts r JOIN dist_customers c ON r.cust_id=c.id WHERE r.id=?");
$s->execute([$id]);
$receipt = $s->fetch();
if (!$receipt) { header('Location: dist-receipts.php'); exit; }

$si = $pdo->prepare("SELECT ri.*, p.name as prod_name, p.unit FROM dist_receipt_items ri JOIN dist_products p ON ri.product_id=p.id WHERE ri.receipt_id=? ORDER BY ri.id");
$si->execute([$id]);
$items = $si->fetchAll();

function amountToWords($amount) {
    $amount = round($amount, 3);
    $dinar = (int)$amount;
    $fils  = round(($amount - $dinar) * 1000);
    $w = '';
    if ($dinar > 0) $w .= $dinar . ' دينار';
    if ($fils  > 0) $w .= ($w ? ' و' : '') . $fils . ' فلس';
    return $w ?: 'صفر';
}

$items_desc = implode(' - ', array_map(fn($i) => $i['prod_name'], $items));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>سند - <?php echo htmlspecialchars($receipt['receipt_num']); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Cairo',sans-serif;direction:rtl;background:#e8e8e8;color:#333;font-size:13px;}

.no-print{background:#333;color:#fff;padding:10px 20px;display:flex;gap:15px;align-items:center;position:sticky;top:0;z-index:99;}
.print-btn{background:#cc2020;color:#fff;border:none;padding:8px 20px;border-radius:5px;font-family:'Cairo',sans-serif;font-size:14px;cursor:pointer;font-weight:700;}

/* ─── Page: A5 landscape ─── */
.page{
    width:210mm;min-height:148mm;
    margin:20px auto;background:#fff;
    position:relative;overflow:hidden;
    box-shadow:0 6px 30px rgba(0,0,0,0.25);
    padding-bottom:52px;
    border:1px solid #e0e0e0;
}

/* ─── TOP HEADER ─── */
.hdr{display:flex;align-items:center;justify-content:space-between;padding:14px 20px 10px;direction:rtl;}

/* Receipt number box */
.rcpt-num-box{
    border:2px dashed #cc2020;border-radius:6px;
    padding:10px 16px;min-width:130px;text-align:center;
}
.rcpt-num-box .nb-lbl{color:#cc2020;font-weight:700;font-size:13px;display:block;margin-bottom:4px;}
.rcpt-num-box .nb-val{border-bottom:1.5px dotted #bbb;min-height:18px;font-weight:700;color:#333;font-size:13px;display:block;padding-bottom:2px;}

/* Center title */
.hdr-center{text-align:center;flex:1;padding:0 10px;}
.hdr-title-row{display:flex;align-items:center;justify-content:center;gap:8px;}
.hdr-snd{font-size:52px;font-weight:900;color:#cc2020;line-height:1;letter-spacing:-2px;}
.hdr-wheat{font-size:22px;color:#c9a227;}
.hdr-company{font-size:15px;font-weight:700;color:#333;margin-top:2px;}
.hdr-company-en{font-size:11px;color:#888;margin-top:1px;}

/* Logo */
.logo-wrap{width:90px;height:90px;border-radius:50%;overflow:hidden;flex-shrink:0;border:3px solid #e8d5a3;background:#fff;}
.logo-wrap img{width:100%;height:100%;object-fit:cover;display:block;}

/* ─── DATE ROW ─── */
.date-row{display:flex;align-items:center;padding:0 20px 8px;direction:rtl;border-bottom:1px solid #f0e4c8;margin-bottom:10px;}
.date-lbl{color:#cc2020;font-weight:700;font-size:13px;margin-left:10px;white-space:nowrap;}
.date-val{flex:1;border-bottom:1.5px dotted #bbb;padding-bottom:2px;font-weight:600;font-size:13px;}

/* ─── MAIN INFO BOX ─── */
.main-box{margin:0 20px 10px;border:1.5px solid #e8d5a3;border-radius:8px;padding:14px 16px;background:#fffdf7;display:flex;gap:16px;align-items:stretch;direction:rtl;}

/* Info fields */
.fields{flex:1;}
.field-row{display:flex;align-items:center;margin-bottom:9px;font-size:12.5px;direction:rtl;}
.field-row:last-child{margin-bottom:0;}
.field-lbl{color:#555;font-weight:700;min-width:120px;white-space:nowrap;}
.field-val{flex:1;border-bottom:1.5px dotted #bbb;padding-bottom:2px;font-weight:600;color:#222;min-height:18px;}

/* Amount box */
.amt-col{display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:130px;}
.amt-box{display:flex;align-items:stretch;border-radius:6px;overflow:hidden;border:2px solid #e8d5a3;direction:ltr;}
.amt-tag{background:#cc2020;color:#fff;font-weight:700;padding:10px 14px;font-size:13px;display:flex;align-items:center;white-space:nowrap;}
.amt-val{background:#fff;padding:10px 14px;font-weight:900;font-size:17px;color:#cc2020;display:flex;align-items:center;min-width:70px;justify-content:center;border-right:1px solid #e8d5a3;}
.amt-unit{background:#f5f5f5;padding:10px 9px;font-size:12px;color:#555;display:flex;align-items:center;border-right:1px solid #e0e0e0;}

/* ─── FOOTER BOXES ─── */
.footer-boxes{display:flex;gap:10px;margin:0 20px;direction:rtl;}
.fb{flex:1;border:1.5px solid #e8d5a3;border-radius:6px;padding:9px 12px;background:#fffdf7;}
.fb .fb-lbl{color:#cc2020;font-weight:700;font-size:12px;text-align:center;border-bottom:1px solid #e8d5a3;padding-bottom:5px;margin-bottom:7px;}
.fb .fb-line{border-bottom:1.5px dotted #bbb;height:18px;margin-bottom:5px;font-size:11px;color:#555;padding-top:2px;}
.fb .sig-area{border-bottom:1.5px dotted #bbb;height:28px;margin-bottom:4px;}
.fb .sig-lbl{font-size:10.5px;color:#888;text-align:center;}

/* ─── BOTTOM BAR ─── */
.bottom-bar{
    background:#cc2020;
    position:absolute;bottom:0;width:100%;
    padding:10px 25px;
    display:flex;align-items:center;justify-content:center;gap:10px;
}
.bottom-bar span{color:#fff;font-weight:700;font-size:14px;}
.bottom-bar .wht{color:#c9a227;font-size:18px;}

@media print{
    body{background:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    .no-print{display:none!important;}
    .page{margin:0;box-shadow:none;width:100%;border:none;}
    @page{size:A5 landscape;margin:0;}
}
</style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="print-btn"><i class="fas fa-print" style="margin-left:5px;"></i> طباعة / حفظ PDF</button>
    <a href="dist-receipts.php" style="color:#ccc;font-size:13px;">← رجوع للسندات</a>
    <?php if (!empty($_GET['new'])): ?>
    <a href="dist-receipt-form.php?cust_id=<?php echo $receipt['cust_id']; ?>" style="color:#c9a227;font-size:13px;font-weight:700;">+ إنشاء سند آخر لنفس العميل</a>
    <?php endif; ?>
    <span style="font-size:12px;color:#aaa;">لحفظ PDF: اختر "Save as PDF" من خيارات الطباعة</span>
</div>

<div class="page">

    <!-- HEADER: logo=RIGHT, title=CENTER, num=LEFT (RTL flex: first=right, last=left) -->
    <div class="hdr">
        <!-- Logo → first child = RIGHT side in RTL -->
        <div class="logo-wrap">
            <img src="/assets/img/bakery-logo.png" alt="معامل الشام">
        </div>

        <!-- Center title -->
        <div class="hdr-center">
            <div class="hdr-title-row">
                <span class="hdr-wheat">🌾</span>
                <span class="hdr-snd">سـنـد</span>
                <span class="hdr-wheat">🌾</span>
            </div>
            <div class="hdr-company">مخابز الشام للخبز العربي</div>
            <div class="hdr-company-en">Maamil Al Sham</div>
        </div>

        <!-- Receipt number → last child = LEFT side in RTL -->
        <div class="rcpt-num-box">
            <span class="nb-lbl">رقم السند :</span>
            <span class="nb-val"><?php echo htmlspecialchars($receipt['receipt_num']); ?></span>
        </div>
    </div>

    <!-- DATE -->
    <div class="date-row">
        <span class="date-lbl">التاريخ :</span>
        <span class="date-val">&nbsp;<?php echo $receipt['receipt_date']; ?></span>
    </div>

    <!-- MAIN INFO BOX -->
    <div class="main-box">
        <div class="fields">
            <div class="field-row">
                <span class="field-lbl">استلمنا من السيد /</span>
                <span class="field-val">&nbsp;<?php echo htmlspecialchars($receipt['cust_name']); ?></span>
            </div>
            <div class="field-row">
                <span class="field-lbl">مبلغ وقدره /</span>
                <span class="field-val">&nbsp;<?php echo amountToWords($receipt['total_amount']); ?></span>
            </div>
            <div class="field-row">
                <span class="field-lbl">وذلك مقابل /</span>
                <span class="field-val">&nbsp;<?php echo $items_desc ?: 'تسليم خبز عربي'; ?></span>
            </div>
            <div class="field-row">
                <span class="field-lbl">طريقة الدفع /</span>
                <span class="field-val">&nbsp;<?php echo htmlspecialchars($receipt['payment_method'] ?? 'نقداً'); ?></span>
            </div>
        </div>
        <div class="amt-col">
            <div class="amt-box">
                <span class="amt-tag">المبلغ</span>
                <span class="amt-val"><?php echo number_format($receipt['total_amount'],3); ?></span>
                <span class="amt-unit">دينار</span>
            </div>
        </div>
    </div>

    <!-- FOOTER BOXES -->
    <div class="footer-boxes">
        <div class="fb">
            <div class="fb-lbl">ملاحظات</div>
            <div class="fb-line"><?php echo htmlspecialchars($receipt['notes']??''); ?></div>
            <div class="fb-line"></div>
        </div>
        <div class="fb">
            <div class="fb-lbl">المحاسب</div>
            <div class="sig-area" style="border-bottom:1.5px dotted #bbb;height:28px;margin-bottom:4px;"></div>
            <div class="sig-lbl" style="font-size:10.5px;color:#888;text-align:center;">التوقيع</div>
        </div>
        <div class="fb">
            <div class="fb-lbl">المستلم</div>
            <div class="sig-area" style="border-bottom:1.5px dotted #bbb;height:28px;margin-bottom:4px;"></div>
            <div class="sig-lbl" style="font-size:10.5px;color:#888;text-align:center;">التوقيع</div>
        </div>
        <div class="fb">
            <div class="fb-lbl">المبلغ كتابة</div>
            <div class="fb-line" style="font-size:10.5px;"><?php echo amountToWords($receipt['total_amount']); ?></div>
            <div class="fb-line"></div>
        </div>
    </div>

    <!-- BOTTOM BAR -->
    <div class="bottom-bar">
        <span class="wht">🌾</span>
        <span>جودة الخبز ... سر ثقة عملائنا</span>
        <span class="wht">🌾</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
