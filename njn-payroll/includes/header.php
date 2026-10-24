<?php $u = user(); ?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e(SHOP) ?></title>
<link rel="stylesheet" href="assets/style.css"></head><body>
<header><b><?= e(SHOP) ?></b><?php if ($u): ?><span><?= e($u['name']) ?> · <a href="logout.php">Logout</a></span><?php endif; ?></header>
<?php if ($u): ?><nav><a href="index.php">Clock</a><a href="payslip.php">Payslips</a>
<?php if ($u['role'] === 'admin'): ?><a href="employees.php">Employees</a><a href="attendance.php">Attendance</a><a href="payroll.php">Payroll</a><?php endif; ?></nav><?php endif; ?>
<main>