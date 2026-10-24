<?php require 'config.php'; need('admin'); check(); $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $f = [trim($_POST['name']), trim($_POST['position']), trim($_POST['username']), $_POST['role'] === 'admin' ? 'admin' : 'employee',
        (float)$_POST['daily_rate'], (float)$_POST['sss'], (float)$_POST['philhealth'], (float)$_POST['pagibig'], isset($_POST['active']) ? 1 : 0];
  try {
    if (!empty($_POST['id'])) {
      $pdo->prepare('UPDATE employees SET name=?,position=?,username=?,role=?,daily_rate=?,sss=?,philhealth=?,pagibig=?,active=? WHERE id=?')->execute([...$f, (int)$_POST['id']]);
      if ($_POST['password'] !== '') $pdo->prepare('UPDATE employees SET password_hash=? WHERE id=?')->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), (int)$_POST['id']]);
    } else {
      if (strlen($_POST['password']) < 6) throw new Exception('Password must be at least 6 characters.');
      $pdo->prepare('INSERT INTO employees (name,position,username,role,daily_rate,sss,philhealth,pagibig,active,password_hash) VALUES (?,?,?,?,?,?,?,?,?,?)')
          ->execute([...$f, password_hash($_POST['password'], PASSWORD_DEFAULT)]);
    }
    header('Location: employees.php'); exit;
  } catch (PDOException $x) { $err = 'That username is already in use.'; }
    catch (Exception $x) { $err = $x->getMessage(); }
}
$v = [];
if (isset($_GET['edit'])) { $s = $pdo->prepare('SELECT * FROM employees WHERE id=?'); $s->execute([(int)$_GET['edit']]); $v = $s->fetch() ?: []; }
$list = $pdo->query('SELECT * FROM employees ORDER BY active DESC, name')->fetchAll();
include 'includes/header.php'; ?>
<div class="card"><h2><?= $v ? 'Edit employee' : 'Add employee' ?></h2><?php if ($err) echo '<p class="err">'.e($err).'</p>'; ?>
<form method="post"><?= csrf() ?><input type="hidden" name="id" value="<?= e($v['id'] ?? '') ?>">
<label>Full name</label><input name="name" required value="<?= e($v['name'] ?? '') ?>">
<label>Position</label><input name="position" value="<?= e($v['position'] ?? '') ?>">
<div class="row"><div><label>Username</label><input name="username" required value="<?= e($v['username'] ?? '') ?>"></div>
<div><label>Password <?= $v ? '(blank = keep)' : '' ?></label><input name="password" type="password"></div></div>
<div class="row"><div><label>Role</label><select name="role"><option value="employee">Employee</option><option value="admin" <?= ($v['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option></select></div>
<div><label>Daily rate (₱, 8 hrs)</label><input name="daily_rate" type="number" step="0.01" value="<?= e($v['daily_rate'] ?? 0) ?>"></div></div>
<p class="mu">Default deductions per payroll period (editable when running payroll):</p>
<div class="row3"><div><label>SSS</label><input name="sss" type="number" step="0.01" value="<?= e($v['sss'] ?? 0) ?>"></div>
<div><label>PhilHealth</label><input name="philhealth" type="number" step="0.01" value="<?= e($v['philhealth'] ?? 0) ?>"></div>
<div><label>Pag-IBIG</label><input name="pagibig" type="number" step="0.01" value="<?= e($v['pagibig'] ?? 0) ?>"></div></div>
<label><input type="checkbox" name="active" style="width:auto" <?= !$v || $v['active'] ? 'checked' : '' ?>> Active</label>
<p><button>Save</button> <?php if ($v) echo '<a href="employees.php">Cancel</a>'; ?></p></form></div>
<div class="card"><h2>Employees</h2><div class="scroll"><table><tr><th>Name</th><th>Position</th><th>Rate</th><th>Role</th><th></th></tr>
<?php foreach ($list as $x): ?><tr class="<?= $x['active'] ? '' : 'off' ?>"><td><?= e($x['name']) ?></td><td><?= e($x['position']) ?></td><td><?= peso($x['daily_rate']) ?></td><td><?= e($x['role']) ?></td><td><a href="?edit=<?= $x['id'] ?>">Edit</a></td></tr><?php endforeach; ?></table></div></div>
<?php include 'includes/footer.php';