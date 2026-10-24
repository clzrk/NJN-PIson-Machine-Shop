<?php require 'config.php'; need('admin'); check();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'del') $pdo->prepare('DELETE FROM attendance WHERE id=?')->execute([(int)$_POST['id']]);
  if (($_POST['action'] ?? '') === 'add') {
    $d = $_POST['date']; $in = "$d {$_POST['tin']}:00"; $out = $_POST['tout'] ? "$d {$_POST['tout']}:00" : null;
    if ($out && $out <= $in) $out = date('Y-m-d H:i:s', strtotime("$out +1 day"));
    $pdo->prepare('INSERT INTO attendance (employee_id,work_date,time_in,time_out) VALUES (?,?,?,?)')->execute([(int)$_POST['emp'], $d, $in, $out]);
  }
  header('Location: attendance.php'); exit;
}
$from = $_GET['from'] ?? date('Y-m-01'); $to = $_GET['to'] ?? date('Y-m-d'); $emp = (int)($_GET['emp'] ?? 0);
$emps = $pdo->query('SELECT id,name FROM employees WHERE active=1 ORDER BY name')->fetchAll();
$sql = 'SELECT a.*, e.name FROM attendance a JOIN employees e ON e.id=a.employee_id WHERE a.work_date BETWEEN ? AND ?'; $p = [$from, $to];
if ($emp) { $sql .= ' AND a.employee_id=?'; $p[] = $emp; }
$st = $pdo->prepare($sql.' ORDER BY a.time_in DESC LIMIT 300'); $st->execute($p);
include 'includes/header.php'; ?>
<div class="card"><h2>Add record (missed punch)</h2><form method="post"><?= csrf() ?><input type="hidden" name="action" value="add">
<label>Employee</label><select name="emp"><?php foreach ($emps as $x) echo '<option value="'.$x['id'].'">'.e($x['name']).'</option>'; ?></select>
<div class="row3"><div><label>Date</label><input type="date" name="date" value="<?= date('Y-m-d') ?>" required></div>
<div><label>In</label><input type="time" name="tin" value="08:00" required></div><div><label>Out</label><input type="time" name="tout" value="17:00"></div></div>
<p><button>Add</button></p></form></div>
<div class="card"><h2>Records</h2><form method="get" class="row3"><div><label>From</label><input type="date" name="from" value="<?= e($from) ?>"></div>
<div><label>To</label><input type="date" name="to" value="<?= e($to) ?>"></div>
<div><label>Employee</label><select name="emp"><option value="0">All</option><?php foreach ($emps as $x) echo '<option value="'.$x['id'].'"'.($emp == $x['id'] ? ' selected' : '').'>'.e($x['name']).'</option>'; ?></select></div>
<div><button>Filter</button></div></form>
<div class="scroll"><table><tr><th>Employee</th><th>Date</th><th>In</th><th>Out</th><th>Hrs</th><th></th></tr>
<?php foreach ($st as $a): ?><tr><td><?= e($a['name']) ?></td><td><?= e($a['work_date']) ?></td><td><?= date('g:i A', strtotime($a['time_in'])) ?></td>
<td><?= $a['time_out'] ? date('g:i A', strtotime($a['time_out'])) : '–' ?></td>
<td><?= $a['time_out'] ? round((strtotime($a['time_out']) - strtotime($a['time_in'])) / 3600, 2) : '–' ?></td>
<td><form method="post" onsubmit="return confirm('Delete this record?')"><?= csrf() ?><input type="hidden" name="action" value="del"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="sm">✕</button></form></td></tr><?php endforeach; ?></table></div></div>
<?php include 'includes/footer.php';