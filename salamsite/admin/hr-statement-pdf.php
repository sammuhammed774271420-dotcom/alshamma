<?php
require_once __DIR__ . '/../includes/admin-check.php';

$emp_id = isset($_GET['emp_id']) && is_numeric($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;
$month  = $_GET['month'] ?? date('Y-m');

if (!$emp_id) { header('Location: hr-statements.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM hr_employees WHERE id=?");
$stmt->execute([$emp_id]);
$emp = $stmt->fetch();
if (!$emp) { header('Location: hr-statements.php'); exit; }

// Get salary
$sal_stmt = $pdo->prepare("SELECT * FROM hr_salaries WHERE emp_id=? AND month=?");
$sal_stmt->execute([$emp_id, $month]);
$salary = $sal_stmt->fetch();

// Get advances
$adv_stmt = $pdo->prepare("SELECT * FROM hr_advances WHERE emp_id=? AND adv_date LIKE ? ORDER BY adv_date");
$adv_stmt->execute([$emp_id, $month . '%']);
$advances = $adv_stmt->fetchAll();

// Attendance summary
$att_stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM hr_attendance WHERE emp_id=? AND att_date LIKE ? GROUP BY status");
$att_stmt->execute([$emp_id, $month . '%']);
$att_summary = [];
foreach ($att_stmt->fetchAll() as $row) { $att_summary[$row['status']] = $row['cnt']; }

// Opening balance
$prev_sal = $pdo->prepare("SELECT COALESCE(SUM(net_salary),0) FROM hr_salaries WHERE emp_id=? AND month<?");
$prev_sal->execute([$emp_id, $month]);
$opening_credit = (float)$prev_sal->fetchColumn();

$prev_adv = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM hr_advances WHERE emp_id=? AND adv_date<?");
$prev_adv->execute([$emp_id, $month . '-01']);
$opening_debit = (float)$prev_adv->fetchColumn();
$opening_balance = $opening_credit - $opening_debit;

// Build transactions
$transactions = [];
if ($salary) {
    $transactions[] = ['date'=>$month.'-01','desc'=>'الراتب الأساسي','debit'=>0,'credit'=>$salary['base_salary']];
    if ($salary['additions'] > 0) $transactions[] = ['date'=>$month.'-01','desc'=>'إضافات','debit'=>0,'credit'=>$salary['additions']];
    if ($salary['deductions'] > 0) $transactions[] = ['date'=>$month.'-01','desc'=>'استقطاعات','debit'=>$salary['deductions'],'credit'=>0];
}
foreach ($advances as $adv) {
    $transactions[] = ['date'=>$adv['adv_date'],'desc'=>$adv['type'].($adv['description']?' - '.$adv['description']:''),'debit'=>$adv['amount'],'credit'=>0];
}

// Fill to 10 rows
while (count($transactions) < 10) { $transactions[] = ['date'=>'','desc'=>'','debit'=>0,'credit'=>0,'balance'=>'']; }

$balance = $opening_balance;
$total_debit = 0; $total_credit = 0;
foreach ($transactions as &$t) {
    if ($t['date']) {
        $balance += $t['credit'] - $t['debit'];
        $t['balance'] = $balance;
        $total_debit += $t['debit'];
        $total_credit += $t['credit'];
    } else {
        $t['balance'] = '';
    }
}
$final_balance = $opening_balance + $total_credit - $total_debit;

$kashf_num = 'KH-' . $emp['emp_number'] . '-' . str_replace('-', '', $month);
$months_ar = ['01'=>'يناير','02'=>'فبراير','03'=>'مارس','04'=>'أبريل','05'=>'مايو','06'=>'يونيو','07'=>'يوليو','08'=>'أغسطس','09'=>'سبتمبر','10'=>'أكتوبر','11'=>'نوفمبر','12'=>'ديسمبر'];
$month_ar = ($months_ar[substr($month,5,2)] ?? substr($month,5,2)) . ' ' . substr($month,0,4);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>كشف حساب - <?php echo htmlspecialchars($emp['name']); ?> - <?php echo $month; ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
* { margin:0;padding:0;box-sizing:border-box; }
body { font-family:'Cairo',sans-serif; direction:rtl; background:#fff; color:#333; font-size:13px; }
.page { width:210mm; min-height:297mm; margin:0 auto; padding:0; background:#fff; position:relative; overflow:hidden; }

/* Header */
.header { background:#cc2020; padding:22px 28px 18px; display:flex; align-items:center; justify-content:space-between; }
.header-right { }
.header-title { font-size:32px; font-weight:900; color:#fff; line-height:1.1; }
.header-sub { font-size:15px; color:rgba(255,255,255,0.85); margin-top:3px; }
.logo-circle { width:90px; height:90px; background:#fff; border-radius:50%; display:flex;align-items:center;justify-content:center;border:4px solid #c9a227; }
.logo-circle i { font-size:36px; color:#cc2020; }

/* Red bottom bar on header */
.header-bottom-bar { background:#c9a227; height:6px; }

/* Section title */
.section-title { text-align:center; margin:20px 0; display:flex; align-items:center; justify-content:center; gap:12px; }
.section-title .wheat { font-size:20px; color:#c9a227; }
.section-title .title-box { background:#cc2020; color:#fff; font-size:17px; font-weight:700; padding:8px 40px; border-radius:4px; }

/* Info boxes */
.info-row { display:flex; gap:15px; margin:0 25px 18px; }
.info-box { flex:1; border:1.5px solid #e5d4b0; border-radius:8px; padding:14px 16px; background:#fff8ef; }
.info-box.right-box { flex:1.6; }
.info-line { display:flex; align-items:center; margin-bottom:6px; font-size:12.5px; }
.info-line .lbl { color:#888; min-width:85px; }
.info-line .val { flex:1; border-bottom:1px dotted #aaa; padding-bottom:2px; margin-right:5px; font-weight:600; }

/* Table */
.stmt-table { width:calc(100% - 50px); margin:0 25px 18px; border-collapse:collapse; }
.stmt-table thead tr { background:#cc2020; }
.stmt-table thead th { color:#fff; padding:9px 8px; font-size:12px; text-align:center; border:1px solid #b01010; }
.stmt-table tbody td { padding:8px; text-align:center; border:1px solid #e0e0e0; height:26px; font-size:12px; }
.stmt-table tbody tr:nth-child(even) { background:#fafafa; }
.stmt-table tfoot td { background:#cc2020; color:#fff; font-weight:700; padding:9px 8px; text-align:center; border:1px solid #b01010; font-size:12px; }

/* Balance row */
.balance-row { display:flex; gap:10px; margin:0 25px 18px; }
.bal-box { flex:1; border:1.5px solid #e5d4b0; border-radius:6px; padding:10px 12px; text-align:center; background:#fff8ef; }
.bal-box .bal-label { font-size:11px; color:#888; margin-bottom:4px; font-weight:600; }
.bal-box .bal-val { font-size:15px; font-weight:700; color:#cc2020; border-bottom:1.5px dotted #aaa; padding-bottom:3px; min-height:22px; }
.bal-box.highlight .bal-label { color:#c9a227; }

/* Notes */
.notes-section { margin:0 25px 18px; border:1.5px solid #e5d4b0; border-radius:8px; padding:12px 16px; background:#fff8ef; }
.notes-section .notes-title { font-weight:700; margin-bottom:8px; font-size:13px; }
.notes-line { border-bottom:1px dotted #aaa; height:20px; margin-bottom:6px; }

/* Footer */
.page-footer { background:#cc2020; position:absolute; bottom:0; width:100%; padding:10px 25px; display:flex; align-items:center; justify-content:center; gap:10px; }
.page-footer span { color:#fff; font-weight:700; font-size:14px; }
.page-footer .wheat { color:#c9a227; font-size:18px; }

/* Decorative wheat corners */
.wheat-corner { position:absolute; color:#c9a227; font-size:40px; opacity:0.15; }
.wheat-corner.bl { bottom:50px; right:15px; }
.wheat-corner.br { bottom:50px; left:15px; transform:scaleX(-1); }

@media print {
    body { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .no-print { display:none !important; }
    .page { margin:0; width:100%; }
}
</style>
</head>
<body>
<div class="no-print" style="background:#333;color:#fff;padding:10px 20px;display:flex;gap:15px;align-items:center;">
    <button onclick="window.print()" style="background:#cc2020;color:#fff;border:none;padding:8px 20px;border-radius:5px;font-family:Cairo,sans-serif;font-size:14px;cursor:pointer;font-weight:700;"><i class="fas fa-print"></i> طباعة / حفظ PDF</button>
    <a href="hr-statements.php?emp_id=<?php echo $emp_id; ?>&month=<?php echo $month; ?>" style="color:#ccc;font-size:13px;">← رجوع</a>
    <span style="font-size:12px;color:#aaa;">لحفظ PDF: اختر "Save as PDF" من خيارات الطباعة</span>
</div>

<div class="page">
    <!-- Header -->
    <div class="header">
        <div class="header-right">
            <div class="header-title">مخابز الشام للخبز العربي</div>
            <div class="header-sub">Maamil Al Sham</div>
        </div>
        <div class="logo-circle">
            <i class="fas fa-bread-slice"></i>
        </div>
    </div>
    <div class="header-bottom-bar"></div>

    <!-- Title -->
    <div class="section-title">
        <span class="wheat">🌾</span>
        <div class="title-box">كشف حساب موظف</div>
        <span class="wheat">🌾</span>
    </div>

    <!-- Info Row -->
    <div class="info-row">
        <div class="info-box right-box">
            <div class="info-line"><span class="lbl">اسم الموظف :</span><span class="val"><?php echo htmlspecialchars($emp['name']); ?></span></div>
            <div class="info-line"><span class="lbl">رقم الموظف :</span><span class="val"><?php echo htmlspecialchars($emp['emp_number']); ?></span></div>
            <div class="info-line"><span class="lbl">القسم :</span><span class="val"><?php echo htmlspecialchars($emp['department'] ?? ''); ?></span></div>
            <div class="info-line"><span class="lbl">تاريخ الكشف :</span><span class="val"><?php echo date('Y/m/d'); ?></span></div>
        </div>
        <div class="info-box">
            <div class="info-line"><span class="lbl">الشهر :</span><span class="val"><?php echo $month_ar; ?></span></div>
            <div class="info-line"><span class="lbl">سنة :</span><span class="val"><?php echo substr($month,0,4); ?></span></div>
            <div class="info-line"><span class="lbl">رقم الكشف :</span><span class="val"><?php echo $kashf_num; ?></span></div>
        </div>
    </div>

    <!-- Table -->
    <table class="stmt-table">
        <thead>
            <tr>
                <th style="width:30px;">م</th>
                <th style="width:80px;">التاريخ</th>
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
                <td colspan="3">الإجمالي</td>
                <td><?php echo number_format($total_debit,3); ?></td>
                <td><?php echo number_format($total_credit,3); ?></td>
                <td><?php echo number_format($final_balance,3); ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Balance Row -->
    <div class="balance-row">
        <div class="bal-box">
            <div class="bal-label">الرصيد السابق</div>
            <div class="bal-val"><?php echo number_format($opening_balance,3); ?></div>
        </div>
        <div class="bal-box highlight">
            <div class="bal-label" style="color:#c9a227;">إجمالي الحركة</div>
            <div class="bal-val" style="color:#c9a227;"><?php echo number_format($total_credit - $total_debit,3); ?></div>
        </div>
        <div class="bal-box">
            <div class="bal-label">الرصيد الحالي</div>
            <div class="bal-val" style="color:<?php echo $final_balance >= 0 ? '#16a34a' : '#cc2020'; ?>;"><?php echo number_format($final_balance,3); ?></div>
        </div>
    </div>

    <!-- Notes -->
    <div class="notes-section">
        <div class="notes-title">ملاحظات :</div>
        <div class="notes-line"></div>
        <div class="notes-line"></div>
        <div class="notes-line"></div>
    </div>

    <!-- Wheat decorations -->
    <div class="wheat-corner bl">🌾</div>
    <div class="wheat-corner br">🌾</div>

    <!-- Footer -->
    <div class="page-footer">
        <span class="wheat">🌾</span>
        <span>جودة الخبز ... سر ثقة عملائنا</span>
        <span class="wheat">🌾</span>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
