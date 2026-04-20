<?php
$admin_title = 'إنشاء سند تسليم';
$admin_icon  = 'file-invoice';
require_once __DIR__ . '/../includes/admin-check.php';

$id       = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$cust_id  = isset($_GET['cust_id']) && is_numeric($_GET['cust_id']) ? (int)$_GET['cust_id'] : 0;

$customers = $pdo->query("SELECT id,name,type,phone FROM dist_customers WHERE status='active' ORDER BY name")->fetchAll();
$products  = $pdo->query("SELECT * FROM dist_products WHERE active=1 ORDER BY name")->fetchAll();

// Load existing receipt
$receipt = null;
$items   = [];
if ($id) {
    $s = $pdo->prepare("SELECT r.*, c.name as cust_name FROM dist_receipts r JOIN dist_customers c ON r.cust_id=c.id WHERE r.id=?");
    $s->execute([$id]);
    $receipt = $s->fetch();
    if ($receipt) {
        $si = $pdo->prepare("SELECT ri.*, p.name as prod_name, p.unit FROM dist_receipt_items ri JOIN dist_products p ON ri.product_id=p.id WHERE ri.receipt_id=?");
        $si->execute([$id]);
        $items = $si->fetchAll();
    }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid      = (int)($_POST['cust_id'] ?? 0);
    $r_date   = $_POST['receipt_date'] ?? date('Y-m-d');
    $notes    = trim($_POST['notes'] ?? '');
    $prod_ids = $_POST['product_id'] ?? [];
    $qtys     = $_POST['quantity'] ?? [];
    $prices   = $_POST['unit_price'] ?? [];

    if (!$cid) $errors[] = 'يجب اختيار العميل';
    if (empty($prod_ids)) $errors[] = 'يجب إضافة منتج واحد على الأقل';

    if (empty($errors)) {
        $total = 0;
        $line_items = [];
        foreach ($prod_ids as $idx => $pid) {
            if (!$pid) continue;
            $qty = (float)($qtys[$idx] ?? 0);
            $price = (float)($prices[$idx] ?? 0);
            $line_total = $qty * $price;
            $total += $line_total;
            $line_items[] = ['product_id'=>(int)$pid, 'quantity'=>$qty, 'unit_price'=>$price, 'total'=>$line_total];
        }

        if ($id) {
            $pdo->prepare("UPDATE dist_receipts SET cust_id=?,receipt_date=?,total_amount=?,notes=? WHERE id=?")
                ->execute([$cid,$r_date,$total,$notes,$id]);
            $pdo->prepare("DELETE FROM dist_receipt_items WHERE receipt_id=?")->execute([$id]);
            $receipt_id = $id;
        } else {
            $last_num = $pdo->query("SELECT COUNT(*) FROM dist_receipts")->fetchColumn();
            $receipt_num = 'SND-' . date('Ymd') . '-' . str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
            $pdo->prepare("INSERT INTO dist_receipts (receipt_num,cust_id,receipt_date,total_amount,notes,created_by) VALUES (?,?,?,?,?,?)")
                ->execute([$receipt_num,$cid,$r_date,$total,$notes,$_SESSION['admin_email']??'admin']);
            $receipt_id = $pdo->lastInsertId();
        }

        $si = $pdo->prepare("INSERT INTO dist_receipt_items (receipt_id,product_id,quantity,unit_price,total) VALUES (?,?,?,?,?)");
        foreach ($line_items as $li) {
            $si->execute([$receipt_id,$li['product_id'],$li['quantity'],$li['unit_price'],$li['total']]);
        }

        header("Location: dist-receipt-pdf.php?id=$receipt_id&new=1"); exit;
    }
}

// Get selected customer
$sel_cust = null;
if ($cust_id) {
    $sc = $pdo->prepare("SELECT * FROM dist_customers WHERE id=?");
    $sc->execute([$cust_id]);
    $sel_cust = $sc->fetch();
} elseif ($receipt) {
    $cust_id = $receipt['cust_id'];
    $sc = $pdo->prepare("SELECT * FROM dist_customers WHERE id=?");
    $sc->execute([$cust_id]);
    $sel_cust = $sc->fetch();
}

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fas fa-file-invoice"></i> <?php echo $id ? 'تعديل سند' : 'إنشاء سند تسليم جديد'; ?></h3>
        <a href="dist-receipts.php" class="btn btn-dark btn-sm"><i class="fas fa-arrow-right"></i> رجوع</a>
    </div>
    <div class="admin-card-body">
        <?php foreach ($errors as $e): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $e; ?></div><?php endforeach; ?>

        <form method="post" id="receiptForm">
            <div class="form-row">
                <div class="form-group">
                    <label>العميل / المطعم / المحل <span style="color:var(--primary)">*</span></label>
                    <select name="cust_id" id="custSelect" required onchange="loadCustomerInfo(this.value)">
                        <option value="">اختر العميل</option>
                        <?php foreach ($customers as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php if ($cust_id==$c['id']) echo 'selected'; ?> data-phone="<?php echo htmlspecialchars($c['phone']??''); ?>" data-type="<?php echo htmlspecialchars($c['type']); ?>">
                            <?php echo htmlspecialchars($c['name']); ?> (<?php echo htmlspecialchars($c['type']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>تاريخ السند</label>
                    <input type="date" name="receipt_date" value="<?php echo $receipt['receipt_date'] ?? date('Y-m-d'); ?>" required>
                </div>
            </div>

            <!-- Customer Info Display -->
            <div id="custInfo" style="background:var(--cream);border-radius:8px;padding:13px 18px;margin-bottom:18px;display:<?php echo $sel_cust ? 'block' : 'none'; ?>;border:1px solid #e5d4b0;">
                <strong id="custName"><?php echo htmlspecialchars($sel_cust['name'] ?? ''); ?></strong>
                <span id="custType" style="margin-right:10px;font-size:13px;color:#888;"><?php echo htmlspecialchars($sel_cust['type'] ?? ''); ?></span>
                <span id="custPhone" style="margin-right:10px;font-size:13px;color:var(--primary);"><?php echo htmlspecialchars($sel_cust['phone'] ?? ''); ?></span>
            </div>

            <!-- Products Table -->
            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h4 style="font-size:15px;font-weight:700;"><i class="fas fa-bread-slice" style="color:var(--primary);"></i> المنتجات</h4>
                    <button type="button" onclick="addRow()" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> إضافة منتج</button>
                </div>
                <div class="table-responsive">
                <table class="admin-table" id="itemsTable">
                    <thead><tr><th>#</th><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th><th>حذف</th></tr></thead>
                    <tbody id="itemsBody">
                        <?php
                        $init_items = !empty($items) ? $items : [['product_id'=>0,'quantity'=>1,'unit_price'=>0,'total'=>0]];
                        foreach ($init_items as $idx => $item):
                        ?>
                        <tr id="row_<?php echo $idx; ?>">
                            <td><?php echo $idx+1; ?></td>
                            <td>
                                <select name="product_id[]" onchange="fillPrice(this, <?php echo $idx; ?>)" required style="padding:7px 10px;border:1.5px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;min-width:180px;">
                                    <option value="">اختر المنتج</option>
                                    <?php foreach ($products as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" data-unit="<?php echo htmlspecialchars($p['unit']); ?>" <?php if (($item['product_id']??0)==$p['id']) echo 'selected'; ?>><?php echo htmlspecialchars($p['name']); ?> (<?php echo number_format($p['price'],3); ?> د.أ/<?php echo htmlspecialchars($p['unit']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" id="qty_<?php echo $idx; ?>" step="0.001" min="0.001" value="<?php echo $item['quantity']??1; ?>" oninput="calcRow(<?php echo $idx; ?>)" style="padding:7px 10px;border:1.5px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;width:90px;"></td>
                            <td><input type="number" name="unit_price[]" id="price_<?php echo $idx; ?>" step="0.001" min="0" value="<?php echo $item['unit_price']??0; ?>" oninput="calcRow(<?php echo $idx; ?>)" style="padding:7px 10px;border:1.5px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;width:100px;"></td>
                            <td><input type="number" id="total_<?php echo $idx; ?>" value="<?php echo $item['total']??0; ?>" readonly style="padding:7px 10px;border:1.5px solid #eee;border-radius:5px;font-family:Cairo,sans-serif;width:100px;background:#f9f9f9;font-weight:700;"></td>
                            <td><button type="button" onclick="removeRow('row_<?php echo $idx; ?>')" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>

            <!-- Total -->
            <div style="text-align:left;background:var(--cream);border-radius:8px;padding:15px 20px;margin-bottom:18px;border:1.5px solid #e5d4b0;">
                <div style="font-size:14px;color:#888;margin-bottom:4px;">المجموع الكلي</div>
                <div style="font-size:28px;font-weight:900;color:var(--primary);" id="grandTotal">0.000 د.أ</div>
            </div>

            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes" rows="2"><?php echo htmlspecialchars($receipt['notes'] ?? ''); ?></textarea>
            </div>

            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-file-invoice"></i> <?php echo $id ? 'حفظ التعديل' : 'إنشاء السند وطباعته'; ?></button>
                <a href="dist-receipts.php" class="btn btn-dark">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<script>
var rowCount = <?php echo count($init_items); ?>;

var customers = <?php echo json_encode(array_reduce($customers, function($carry, $c) {
    $carry[$c['id']] = ['name'=>$c['name'],'type'=>$c['type'],'phone'=>$c['phone']??''];
    return $carry;
}, [])); ?>;

function loadCustomerInfo(id) {
    var c = customers[id];
    if (c) {
        document.getElementById('custName').textContent = c.name;
        document.getElementById('custType').textContent = c.type;
        document.getElementById('custPhone').textContent = c.phone;
        document.getElementById('custInfo').style.display = 'block';
    } else {
        document.getElementById('custInfo').style.display = 'none';
    }
}

function fillPrice(sel, idx) {
    var opt = sel.options[sel.selectedIndex];
    var price = opt.getAttribute('data-price') || 0;
    document.getElementById('price_' + idx).value = parseFloat(price).toFixed(3);
    calcRow(idx);
}

function calcRow(idx) {
    var qty   = parseFloat(document.getElementById('qty_' + idx).value) || 0;
    var price = parseFloat(document.getElementById('price_' + idx).value) || 0;
    var total = qty * price;
    document.getElementById('total_' + idx).value = total.toFixed(3);
    calcGrand();
}

function calcGrand() {
    var totals = document.querySelectorAll('[id^="total_"]');
    var grand = 0;
    totals.forEach(function(t) { grand += parseFloat(t.value) || 0; });
    document.getElementById('grandTotal').textContent = grand.toFixed(3) + ' د.أ';
}

function addRow() {
    var idx = rowCount++;
    var prods = <?php echo json_encode(array_map(fn($p) => ['id'=>$p['id'],'name'=>$p['name'],'price'=>$p['price'],'unit'=>$p['unit']], $products)); ?>;
    var opts = prods.map(p => `<option value="${p.id}" data-price="${p.price}" data-unit="${p.unit}">${p.name} (${parseFloat(p.price).toFixed(3)} د.أ/${p.unit})</option>`).join('');

    var tr = document.createElement('tr');
    tr.id = 'row_' + idx;
    tr.innerHTML = `
        <td>${idx+1}</td>
        <td><select name="product_id[]" onchange="fillPrice(this, ${idx})" required style="padding:7px 10px;border:1.5px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;min-width:180px;">
            <option value="">اختر المنتج</option>${opts}</select></td>
        <td><input type="number" name="quantity[]" id="qty_${idx}" step="0.001" min="0.001" value="1" oninput="calcRow(${idx})" style="padding:7px 10px;border:1.5px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;width:90px;"></td>
        <td><input type="number" name="unit_price[]" id="price_${idx}" step="0.001" min="0" value="0" oninput="calcRow(${idx})" style="padding:7px 10px;border:1.5px solid #ddd;border-radius:5px;font-family:Cairo,sans-serif;width:100px;"></td>
        <td><input type="number" id="total_${idx}" value="0" readonly style="padding:7px 10px;border:1.5px solid #eee;border-radius:5px;font-family:Cairo,sans-serif;width:100px;background:#f9f9f9;font-weight:700;"></td>
        <td><button type="button" onclick="removeRow('row_${idx}')" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button></td>
    `;
    document.getElementById('itemsBody').appendChild(tr);
    calcGrand();
}

function removeRow(rowId) {
    var row = document.getElementById(rowId);
    if (row) { row.remove(); calcGrand(); }
}

calcGrand();
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
