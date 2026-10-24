<?php require 'config.php'; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') { check();
  $s = $pdo->prepare('SELECT * FROM employees WHERE username=? AND active=1'); $s->execute([trim($_POST['username'])]); $u = $s->fetch();
  if ($u && password_verify($_POST['password'], $u['password_hash'])) {
    session_regenerate_id(true);
    $_SESSION['u'] = ['id' => $u['id'], 'name' => $u['name'], 'role' => $u['role']];
    header('Location: index.php'); exit;
  }
  $err = 'Wrong username or password.';
}
include 'includes/header.php'; ?>
<div class="card"><h2>Log in</h2><?php if ($err) echo '<p class="err">'.e($err).'</p>'; ?>
<form method="post"><?= csrf() ?><label>Username</label><input name="username" required autofocus>
<label>Password</label><input name="password" type="password" required><p><button>Log in</button></p></form></div>
<?php include 'includes/footer.php';