<?php require 'config.php'; need('admin'); check();
$from = $_REQUEST['from'] ?? date('Y-m-01'); $to = $_REQUEST['to'] ?? date('Y-m-d');
$emps = $pdo->query('SELECT * FROM employees WHERE active=1 AND daily_rate>0 ORDER BY name')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pdo->prepare('DELETE FROM payslips WHERE period_start=? AND period_end=?')->execute([$from, $to]);
  $ins = $pdo->prepare('INSERT INTO payslips (employee_id,period_start,period_end,days,reg_hours,ot_hours,gross,bonus,sss,philhealth,pagibig,other_ded,net) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
  foreach ($emps as $e) {
    [$d, $r, $o] = hours_for($pdo, $e['id'], $from, $to); $g = gross_pay($e['daily_rate'], $r, $o);
    $x = $_POST['d'][$e['id']] ?? [];
    $b = (float)($x['bo'] ?? 0); $s = (float)($x['sss'] ?? 0); $ph = (float)($x['ph'] ?? 0); $pg = (float)($x['pg'] ?? 0); $ot = (float)($x['ot'] ?? 0);
    $ins->execute([$e['id'], $from, $to, $d, $r, $o, $g, $b, $s, $ph, $pg, $ot, round($g + $b - $s - $ph - $pg - $ot, 2)]);
  }
  header('Location: payslip.php'); exit;
}
include 'includes/header.php'; ?>
<div class="card"><h2>Pay period</h2><form method="get" class="row"><div><label>From</label><input type="date" name="from" value="<?= e($from) ?>"></div>
<div><label>To</label><input type="date" name="to" value="<?= e($to) ?>"></div><div><button>Show</button></div></form></div>
<form method="post"><?= csrf() ?><input type="hidden" name="from" value="<?= e($from) ?>"><input type="hidden" name="to" value="<?= e($to) ?>">
<?php foreach ($emps as $e): [$d, $r, $o] = hours_for($pdo, $e['id'], $from, $to); $g = gross_pay($e['daily_rate'], $r, $o); ?>
<div class="card"><h2><?= e($e['name']) ?> <span class="mu"><?= e($e['position']) ?></span></h2>
<p class="mu"><?= $d ?> days · <?= $r ?> reg hrs · <?= $o ?> OT hrs · Gross <b><?= peso($g) ?></b></p>
<label>Bonus / additional pay</label><input type="number" step="0.01" name="d[<?= $e['id'] ?>][bo]" value="0">
<div class="row3"><div><label>SSS</label><input type="number" step="0.01" name="d[<?= $e['id'] ?>][sss]" value="<?= e($e['sss']) ?>"></div>
<div><label>PhilHealth</label><input type="number" step="0.01" name="d[<?= $e['id'] ?>][ph]" value="<?= e($e['philhealth']) ?>"></div>
<div><label>Pag-IBIG</label><input type="number" step="0.01" name="d[<?= $e['id'] ?>][pg]" value="<?= e($e['pagibig']) ?>"></div></div>
<label>Other deductions (cash advance, etc.)</label><input type="number" step="0.01" name="d[<?= $e['id'] ?>][ot]" value="0"></div>
<?php endforeach; ?>
<?php if ($emps): ?><p><button onclick="return confirm('Save payslips for this period? Existing ones for the same period are replaced.')">Save payroll</button></p><?php else: ?><p class="mu">No active employees with a daily rate.</p><?php endif; ?></form>
<?php include 'includes/footer.php';