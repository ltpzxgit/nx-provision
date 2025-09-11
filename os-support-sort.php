<?php
require_once 'auth_check.php';
// เช็คสิทธิ์ Admin
session_start();
$host = 'localhost'; $dbname = 'nxprovisiondb'; $username = 'root'; $password = '';
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$currentUser = null;
if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT role FROM users WHERE id=?");
  $stmt->execute([$_SESSION['user_id']]);
  $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!$currentUser || $currentUser['role'] !== 'Admin') {
  http_response_code(403); echo "Forbidden"; exit;
}

// รับข้อมูล JSON array ของ id
$data = json_decode(file_get_contents('php://input'), true);
if (is_array($data)) {
  foreach ($data as $order => $id) {
    $stmt = $pdo->prepare("UPDATE os_support SET sort_order=? WHERE id=?");
    $stmt->execute([$order, $id]);
  }
  echo json_encode(['status'=>'ok']);
  exit;
}
http_response_code(400); echo "Bad Request"; exit;
?>