<?php
$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

$id_lobby = intval($_GET['id_lobby'] ?? 0);

$lobby = $conexion->query("SELECT started FROM game_lobby WHERE id_lobby = $id_lobby")->fetch_assoc();
$players = $conexion->query("SELECT COUNT(*) AS total FROM lobby_players WHERE id_lobby = $id_lobby")->fetch_assoc();

echo json_encode([
  "started" => $lobby['started'] ?? 0,
  "players" => $players['total'] ?? 0
]);
?>
