<?php
// os-support-edit.php
require_once 'auth_check.php';
$host = 'localhost'; $dbname = 'nxprovisiondb'; $username = 'root'; $password = '';
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("DB Connection failed: " . $e->getMessage()); }

// --- เช็คสิทธิ์ Admin ---
session_start();
$currentUser = null;
if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
  $stmt->execute([$_SESSION['user_id']]);
  $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$currentUser || $currentUser['role'] !== 'Admin') {
  http_response_code(403); echo "Forbidden"; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
  $os = $_POST['os'] ?? '';
  $new_provision = isset($_POST['new_provision']) ? intval($_POST['new_provision']) : 0;
  $deep = isset($_POST['deep']) ? intval($_POST['deep']) : 0;
  $zabbix_agent = isset($_POST['zabbix_agent']) ? intval($_POST['zabbix_agent']) : 0;
  $crowdstrike = isset($_POST['crowdstrike']) ? intval($_POST['crowdstrike']) : 0;
  $template = isset($_POST['template']) ? intval($_POST['template']) : 0;
  $other = $_POST['other'] ?? '';
  $eos = $_POST['eos'] ?? '';
  $std_nonstd = $_POST['std_nonstd'] ?? '';
  if ($id && $os) {
    $stmt = $pdo->prepare("UPDATE os_support SET os=?, new_provision=?, deep=?, zabbix_agent=?, crowdstrike=?, template=?, other=?, eos=?, std_nonstd=? WHERE id=?");
    $stmt->execute([$os, $new_provision, $deep, $zabbix_agent, $crowdstrike, $template, $other, $eos, $std_nonstd, $id]);
    header('Location: nx-mainboard.php?tab=ossupport');
    exit;
  }
}
header('Location: nx-mainboard.php?tab=ossupport');
exit;
?>