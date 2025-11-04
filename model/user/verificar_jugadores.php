<?php
require_once("../../database/conexion.php");
$db = new Database();
$con = $db->conectar();

$id_game = (int)($_POST['id_game'] ?? 0);
$id_user = (int)($_POST['id_user'] ?? 0);

if ($id_game <= 0) {
    echo json_encode(["error" => "Partida no válida"]);
    exit;
}

// Obtener info jugadores
$stmt = $con->prepare("
    SELECT u.username, rp.ready, rp.team, rp.id_user
    FROM room_players rp
    JOIN users u ON rp.id_user = u.id_user
    WHERE rp.id_games = ?
    ORDER BY rp.team, rp.join_date
");
$stmt->execute([$id_game]);
$jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar equipos y listos
$countA = 0;
$countB = 0;
$ready = 0;
foreach ($jugadores as $j) {
    if ($j['team'] == 1) $countA++;
    else $countB++;
    if ($j['ready'] == 1) $ready++;
}

// Saber si este jugador está listo
$stmt = $con->prepare("SELECT ready FROM room_players WHERE id_games = ? AND id_user = ?");
$stmt->execute([$id_game, $id_user]);
$este_ready = (int)$stmt->fetchColumn();

// regla de inicio
$starting = ($countA >= 1 && $countB >= 1 && $ready == count($jugadores) && count($jugadores) > 0);

// actualizar “started” si aplica
if ($starting) {
    $con->prepare("UPDATE games SET started = 1 WHERE id_games = ?")->execute([$id_game]);
}

echo json_encode([
    "jugadores" => $jugadores,
    "started" => (int)($starting ? 1 : 0),
    "este_ready" => $este_ready,
    "starting" => $starting
]);
