<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

$id_lobby = intval($_POST['id_lobby']);

// ✅ Cambiar estado de la sala a iniciada
$conexion->query("UPDATE game_lobby SET started = 1 WHERE id_lobby = $id_lobby");

// Esperar medio segundo para asegurar que el estado se guarde
usleep(500000);

// Redirigir al jugador que inició la partida
header("Location: ../model/user/battle.php");
exit();
?>
