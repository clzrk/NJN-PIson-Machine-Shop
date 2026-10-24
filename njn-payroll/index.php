<?php
     require 'config.php'; need(); $id = user()['id'];
    $o = $pdo->prepare('SELECT id FROM attendance WHERE employee_id=? AND time_out IS NULL ORDER BY id DESC LIMIT 1'); $o->execute([$id]); $open = $o->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { check();
  if ($open) $pdo->prepare('UPDATE attendance SET time_out=NOW() WHERE id=?')->execute([$open['id']]);
  else $pdo->prepare('INSERT INTO attendance (employee_id,work_date,time_in) VALUES (?,CURDATE(),NOW())')->execute([$id]);
  header('Location: index.php'); exit;
    }
    $r = $pdo->prepare('SELECT * FROM attendance WHERE employee_id=? ORDER BY time_in DESC LIMIT 10'); $r->execute([$id]);
    include 'includes/header.php';  
    ?>
<div class="card center"><h2><?= date('l, F j, Y') ?></h2><p class="big"><?= date('g:i A') ?></p>
<p class="mu"><?= $open ? 'You are clocked in.' : 'You are not clocked in.' ?></p>
<form method="post"><?= csrf() ?><button class="<?= $open ? 'out' : 'in' ?>"><?= $open ? 'Time out' : 'Time in' ?></button></form></div>
<div class="card"><h2>My recent attendance</h2><div class="scroll"><table><tr><th>Date</th><th>In</th><th>Out</th><th>Hrs</th></tr>
<?php foreach ($r as $a): ?><tr><td><?= e($a['work_date']) ?></td><td><?= date('g:i A', strtotime($a['time_in'])) ?></td>
<td><?= $a['time_out'] ? date('g:i A', strtotime($a['time_out'])) : '–' ?></td>
<td><?= $a['time_out'] ? round((strtotime($a['time_out']) - strtotime($a['time_in'])) / 3600, 2) : '–' ?></td></tr><?php endforeach; ?></table></div></div>
<?php include 'includes/footer.php'; 

    ?>