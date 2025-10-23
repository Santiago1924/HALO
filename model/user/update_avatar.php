<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  exit('no_session');
}

require_once("../../database/conexion.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_POST['id_avatar'])) {
  exit('invalid');
}

$id_avatar = intval($_POST['id_avatar']);
$username = $_SESSION['usuario'];

try {
  $stmt = $con->prepare("UPDATE users SET id_avatar = ? WHERE username = ?");
  $ok = $stmt->execute([$id_avatar, $username]);
  echo $ok ? 'ok' : 'error';
} catch (Exception $e) {
  echo 'ex: ' . $e->getMessage();
}
