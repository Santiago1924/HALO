<?php
// end_game.php  (opcional)
require_once("../../database/conexion.php");
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
  echo json_encode(["error" => "Sesión no válida"]);
  exit;
}

$db = new Database();
$con = $db->conectar();

$id_game = (int)($_POST['id_game'] ?? 0);
$reason  = $_POST['reason'] ?? 'manual';
if (!$id_game) {
  echo json_encode(["error" => "No game"]);
  exit;
}

try {
  $con->beginTransaction();

  // Marcar como finalizada
  $stmt = $con->prepare("UPDATE games SET started = 0, end_date = NOW() WHERE id_games = ?");
  $stmt->execute([$id_game]);

  // Lógica de puntos puede hacerse aquí (similar a real_time_damage.php),
  // pero la dejamos simple para que el caller indique ganador/loser si lo desea.
  $con->commit();
  echo json_encode(["ok" => true, "msg" => "Partida finalizada: $reason"]);
} catch (Exception $e) {
  if ($con->inTransaction()) $con->rollBack();
  echo json_encode(["error" => $e->getMessage()]);
}
