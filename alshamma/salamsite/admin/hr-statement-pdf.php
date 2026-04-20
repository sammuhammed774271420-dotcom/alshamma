<?php
require_once __DIR__ . '/../includes/admin-check.php';

$emp_id = isset($_GET['emp_id']) && is_numeric($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;
$month  = $_GET['month'] ?? date('Y-m');

if (!$emp_id) { header('Location: hr-statements.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM hr_employees WHERE id=?");
$stmt->execute([$emp_id]);
$emp = $stmt->fetch();
if (!$emp) { header('Location: hr-statements.php'); exit; }

$sal_stmt = $pdo->prepare("SELECT * FROM hr_salaries WHERE emp_id=? AND month=?");
$sal_stmt->execute([$emp_id, $month]);
$salary = $sal_stmt->fetch();

$adv_stmt = $pdo->prepare("SELECT * FROM hr_advances WHERE emp_id=? AND adv_date LIKE ? ORDER BY adv_date");
$adv_stmt->execute([$emp_id, $month . '%']);
$advances = $adv_stmt->fetchAll();

$att_stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM hr_attendance WHERE emp_id=? AND att_date LIKE ? GROUP BY status");
$att_stmt->execute([$emp_id, $month . '%']);
$att_summary = [];
foreach ($att_stmt->fetchAll() as $row) { $att_summary[$row['status']] = $row['cnt']; }

$prev_sal = $pdo->prepare("SELECT COALESCE(SUM(net_salary),0) FROM hr_salaries WHERE emp_id=? AND month<?");
$prev_sal->execute([$emp_id, $month]);
$opening_credit = (float)$prev_sal->fetchColumn();

$prev_adv = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM hr_advances WHERE emp_id=? AND adv_date<?");
$prev_adv->execute([$emp_id, $month . '-01']);
$opening_debit = (float)$prev_adv->fetchColumn();
$opening_balance = $opening_credit - $opening_debit;

$transactions = [];
if ($salary) {
    $transactions[] = ['date'=>$month.'-01','desc'=>'الراتب الأساسي','debit'=>0,'credit'=>$salary['base_salary']];
    if ($salary['additions'] > 0) $transactions[] = ['date'=>$month.'-01','desc'=>'إضافات','debit'=>0,'credit'=>$salary['additions']];
    if ($salary['deductions'] > 0) $transactions[] = ['date'=>$month.'-01','desc'=>'استقطاعات','debit'=>$salary['deductions'],'credit'=>0];
}
foreach ($advances as $adv) {
    $transactions[] = ['date'=>$adv['adv_date'],'desc'=>$adv['type'].($adv['description']?' - '.$adv['description']:''),'debit'=>$adv['amount'],'credit'=>0];
}
while (count($transactions) < 10) { $transactions[] = ['date'=>'','desc'=>'','debit'=>0,'credit'=>0,'balance'=>'']; }

$balance = $opening_balance;
$total_debit = 0; $total_credit = 0;
foreach ($transactions as &$t) {
    if ($t['date']) {
        $balance += $t['credit'] - $t['debit'];
        $t['balance'] = $balance;
        $total_debit  += $t['debit'];
        $total_credit += $t['credit'];
    } else { $t['balance'] = ''; }
}
$final_balance = $opening_balance + $total_credit - $total_debit;

$kashf_num = 'KH-' . $emp['emp_number'] . '-' . str_replace('-', '', $month);
$months_ar = ['01'=>'يناير','02'=>'فبراير','03'=>'مارس','04'=>'أبريل','05'=>'مايو','06'=>'يونيو','07'=>'يوليو','08'=>'أغسطس','09'=>'سبتمبر','10'=>'أكتوبر','11'=>'نوفمبر','12'=>'ديسمبر'];
$month_ar  = ($months_ar[substr($month,5,2)] ?? substr($month,5,2)) . ' ' . substr($month,0,4);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>كشف حساب - <?php echo htmlspecialchars($emp['name']); ?> - <?php echo $month; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Cairo',sans-serif;direction:rtl;background:#e8e8e8;color:#333;font-size:13px;}
.page{
    width:210mm;min-height:297mm;
    margin:15px auto;background:#fff;
    position:relative;overflow:hidden;
    box-shadow:0 6px 30px rgba(0,0,0,0.2);
    padding-bottom:52px;
}

/* ─── HEADER ─── */
.hdr{
    display:flex;align-items:center;justify-content:space-between;
    padding:22px 25px 16px;
    border-bottom:3px solid #cc2020;
    direction:rtl;
}
.hdr-text{}
.hdr-title{font-size:32px;font-weight:900;color:#cc2020;line-height:1.1;}
.hdr-sub{font-size:14px;color:#444;font-weight:600;margin-top:3px;}

/* Logo */
.logo-wrap{width:95px;height:95px;border-radius:50%;overflow:hidden;flex-shrink:0;border:3px solid #e8d5a3;background:#fff;}
.logo-wrap img{width:100%;height:100%;object-fit:cover;display:block;}

/* Gold strip under header */
.gold-strip{background:#c9a227;height:5px;}

/* ─── SECTION TITLE ─── */
.sec-title{display:flex;align-items:center;justify-content:center;gap:14px;margin:16px 0 14px;}
.sec-title .wh{font-size:22px;color:#c9a227;}
.sec-title .tbox{background:#cc2020;color:#fff;font-size:16px;font-weight:700;padding:9px 50px;border-radius:3px;}

/* ─── INFO ROW ─── */
.info-row{display:flex;gap:14px;margin:0 22px 16px;direction:rtl;}
.ibox{border:1.5px solid #e8d5a3;border-radius:6px;padding:12px 14px;background:#fffdf7;}
.ibox.big{flex:1.8;}
.ibox.sm{flex:1;}
.iline{display:flex;align-items:center;margin-bottom:8px;font-size:12px;direction:rtl;}
.iline:last-child{margin-bottom:0;}
.iline .lbl{color:#555;font-weight:700;min-width:95px;text-align:right;white-space:nowrap;}
.iline .val{flex:1;border-bottom:1.5px dotted #bbb;padding-bottom:2px;margin-right:8px;font-weight:700;color:#222;min-height:17px;}

/* ─── TABLE ─── */
.tbl{width:calc(100% - 44px);margin:0 22px 16px;border-collapse:collapse;direction:rtl;}
.tbl thead tr{background:#cc2020;}
.tbl thead th{color:#fff;padding:9px 6px;font-size:12px;text-align:center;border:1px solid #b01010;}
.tbl thead th:first-child{background:#9a0000;}
.tbl tbody td{padding:7px 6px;text-align:center;border:1px solid #e5e5e5;height:26px;font-size:12px;color:#333;}
.tbl tbody tr:nth-child(even){background:#fafafa;}
.tbl tbody td:first-child{background:#f9f0f0;color:#cc2020;font-weight:700;}
.tbl tfoot td{background:#cc2020;color:#fff;font-weight:700;padding:9px 6px;text-align:center;border:1px solid #b01010;font-size:12px;}
.tbl tfoot td:first-child{background:#9a0000;}

/* ─── BALANCE ROW ─── */
.bal-row{display:flex;gap:12px;margin:0 22px 16px;direction:rtl;}
.bx{flex:1;border:1.5px solid #e8d5a3;border-radius:6px;padding:11px 14px;text-align:center;background:#fffdf7;}
.bx .bl{font-size:12px;color:#555;font-weight:700;margin-bottom:6px;}
.bx .bv{font-size:15px;font-weight:700;color:#333;border-bottom:1.5px dotted #bbb;padding-bottom:5px;min-height:24px;}
.bx.gold .bl{color:#c9a227;}
.bx.gold .bv{color:#c9a227;}

/* ─── NOTES ─── */
.notes{margin:0 22px 14px;border:1.5px solid #e8d5a3;border-radius:6px;padding:12px 14px;background:#fffdf7;}
.notes-lbl{font-weight:700;font-size:13px;color:#555;margin-bottom:9px;}
.notes-line{border-bottom:1.5px dotted #bbb;height:22px;margin-bottom:8px;}

/* ─── WHEAT CORNER DECO ─── */
.wheat-deco-row{display:flex;align-items:flex-end;justify-content:space-between;padding:0 18px;margin-bottom:4px;}
.wd{font-size:52px;color:rgba(201,162,39,0.15);line-height:1;}

/* ─── FOOTER ─── */
.footer{
    background:#cc2020;
    position:absolute;bottom:0;width:100%;
    padding:11px 25px;
    display:flex;align-items:center;justify-content:center;gap:12px;
}
.footer span{color:#fff;font-weight:700;font-size:14px;}
.footer .wh{color:#c9a227;font-size:20px;}

@media print{
    body{background:#fff;-webkit-print-color-adjust:exact;print-color-adjust:exact;}
    .no-print{display:none!important;}
    .page{margin:0;box-shadow:none;width:100%;}
    @page{size:A4 portrait;margin:0;}
}
</style>
</head>
<body>

<div class="no-print" style="background:#333;color:#fff;padding:10px 20px;display:flex;gap:15px;align-items:center;">
    <button onclick="window.print()" style="background:#cc2020;color:#fff;border:none;padding:8px 20px;border-radius:5px;font-family:'Cairo',sans-serif;font-size:14px;cursor:pointer;font-weight:700;">
        <i class="fas fa-print"></i> طباعة / حفظ PDF
    </button>
    <a href="hr-statements.php?emp_id=<?php echo $emp_id; ?>&month=<?php echo $month; ?>" style="color:#ccc;font-size:13px;">← رجوع</a>
    <span style="font-size:12px;color:#aaa;">لحفظ PDF: اختر "Save as PDF" من خيارات الطباعة</span>
</div>

<div class="page">

    <!-- HEADER: logo=RIGHT, text=LEFT (RTL flex: first=right, last=left) -->
    <div class="hdr">
        <!-- Logo → first child = RIGHT side in RTL -->
        <div class="logo-wrap">
            <img src="/assets/img/bakery-logo.png" alt="معامل الشام">
        </div>
        <!-- Company name → last child = LEFT side in RTL -->
        <div class="hdr-text">
            <div class="hdr-title">مخابز الشام للخبز العربي</div>
            <div class="hdr-sub">Maamil Al Sham</div>
        </div>
    </div>
    <div class="gold-strip"></div>

    <!-- SECTION TITLE -->
    <div class="sec-title">
        <span class="wh">🌾</span>
        <div class="tbox">كشف حساب موظف</div>
        <span class="wh">🌾</span>
    </div>

    <!-- INFO ROW -->
    <div class="info-row">
        <div class="ibox big">
            <div class="iline"><span class="lbl">اسم الموظف :</span><span class="val"><?php echo htmlspecialchars($emp['name']); ?></span></div>
            <div class="iline"><span class="lbl">رقم الموظف :</span><span class="val"><?php echo htmlspecialchars($emp['emp_number']); ?></span></div>
            <div class="iline"><span class="lbl">القسم :</span><span class="val"><?php echo htmlspecialchars($emp['department'] ?? ''); ?></span></div>
            <div class="iline"><span class="lbl">تاريخ الكشف :</span><span class="val"><?php echo date('Y/m/d'); ?></span></div>
        </div>
        <div class="ibox sm">
            <div class="iline"><span class="lbl">الشهر :</span><span class="val"><?php echo $month_ar; ?></span></div>
            <div class="iline"><span class="lbl">سنة :</span><span class="val"><?php echo substr($month,0,4); ?></span></div>
            <div class="iline"><span class="lbl">رقم الكشف :</span><span class="val"><?php echo $kashf_num; ?></span></div>
        </div>
    </div>

    <!-- TABLE -->
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:30px;">م</th>
                <th style="width:85px;">التاريخ</th>
                <th>البيان</th>
                <th style="width:90px;">مدين (خصم)</th>
                <th style="width:90px;">دائن (إضافة)</th>
                <th style="width:90px;">الرصيد</th>
            </tr>
        </thead>
        <tbody>
        <?php for ($i = 0; $i < 10; $i++):
            $t = $transactions[$i] ?? ['date'=>'','desc'=>'','debit'=>0,'credit'=>0,'balance'=>''];
        ?>
        <tr>
            <td><?php echo $i+1; ?></td>
            <td><?php echo $t['date']; ?></td>
            <td style="text-align:right;padding-right:10px;"><?php echo htmlspecialchars($t['desc']); ?></td>
            <td><?php echo ($t['debit'] > 0) ? number_format($t['debit'],3) : ''; ?></td>
            <td><?php echo ($t['credit'] > 0) ? number_format($t['credit'],3) : ''; ?></td>
            <td><?php echo ($t['balance'] !== '') ? number_format($t['balance'],3) : ''; ?></td>
        </tr>
        <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td colspan="2" style="font-size:14px;">الإجمالي</td>
                <td><?php echo number_format($total_debit,3); ?></td>
                <td><?php echo number_format($total_credit,3); ?></td>
                <td><?php echo number_format($final_balance,3); ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- BALANCE ROW -->
    <div class="bal-row">
        <div class="bx">
            <div class="bl">الرصيد السابق</div>
            <div class="bv"><?php echo number_format($opening_balance,3); ?></div>
        </div>
        <div class="bx gold">
            <div class="bl">إجمالي الحركة</div>
            <div class="bv"><?php echo number_format($total_credit - $total_debit,3); ?></div>
        </div>
        <div class="bx">
            <div class="bl">الرصيد الحالي</div>
            <div class="bv" style="color:<?php echo $final_balance >= 0 ? '#16a34a' : '#cc2020'; ?>;">
                <?php echo number_format($final_balance,3); ?>
            </div>
        </div>
    </div>

    <!-- NOTES + WHEAT DECO -->
    <div style="display:flex;align-items:flex-start;gap:0;margin:0 22px 10px;direction:rtl;">
        <div style="width:65px;font-size:60px;color:rgba(201,162,39,0.14);line-height:1;margin-left:8px;flex-shrink:0;">🌾</div>
        <div style="flex:1;border:1.5px solid #e8d5a3;border-radius:6px;padding:12px 14px;background:#fffdf7;">
            <div style="font-weight:700;font-size:13px;color:#555;margin-bottom:9px;">ملاحظات :</div>
            <div style="border-bottom:1.5px dotted #bbb;height:20px;margin-bottom:8px;"></div>
            <div style="border-bottom:1.5px dotted #bbb;height:20px;margin-bottom:8px;"></div>
            <div style="border-bottom:1.5px dotted #bbb;height:20px;"></div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <span class="wh">🌾</span>
        <span>جودة الخبز ... سر ثقة عملائنا</span>
        <span class="wh">🌾</span>
    </div>

</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
