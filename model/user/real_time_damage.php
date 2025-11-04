<?php
// real_time_damage.php
require_once("../../database/conexion.php");
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
  echo json_encode(["error" => "Sesión no válida"]);
  exit;
}

$db = new Database();
$con = $db->conectar();

$id_game   = (int)($_POST['id_game'] ?? 0);
$id_user   = (int)$_SESSION['id_user']; // atacante
$target    = (int)($_POST['target'] ?? 0);
$weapon_id = (int)($_POST['weapon_id'] ?? 0);
$zona      = $_POST['zona'] ?? 'body';

if (!$id_game || !$id_user || !$target || !$weapon_id) {
  echo json_encode(["error" => "Datos incompletos"]);
  exit;
}

try {
  $con->beginTransaction();

  // ✅ Obtener daño base del arma
  $stmt = $con->prepare("SELECT damage FROM weapons WHERE id_weapons = ?");
  $stmt->execute([$weapon_id]);
  $weapon = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$weapon) throw new Exception("Arma inválida");

  $base = (int)$weapon['damage'];

  // ✅ Multiplicadores por zona
  $zona_mult = [
    'head' => 1.8,
    'body' => 1.0,
    'legs' => 0.7
  ];
  $mult = $zona_mult[$zona] ?? 1.0;

  // ✅ Balance del daño
  $damage = (int) round(min($base * $mult * 0.55, 35)); // menos letal

  // ✅ Obtener vida actual del objetivo
  $stmt = $con->prepare("SELECT current_hp FROM room_players WHERE id_games = ? AND id_user = ? FOR UPDATE");
  $stmt->execute([$id_game, $target]);
  $hp_row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$hp_row) throw new Exception("Objetivo no encontrado en partida");

  $hp_actual = (int)$hp_row['current_hp'];
  $nuevo_hp = max(0, $hp_actual - $damage);

  // ✅ Actualizar HP
  $stmt = $con->prepare("UPDATE room_players SET current_hp = ? WHERE id_games = ? AND id_user = ?");
  $stmt->execute([$nuevo_hp, $id_game, $target]);

  $response = [
    "msg" => "💥 Has hecho {$damage} de daño.",
    "target_hp" => $nuevo_hp
  ];

  // ✅ Verificar si el objetivo murió
  if ($nuevo_hp <= 0) {
    $stmt = $con->prepare("UPDATE room_players SET is_alive = 0 WHERE id_games = ? AND id_user = ?");
    $stmt->execute([$id_game, $target]);
  }

  // ✅ Verificar si todo un equipo ha muerto
  $stmt = $con->prepare("SELECT team FROM room_players WHERE id_games = ? AND id_user = ?");
  $stmt->execute([$id_game, $id_user]);
  $attacker_team = (int)$stmt->fetchColumn();

  $stmt = $con->prepare("SELECT COUNT(*) FROM room_players WHERE id_games = ? AND team != ? AND is_alive = 1");
  $stmt->execute([$id_game, $attacker_team]);
  $oponentes_vivos = (int)$stmt->fetchColumn();

  // Si el equipo rival está completamente muerto → terminar partida
  if ($oponentes_vivos == 0) {
    // Marcar partida como finalizada
    $stmt = $con->prepare("UPDATE games SET started = 0, end_date = NOW() WHERE id_games = ?");
    $stmt->execute([$id_game]);

    // Obtener todos los jugadores
    $stmt = $con->prepare("
      SELECT rp.id_user, rp.team, u.points
      FROM room_players rp
      JOIN users u ON rp.id_user = u.id_user
      WHERE rp.id_games = ?
    ");
    $stmt->execute([$id_game]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $POINTS_WIN = 50;
    $POINTS_LOSE = -20;

    $insertHist = $con->prepare("
      INSERT INTO historial_partidas (id_user, id_game, result, puntos_cambiados, puntos_totales, kills, deaths)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $updatePoints = $con->prepare("UPDATE users SET points = ? WHERE id_user = ?");

    foreach ($players as $p) {
      $uid = (int)$p['id_user'];
      $team = (int)$p['team'];
      $points = (int)$p['points'];

      if ($team == $attacker_team) {
        $delta = $POINTS_WIN;
        $res = "victoria";
      } else {
        $delta = $POINTS_LOSE;
        $res = "derrota";
      }

      $nuevo_total = max(0, $points + $delta);
      $updatePoints->execute([$nuevo_total, $uid]);
      $insertHist->execute([$uid, $id_game, $res, $delta, $nuevo_total, 0, 0]);
    }

    $response["msg"] .= " 🏁 ¡Tu equipo ganó la partida!";
    $response["ended"] = true;
    $response["winner_team"] = $attacker_team;
  }

  $con->commit();
  echo json_encode($response);
} catch (Exception $e) {
  if ($con->inTransaction()) $con->rollBack();
  echo json_encode(["error" => $e->getMessage()]);
}
