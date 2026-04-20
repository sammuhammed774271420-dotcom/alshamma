<?php
$admin_title = 'كشوف حسابات الموظفين';
$admin_icon  = 'file-alt';
require_once __DIR__ . '/../includes/admin-check.php';

$emp_id = isset($_GET['emp_id']) && is_numeric($_GET['emp_id']) ? (int)$_GET['emp_id'] : 0;
$month  = $_GET['month'] ?? date('Y-m');
$year   = substr($month, 0, 4);
$mon    = substr($month, 5, 2);

$employees = $pdo->query("SELECT id,name,emp_number,department,position,base_salary FROM hr_employees ORDER BY name")->fetchAll();

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-file-alt"></i> كشوف حسابات الموظفين</h3>
    </div>
    <div class="admin-card-body">
        <form method="get" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <select name="emp_id" onchange="this.form.submit()" style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;min-width:220px;">
                <option value="">اختر الموظف</option>
                <?php foreach ($employees as $e): ?>
                <option value="<?php echo $e['id']; ?>" <?php if ($emp_id==$e['id']) echo 'selected'; ?>><?php echo htmlspecialchars($e['name']); ?> (<?php echo $e['emp_number']; ?>)</option>
                <?php endforeach; ?>
            </select>
            <input type="month" name="month" value="<?php echo $month; ?>" onchange="this.form.submit()" style="padding:10px;border:1.5px solid #ddd;border-radius:7px;font-family:Cairo,sans-serif;">
            <?php if ($emp_id): ?>
            <a href="hr-statement-pdf.php?emp_id=<?php echo $emp_id; ?>&month=<?php echo $month; ?>" target="_blank" class="btn btn-primary"><i class="fas fa-file-pdf"></i> طباعة / تصدير PDF</a>
            <?php endif; ?>
        </form>

        <?php if ($emp_id):
            $stmt = $pdo->prepare("SELECT * FROM hr_employees WHERE id=?");
            $stmt->execute([$emp_id]);
            $emp = $stmt->fetch();

            // Get salary record for the month
            $sal_stmt = $pdo->prepare("SELECT * FROM hr_salaries WHERE emp_id=? AND month=?");
            $sal_stmt->execute([$emp_id, $month]);
            $salary = $sal_stmt->fetch();

            // Get advances/deductions for the month
            $adv_stmt = $pdo->prepare("SELECT * FROM hr_advances WHERE emp_id=? AND adv_date LIKE ? ORDER BY adv_date");
            $adv_stmt->execute([$emp_id, $month . '%']);
            $advances = $adv_stmt->fetchAll();

            // Attendance summary for the month
            $att_stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM hr_attendance WHERE emp_id=? AND att_date LIKE ? GROUP BY status");
            $att_stmt->execute([$emp_id, $month . '%']);
            $att_summary = [];
            foreach ($att_stmt->fetchAll() as $row) { $att_summary[$row['status']] = $row['cnt']; }

            // Build transactions for statement
            $transactions = [];
            $row_num = 1;

            // Opening balance (previous month net)
            $prev_month_date = date('Y-m', strtotime($month . '-01 -1 month'));
            $prev_sal = $pdo->prepare("SELECT net_salary FROM hr_salaries WHERE emp_id=? AND month<?");
            $prev_sal->execute([$emp_id, $month]);
            $prev_sals = $prev_sal->fetchAll();
            $opening_credit = array_sum(array_column($prev_sals, 'net_salary'));
            $prev_adv = $pdo->prepare("SELECT SUM(amount) FROM hr_advances WHERE emp_id=? AND adv_date<?");
            $prev_adv->execute([$emp_id, $month . '-01']);
            $opening_debit = (float)$prev_adv->fetchColumn();
            $opening_balance = $opening_credit - $opening_debit;

            // Current month salary
            if ($salary) {
                $transactions[] = ['date'=>$month.'-01', 'desc'=>'الراتب الأساسي', 'debit'=>0, 'credit'=>$salary['base_salary']];
                if ($salary['additions'] > 0) $transactions[] = ['date'=>$month.'-01', 'desc'=>'إضافات', 'debit'=>0, 'credit'=>$salary['additions']];
                if ($salary['deductions'] > 0) $transactions[] = ['date'=>$month.'-01', 'desc'=>'استقطاعات', 'debit'=>$salary['deductions'], 'credit'=>0];
            }
            // Advances
            foreach ($advances as $adv) {
                $transactions[] = ['date'=>$adv['adv_date'], 'desc'=>$adv['type'].' - '.($adv['description']??''), 'debit'=>$adv['amount'], 'credit'=>0];
            }

            // Compute running balance
            $balance = $opening_balance;
            $total_debit = 0; $total_credit = 0;
            foreach ($transactions as &$t) {
                $balance += $t['credit'] - $t['debit'];
                $t['balance'] = $balance;
                $total_debit += $t['debit'];
                $total_credit += $t['credit'];
            }
            $final_balance = $opening_balance + $total_credit - $total_debit;
        ?>

        <!-- Statement Preview -->
        <div style="border:2px solid #ddd;border-radius:10px;overflow:hidden;max-width:900px;">
            <div style="background:linear-gradient(135deg,var(--primary-dark),var(--primary));color:#fff;padding:20px 25px;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:22px;font-weight:900;"><i class="fas fa-bread-slice"></i> مخابز الشام للخبز العربي</div>
                    <div style="font-size:12px;opacity:0.8;">Maamil Al Sham - Arabic Bread</div>
                </div>
                <div style="text-align:left;">
                    <div style="font-size:16px;font-weight:700;">كشف حساب موظف</div>
                    <div style="font-size:12px;opacity:0.8;"><?php echo $month; ?></div>
                </div>
            </div>

            <div style="padding:20px;background:#fafafa;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                    <div style="background:var(--cream);border-radius:8px;padding:15px;border:1px solid #e5d4b0;">
                        <div style="font-size:13px;color:#888;margin-bottom:3px;">اسم الموظف</div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars($emp['name']); ?></div>
                        <div style="font-size:12px;color:#888;margin-top:8px;">رقم الموظف: <strong><?php echo $emp['emp_number']; ?></strong></div>
                        <div style="font-size:12px;color:#888;">القسم: <strong><?php echo htmlspecialchars($emp['department'] ?? '-'); ?></strong></div>
                        <div style="font-size:12px;color:#888;">الوظيفة: <strong><?php echo htmlspecialchars($emp['position'] ?? '-'); ?></strong></div>
                    </div>
                    <div style="background:var(--cream);border-radius:8px;padding:15px;border:1px solid #e5d4b0;">
                        <div style="font-size:13px;color:#888;margin-bottom:3px;">ملخص الشهر</div>
                        <div style="font-size:12px;color:#333;margin-bottom:5px;">الشهر: <strong><?php echo $month; ?></strong></div>
                        <div style="font-size:12px;">حضور: <strong style="color:#16a34a;"><?php echo $att_summary['حضور'] ?? 0; ?></strong> يوم</div>
                        <div style="font-size:12px;">غياب: <strong style="color:var(--primary);"><?php echo $att_summary['غياب'] ?? 0; ?></strong> يوم</div>
                        <div style="font-size:12px;">إجازات: <strong style="color:#2563eb;"><?php echo ($att_summary['إجازة'] ?? 0) + ($att_summary['إجازة مرضية'] ?? 0); ?></strong> يوم</div>
                        <div style="font-size:12px;margin-top:5px;">الرصيد السابق: <strong><?php echo number_format($opening_balance,3); ?> د.أ</strong></div>
                    </div>
                </div>

                <table class="admin-table" style="margin-bottom:15px;">
                    <thead style="background:var(--primary);color:#fff;">
                        <tr>
                            <th style="color:#fff;">م</th>
                            <th style="color:#fff;">التاريخ</th>
                            <th style="color:#fff;">البيان</th>
                            <th style="color:#fff;">مدين (خصم)</th>
                            <th style="color:#fff;">دائن (إضافة)</th>
                            <th style="color:#fff;">الرصيد</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($transactions)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#aaa;padding:30px;">لا توجد حركات هذا الشهر</td></tr>
                    <?php else: foreach ($transactions as $i => $t): ?>
                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td><?php echo $t['date']; ?></td>
                        <td><?php echo htmlspecialchars($t['desc']); ?></td>
                        <td style="color:var(--primary);"><?php echo $t['debit'] > 0 ? number_format($t['debit'],3) : '-'; ?></td>
                        <td style="color:#16a34a;"><?php echo $t['credit'] > 0 ? number_format($t['credit'],3) : '-'; ?></td>
                        <td style="font-weight:700;<?php echo $t['balance'] >= 0 ? 'color:#16a34a;' : 'color:var(--primary);'; ?>"><?php echo number_format($t['balance'],3); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                    <tr style="background:var(--primary);color:#fff;font-weight:700;">
                        <td colspan="3" style="text-align:center;">الإجمالي</td>
                        <td><?php echo number_format($total_debit,3); ?></td>
                        <td><?php echo number_format($total_credit,3); ?></td>
                        <td><?php echo number_format($final_balance,3); ?></td>
                    </tr>
                    </tbody>
                </table>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:15px;">
                    <div style="background:#f0f9ff;border-radius:8px;padding:12px;text-align:center;border:1px solid #bae6fd;">
                        <div style="font-size:12px;color:#0369a1;">الرصيد السابق</div>
                        <div style="font-size:18px;font-weight:700;color:#0369a1;"><?php echo number_format($opening_balance,3); ?></div>
                    </div>
                    <div style="background:#fff8ef;border-radius:8px;padding:12px;text-align:center;border:1px solid #e5d4b0;">
                        <div style="font-size:12px;color:var(--gold-dark);">إجمالي الحركة</div>
                        <div style="font-size:18px;font-weight:700;color:var(--gold-dark);"><?php echo number_format($total_credit - $total_debit,3); ?></div>
                    </div>
                    <div style="background:<?php echo $final_balance >= 0 ? '#d1fae5' : '#fee2e2'; ?>;border-radius:8px;padding:12px;text-align:center;border:1px solid <?php echo $final_balance >= 0 ? '#6ee7b7' : '#fca5a5'; ?>;">
                        <div style="font-size:12px;color:<?php echo $final_balance >= 0 ? '#065f46' : '#991b1b'; ?>;">الرصيد الحالي</div>
                        <div style="font-size:18px;font-weight:700;color:<?php echo $final_balance >= 0 ? '#065f46' : '#991b1b'; ?>"><?php echo number_format($final_balance,3); ?></div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;justify-content:center;">
                    <a href="hr-statement-pdf.php?emp_id=<?php echo $emp_id; ?>&month=<?php echo $month; ?>" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> طباعة / تصدير PDF</a>
                    <a href="hr-salaries.php?emp_id=<?php echo $emp_id; ?>" class="btn btn-gold"><i class="fas fa-money-bill-wave"></i> الرواتب</a>
                    <a href="hr-advances.php?emp_id=<?php echo $emp_id; ?>" class="btn btn-dark"><i class="fas fa-hand-holding-usd"></i> السلف</a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="empty-state"><i class="fas fa-file-alt"></i><p>اختر موظفاً وشهراً لعرض كشف الحساب</p></div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
