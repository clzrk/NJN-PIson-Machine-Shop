<?php
// ---- EDIT THESE (use your hosting's database details) ----
const DB_HOST = 'localhost';
const DB_NAME = 'njn_payroll';
const DB_USER = 'root';
const DB_PASS = '';
// ---- Payroll rules ----
const SHOP = 'NJN Pison Machine Shop';
const BREAK_HOURS = 1;     // unpaid break deducted from days longer than 5 hrs
const OT_MULT = 1.25;      // overtime multiplier (hours beyond 8/day)

date_default_timezone_set('Asia/Manila');
session_start();
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS,
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$pdo->exec("SET time_zone = '+08:00'");

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso($n){ return '₱'.number_format((float)$n, 2); }
function user(){ return $_SESSION['u'] ?? null; }
function need($role = null){
  if (!user()) { header('Location: login.php'); exit; }
  if ($role && user()['role'] !== $role) { http_response_code(403); exit('Not allowed.'); }
}
function csrf(){
  $_SESSION['t'] = $_SESSION['t'] ?? bin2hex(random_bytes(16));
  return '<input type="hidden" name="t" value="'.$_SESSION['t'].'">';
}
function check(){
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($_SESSION['t'] ?? '', $_POST['t'] ?? '')) exit('Bad request.');
}
// Returns [days, regular hours, overtime hours] for one employee in a date range
function hours_for($pdo, $emp, $from, $to){
  $st = $pdo->prepare("SELECT work_date, SUM(TIMESTAMPDIFF(MINUTE,time_in,time_out)) m FROM attendance
    WHERE employee_id=? AND work_date BETWEEN ? AND ? AND time_out IS NOT NULL GROUP BY work_date");
  $st->execute([$emp, $from, $to]);
  $days = 0; $reg = 0; $ot = 0;
  foreach ($st as $r) {
    $h = $r['m'] / 60; if ($h > 5) $h -= BREAK_HOURS; $h = max($h, 0);
    $reg += min($h, 8); $ot += max($h - 8, 0); $days++;
  }
  return [$days, round($reg, 2), round($ot, 2)];
}
function gross_pay($rate, $reg, $ot){ $hr = $rate / 8; return round($hr * $reg + $hr * OT_MULT * $ot, 2); }