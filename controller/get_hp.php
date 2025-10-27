<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
  die(json_encode(["error" => "Error de conexión"]));
}

$username = $_SESSION['usuario'];

// Buscar jugador actual
$player = $conexion->query("
  SELECT u.id_user, rp.current_hp
  FROM users u
  JOIN room_players rp ON rp.id_user = u.id_user
  WHERE u.username = '$username'
")->fetch_assoc();

$id_user = $player['id_user'];
$player_hp = $player['current_hp'];

// Buscar enemigo
$enemy = $conexion->query("
  SELECT rp.current_hp
  FROM room_players rp
  JOIN users u ON rp.id_user = u.id_user
  WHERE u.id_user != $id_user
  LIMIT 1
")->fetch_assoc();

$enemy_hp = $enemy['current_hp'] ?? 100;

echo json_encode([
  "player_hp" => $player_hp,
  "enemy_hp" => $enemy_hp
]);
?>
