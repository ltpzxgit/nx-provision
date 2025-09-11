<?php
// os-support-delete.php
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

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $id = intval($_GET['id']);
  $stmt = $pdo->prepare("DELETE FROM os_support WHERE id=?");
  $stmt->execute([$id]);
}
header('Location: nx-mainboard.php?tab=ossupport');
exit;
?>