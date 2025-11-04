<?php
require_once("../../database/conexion.php");
session_start();

$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_user'])) {
  echo json_encode(["error" => true, "msg" => "Sesión inválida"]);
  exit();
}

$id_game = $_POST['id_game'] ?? 0;
$id_user = $_POST['id_user'] ?? 0;

if (!$id_game || !$id_user) {
  echo json_encode(["error" => true, "msg" => "Datos faltantes"]);
  exit();
}

// 🕒 Actualizar actividad del jugador actual
$con->prepare("
  UPDATE room_players 
  SET last_active = NOW() 
  WHERE id_games = ? AND id_user = ? AND active_state = 'active'
")->execute([$id_game, $id_user]);

// 🧹 Marcar como inactivos los jugadores sin actividad por más de 60 segundos
$con->prepare("
  UPDATE room_players
  SET active_state = 'inactive'
  WHERE id_games = ?
  AND active_state = 'active'
  AND TIMESTAMPDIFF(SECOND, last_active, NOW()) > 60
")->execute([$id_game]);

// 🔍 Obtener los jugadores activos
$stmt = $con->prepare("
  SELECT rp.id_user, u.username, rp.ready, rp.team, g.started
  FROM room_players rp
  JOIN users u ON u.id_user = rp.id_user
  JOIN games g ON g.id_games = rp.id_games
  WHERE rp.id_games = ? AND rp.active_state = 'active'
");
$stmt->execute([$id_game]);
$jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ⚠️ Verificar si el jugador actual sigue activo
$still_active = false;
foreach ($jugadores as $j) {
  if ($j['id_user'] == $id_user) {
    $still_active = true;
    break;
  }
}

if (!$still_active) {
  echo json_encode(["error" => false, "kick" => true]);
  exit();
}

$total = count($jugadores);
$total_ready = array_sum(array_column($jugadores, "ready"));
$teamA = count(array_filter($jugadores, fn($j) => $j["team"] == 1));
$teamB = count(array_filter($jugadores, fn($j) => $j["team"] == 2));

$este_ready = 0;
$started = 0;
foreach ($jugadores as $j) {
  if ($j["id_user"] == $id_user) $este_ready = $j["ready"];
  $started = $j["started"];
}

// 🧹 Limpiar jugadores inexistentes
$con->prepare("
  DELETE FROM room_players 
  WHERE id_games = ? 
  AND id_user NOT IN (SELECT id_user FROM users)
")->execute([$id_game]);

// 📤 Respuesta final
echo json_encode([
  "error" => false,
  "kick" => false,
  "jugadores" => $jugadores,
  "total_jugadores" => $total,
  "total_ready" => $total_ready,
  "teamA" => $teamA,
  "teamB" => $teamB,
  "ready_all" => ($total > 1 && $total_ready == $total),
  "este_ready" => $este_ready,
  "started" => $started,
  "texto_estado" => "Jugadores activos: $total / Listos: $total_ready"
]);
