<?php
header('Content-Type: application/json');
require_once("../../database/conexion.php");

$id_user = intval($_POST['id_user'] ?? 0);
$id_game = intval($_POST['id_game'] ?? 0);

if (!$id_user || !$id_game) {
    echo json_encode(["error" => "Datos incompletos para el abandono."]);
    exit;
}

$db = new Database();
$con = $db->conectar();
$con->beginTransaction();
$response = ["success" => true, "end_match" => false];

try {
    // 1. Marcar al jugador como inactivo/fuera de combate y poner HP a 0
    $stmt = $con->prepare("
        UPDATE room_players 
        SET current_hp = 0, status = 'abandoned' 
        WHERE id_games = ? AND id_user = ?
    ");
    $stmt->execute([$id_game, $id_user]);

    // 2. Verificar si el abandono deja solo un equipo/jugador activo (lo que fuerza la victoria)
    $stmt_active = $con->prepare("
        SELECT COUNT(DISTINCT rp.team) 
        FROM room_players rp
        WHERE rp.id_games = ? AND rp.current_hp > 0
    ");
    $stmt_active->execute([$id_game]);
    $active_teams_count = (int)$stmt_active->fetchColumn();

    // Si solo queda un equipo (o un solo jugador en un equipo)
    if ($active_teams_count <= 1) {
        $response['end_match'] = true;
        // La finalización real se manejará en el cliente (batalla.php) llamando a finalizar("abandono")
        // y el otro jugador verá oponentes.length === 0 en real_time_status.php y finalizará("victoria").
    }

    $con->commit();
    echo json_encode($response);
} catch (PDOException $e) {
    $con->rollBack();
    error_log("Error al procesar la salida del jugador: " . $e->getMessage());
    echo json_encode(["error" => "Error de base de datos al abandonar."]);
}
