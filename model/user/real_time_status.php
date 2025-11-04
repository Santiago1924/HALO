<?php
header('Content-Type: application/json');
require_once("../../database/conexion.php");

// 🔑 Obtener y validar datos POST
$id_game = intval($_POST['id_game'] ?? 0);
$id_user = intval($_POST['id_user'] ?? 0);

if (!$id_game || !$id_user) {
  echo json_encode(["error" => "Faltan datos de partida o usuario."]);
  exit;
}

$db = new Database();
$con = $db->conectar();
$response = ["mi_hp" => 0, "oponentes" => []];

try {
  // 1. Obtener mi información (HP y equipo)
  $stmt_mine = $con->prepare("
        SELECT team, current_hp 
        FROM room_players 
        WHERE id_games = ? AND id_user = ?
    ");
  $stmt_mine->execute([$id_game, $id_user]);
  $userData = $stmt_mine->fetch(PDO::FETCH_ASSOC);

  if (!$userData) {
    echo json_encode(["error" => "Jugador no encontrado en la partida."]);
    exit;
  }

  $my_team = (int)$userData['team'];
  $response['mi_hp'] = max(0, (float)$userData['current_hp']);

  // 2. Obtener datos de los oponentes ACTIVO (de otro equipo)
  // Nota: El uso de 'status = active' es clave aquí para filtrar a los que abandonaron.
  $stmt_opponents = $con->prepare("
        SELECT 
            rp.id_user, 
            u.username, 
            rp.current_hp AS hp,
            a.image_url AS avatar_img
        FROM room_players rp
        JOIN users u ON rp.id_user = u.id_user
        LEFT JOIN avatars a ON u.id_avatar = a.id_avatar
        WHERE 
            rp.id_games = ? AND 
            rp.team <> ? AND 
            rp.status = 'active'
    ");
  $stmt_opponents->execute([$id_game, $my_team]);
  $oponentes_raw = $stmt_opponents->fetchAll(PDO::FETCH_ASSOC);

  $oponentes_clean = [];
  foreach ($oponentes_raw as $o) {
    $oponentes_clean[] = [
      "id_user" => (int)$o['id_user'],
      "username" => htmlspecialchars($o['username']),
      // Aseguramos que HP esté en el rango 0-100 para la barra de vida en el cliente
      "hp" => max(0, min(100, (float)$o['hp'])),
      "avatar_img" => $o['avatar_img']
    ];
  }

  $response['oponentes'] = $oponentes_clean;

  // 3. Verificación de Victoria/Fin de Partida
  // Si no quedan oponentes activos y yo no he muerto, el cliente debe ganar.
  if (count($oponentes_clean) === 0 && $response['mi_hp'] > 0) {
    // match_end le indica al JS que verifique si ganó/perdió/empató
    $response['ended'] = true;
    $response['winner_team'] = $my_team;
  }

  echo json_encode($response);
} catch (PDOException $e) {
  // Captura errores de base de datos
  error_log("Error en real_time_status.php: " . $e->getMessage());
  echo json_encode(["error" => "Error interno del servidor."]);
}
