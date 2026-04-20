<?php
require_once __DIR__ . '/../includes/admin-check.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: dist-receipts.php'); exit; }

$s = $pdo->prepare("SELECT r.*, c.name as cust_name, c.type as cust_type, c.phone as cust_phone, c.address as cust_address FROM dist_receipts r JOIN dist_customers c ON r.cust_id=c.id WHERE r.id=?");
$s->execute([$id]);
$receipt = $s->fetch();
if (!$receipt) { header('Location: dist-receipts.php'); exit; }

$si = $pdo->prepare("SELECT ri.*, p.name as prod_name, p.unit FROM dist_receipt_items ri JOIN dist_products p ON ri.product_id=p.id WHERE ri.receipt_id=? ORDER BY ri.id");
$si->execute([$id]);
$items = $si->fetchAll();

// Amount in Arabic words helper (simplified)
function amountToWords($amount) {
    $amount = round($amount, 3);
    $dinar = (int)$amount;
    $fils = round(($amount - $dinar) * 1000);
    $words = '';
    if ($dinar > 0) $words .= $dinar . ' دينار';
    if ($fils > 0)  $words .= ($words ? ' و' : '') . $fils . ' فلس';
    return $words ?: 'صفر';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>سند - <?php echo htmlspecialchars($receipt['receipt_num']); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Cairo',sans-serif; direction:rtl; background:#f0f0f0; color:#333; font-size:13px; }

.no-print { background:#333; color:#fff; padding:10px 20px; display:flex; gap:15px; align-items:center; }
.print-btn { background:#cc2020; color:#fff; border:none; padding:8px 20px; border-radius:5px; font-family:Cairo,sans-serif; font-size:14px; cursor:pointer; font-weight:700; }

/* Receipt - A5 landscape */
.receipt-page {
    width:200mm;
    min-height:130mm;
    margin:15px auto;
    background:#fff;
    position:relative;
    overflow:hidden;
    box-shadow:0 4px 20px rgba(0,0,0,0.2);
    padding-bottom: 50px;
}

/* Red top strip */
.top-strip { background:#cc2020; height:8px; }
.gold-strip { background:#c9a227; height:3px; }

/* Header */
.r-header { display:flex; align-items:center; justify-content:space-between; padding:15px 25px 10px; }
.r-title-block { text-align:center; flex:1; }
.r-title { font-size:48px; font-weight:900; color:#cc2020; line-height:1; }
.r-subtitle { font-size:16px; font-weight:700; color:#333; margin-top:3px; }
.r-sub2 { font-size:12px; color:#888; }
.r-logo { width:85px; height:85px; background:#fff; border-radius:50%; border:4px solid #c9a227; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.r-logo span { font-size:34px; }

.r-num-box {
    border:2px solid #cc2020;
    border-radius:6px;
    padding:8px 18px;
    color:#cc2020;
    font-weight:700;
    font-size:13px;
    min-width:130px;
    text-align:center;
}
.r-num-box .label { font-size:11px; color:#888; display:block; }

.r-date-row { display:flex; align-items:center; padding:5px 25px 12px; }
.r-date-row .label { color:#cc2020; font-weight:700; margin-left:5px; }
.r-date-val { flex:1; border-bottom:1.5px dotted #aaa; padding-bottom:2px; font-weight:600; }

/* Wheat dividers */
.wheat-row { display:flex; align-items:center; justify-content:center; gap:8px; margin:5px 20px; }
.wheat-row .line { flex:1; height:1px; background:linear-gradient(to left, transparent, #c9a227 50%, transparent); }
.wheat-row .icon { color:#c9a227; font-size:14px; }

/* Info section */
.info-section { margin:8px 25px; border:1.5px solid #e5d4b0; border-radius:8px; padding:15px 18px; background:#fff8ef; }
.info-row-item { display:flex; align-items:center; margin-bottom:8px; font-size:12.5px; }
.info-row-item:last-child { margin-bottom:0; }
.info-row-item .lbl { color:#888; min-width:110px; font-weight:600; }
.info-row-item .val { flex:1; border-bottom:1px dotted #aaa; padding-bottom:2px; font-weight:600; }
.amount-box { display:flex; align-items:stretch; border-radius:6px; overflow:hidden; border:1.5px solid #e5d4b0; max-width:250px; }
.amount-label { background:#cc2020; color:#fff; font-weight:700; padding:8px 18px; font-size:13px; display:flex;align-items:center; }
.amount-val { background:#fff8ef; padding:8px 15px; flex:1; font-weight:700; font-size:16px; color:#cc2020; display:flex;align-items:center; }
.unit-lbl { background:#e5e5e5; padding:8px 10px; font-size:12px; color:#666; display:flex;align-items:center; }

/* Items table */
.items-table { width:calc(100% - 50px); margin:12px 25px; border-collapse:collapse; }
.items-table thead tr { background:#cc2020; }
.items-table thead th { color:#fff; padding:7px 10px; font-size:12px; text-align:center; }
.items-table tbody td { padding:7px 10px; border:1px solid #e0e0e0; text-align:center; font-size:12px; }
.items-table tbody tr:nth-child(even) { background:#fafafa; }
.items-table tfoot td { background:#1a1a1a; color:#fff; font-weight:700; padding:8px 10px; text-align:center; font-size:12.5px; }

/* Footer boxes */
.footer-boxes { display:flex; gap:12px; margin:12px 25px; }
.footer-box { flex:1; border:1.5px solid #e5d4b0; border-radius:6px; padding:10px 12px; background:#fff8ef; }
.footer-box.highlight { border-color:#cc2020; }
.footer-box .fb-title { font-weight:700; font-size:12px; color:#cc2020; margin-bottom:8px; text-align:center; border-bottom:1px solid #e5d4b0; padding-bottom:5px; }
.footer-box.highlight .fb-title { color:#c9a227; border-color:#c9a227; }
.footer-box .fb-val { border-bottom:1px dotted #aaa; height:20px; margin-bottom:5px; font-size:12px; }
.sig-line { border-bottom:1px dotted #aaa; height:28px; margin-bottom:5px; }
.sig-label { font-size:11px; color:#888; text-align:center; }

/* Bottom bar */
.bottom-bar { background:#cc2020; position:absolute; bottom:0; width:100%; padding:9px 25px; display:flex; align-items:center; justify-content:center; gap:8px; }
.bottom-bar span { color:#fff; font-weight:700; font-size:13px; }
.bottom-bar .wheat { color:#c9a227; font-size:16px; }

/* Decorative */
.side-deco { position:absolute; font-size:35px; color:rgba(201,162,39,0.12); }
.side-deco.right { top:120px; right:8px; }
.side-deco.left  { top:120px; left:8px; transform:scaleX(-1); }

@media print {
    body { background:#fff; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .no-print { display:none !important; }
    .receipt-page { margin:0; box-shadow:none; width:100%; }
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

<div class="receipt-page">
    <div class="top-strip"></div>
    <div class="gold-strip"></div>

    <!-- Header -->
    <div class="r-header">
        <div class="r-num-box">
            <span class="label">رقم السند :</span>
            <?php echo htmlspecialchars($receipt['receipt_num']); ?>
        </div>
        <div class="r-title-block">
            <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:3px;">
                <span style="color:#c9a227;font-size:18px;">🌾</span>
                <div class="r-title">ﺴﻨﺪ</div>
                <span style="color:#c9a227;font-size:18px;">🌾</span>
            </div>
            <div class="r-subtitle">مخابز الشام للخبز العربي</div>
            <div class="r-sub2">Maamil Al Sham</div>
        </div>
        <div class="r-logo"><span>🍞</span></div>
    </div>

    <!-- Date -->
    <div class="r-date-row">
        <span class="label">التاريخ :</span>
        <span class="r-date-val">&nbsp;<?php echo $receipt['receipt_date']; ?></span>
    </div>

    <!-- Info -->
    <div class="info-section">
        <div style="display:flex;gap:15px;align-items:flex-start;flex-wrap:wrap;">
            <div style="flex:1;">
                <div class="info-row-item">
                    <span class="lbl">استلمنا من السيد /</span>
                    <span class="val">&nbsp;<?php echo htmlspecialchars($receipt['cust_name']); ?></span>
                </div>
                <div class="info-row-item">
                    <span class="lbl">نوع العميل /</span>
                    <span class="val">&nbsp;<?php echo htmlspecialchars($receipt['cust_type']); ?></span>
                </div>
                <div class="info-row-item">
                    <span class="lbl">وذلك مقابل /</span>
                    <span class="val">&nbsp;تسليم خبز عربي</span>
                </div>
                <div class="info-row-item">
                    <span class="lbl">طريقة الدفع /</span>
                    <span class="val">&nbsp;نقداً</span>
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:#888;margin-bottom:4px;text-align:center;">المبلغ</div>
                <div class="amount-box">
                    <span class="amount-label">المبلغ</span>
                    <span class="amount-val"><?php echo number_format($receipt['total_amount'],3); ?></span>
                    <span class="unit-lbl">د.أ</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:30px;">م</th>
                <th>المنتج</th>
                <th style="width:70px;">الكمية</th>
                <th style="width:60px;">الوحدة</th>
                <th style="width:85px;">السعر</th>
                <th style="width:85px;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $i => $item): ?>
        <tr>
            <td><?php echo $i+1; ?></td>
            <td style="text-align:right;padding-right:10px;"><?php echo htmlspecialchars($item['prod_name']); ?></td>
            <td><?php echo number_format($item['quantity'],3); ?></td>
            <td><?php echo htmlspecialchars($item['unit']); ?></td>
            <td><?php echo number_format($item['unit_price'],3); ?></td>
            <td style="font-weight:700;"><?php echo number_format($item['total'],3); ?></td>
        </tr>
        <?php endforeach; ?>
        <?php for ($r = count($items); $r < 3; $r++): ?>
        <tr><td><?php echo $r+1; ?></td><td></td><td></td><td></td><td></td><td></td></tr>
        <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align:right;padding-right:15px;">الإجمالي الكلي</td>
                <td><?php echo number_format($receipt['total_amount'],3); ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer Boxes -->
    <div class="footer-boxes">
        <div class="footer-box highlight">
            <div class="fb-title">المبلغ كتابةً</div>
            <div class="fb-val" style="font-size:11px;padding-top:2px;"><?php echo amountToWords($receipt['total_amount']); ?></div>
            <div class="fb-val"></div>
        </div>
        <div class="footer-box">
            <div class="fb-title">المحاسب</div>
            <div class="sig-line"></div>
            <div class="sig-label">التوقيع</div>
        </div>
        <div class="footer-box">
            <div class="fb-title">المستلم</div>
            <div class="sig-line"></div>
            <div class="sig-label">التوقيع</div>
        </div>
        <div class="footer-box">
            <div class="fb-title">ملاحظات</div>
            <div class="fb-val" style="font-size:11px;"><?php echo htmlspecialchars($receipt['notes']??''); ?></div>
            <div class="fb-val"></div>
        </div>
    </div>

    <!-- Decorations -->
    <div class="side-deco right">🌾</div>
    <div class="side-deco left">🌾</div>

    <!-- Bottom bar -->
    <div class="bottom-bar">
        <span class="wheat">🌾</span>
        <span>جودة الخبز ... سر ثقة عملائنا</span>
        <span class="wheat">🌾</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
