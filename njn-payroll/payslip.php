<?php require 'config.php'; need(); $u = user();
if (isset($_GET['id'])) {
  $s = $pdo->prepare('SELECT p.*, e.name, e.position, e.daily_rate FROM payslips p JOIN employees e ON e.id=p.employee_id WHERE p.id=?'); $s->execute([(int)$_GET['id']]); $p = $s->fetch();
  if (!$p || ($u['role'] !== 'admin' && $p['employee_id'] != $u['id'])) { http_response_code(404); exit('Not found.'); }
  include 'includes/header.php'; ?>
<div class="card"><h2>Payslip</h2><p><b><?= e($p['name']) ?></b><br><span class="mu"><?= e($p['position']) ?></span><br>Period: <?= e($p['period_start']) ?> to <?= e($p['period_end']) ?></p>
<table><tr><td>Days worked</td><td><?= $p['days'] ?></td></tr><tr><td>Regular hours</td><td><?= $p['reg_hours'] ?></td></tr><tr><td>Overtime hours</td><td><?= $p['ot_hours'] ?></td></tr>
<tr><td>Daily rate</td><td><?= peso($p['daily_rate']) ?></td></tr><tr><td><b>Gross pay</b></td><td><b><?= peso($p['gross']) ?></b></td></tr>
<tr><td>Bonus / additional pay</td><td>+<?= peso($p['bonus']) ?></td></tr>
<tr><td>SSS</td><td>-<?= peso($p['sss']) ?></td></tr><tr><td>PhilHealth</td><td>-<?= peso($p['philhealth']) ?></td></tr>
<tr><td>Pag-IBIG</td><td>-<?= peso($p['pagibig']) ?></td></tr><tr><td>Other deductions</td><td>-<?= peso($p['other_ded']) ?></td></tr>
<tr><td><b>Net pay</b></td><td class="net"><?= peso($p['net']) ?></td></tr></table>
<p><button onclick="window.print()">Print</button> <a href="payslip.php">Back</a></p></div>
<?php include 'includes/footer.php'; exit; }
$q = 'SELECT p.id,p.period_start,p.period_end,p.net,e.name FROM payslips p JOIN employees e ON e.id=p.employee_id';
if ($u['role'] === 'admin') $st = $pdo->query($q.' ORDER BY p.period_end DESC, e.name LIMIT 200');
else { $st = $pdo->prepare($q.' WHERE p.employee_id=? ORDER BY p.period_end DESC LIMIT 50'); $st->execute([$u['id']]); }
include 'includes/header.php'; ?>
<div class="card"><h2>Payslips</h2><div class="scroll"><table><tr><th>Employee</th><th>Period</th><th>Net</th><th></th></tr>
<?php foreach ($st as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= e($p['period_start']) ?> – <?= e($p['period_end']) ?></td><td><?= peso($p['net']) ?></td><td><a href="?id=<?= $p['id'] ?>">View</a></td></tr><?php endforeach; ?></table></div></div>
<?php include 'includes/footer.php';