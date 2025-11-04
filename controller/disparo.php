<?php
require_once("../database/conexion.php");
session_start();

header("Content-Type: application/json");

if (!isset($_POST['id_user'], $_POST['enemy_id'], $_POST['id_weapon'], $_POST['id_body'])) {
    echo json_encode(["status" => "error", "message" => "Faltan datos en la solicitud."]);
    exit();
}

$db = new Database();
$con = $db->conectar();

$id_user   = (int) $_POST['id_user'];
$enemy_id  = (int) $_POST['enemy_id'];
$id_weapon = (int) $_POST['id_weapon'];
$id_body   = (int) $_POST['id_body'];

try {
    // 🔹 Obtener información del arma
    $stmt = $con->prepare("SELECT name, damage FROM weapons WHERE id_weapons = ?");
    $stmt->execute([$id_weapon]);
    $weapon = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🔹 Multiplicador por parte del cuerpo
    $stmt = $con->prepare("SELECT name, damage AS multiplier FROM damage_part_body WHERE id_damage_body = ?");
    $stmt->execute([$id_body]);
    $body = $stmt->fetch(PDO::FETCH_ASSOC);

    $total_damage = $weapon['damage'] * $body['multiplier'];

    // 🔹 Vida actual del enemigo
    $stmt = $con->prepare("SELECT current_hp, id_games FROM room_players WHERE id_user = ?");
    $stmt->execute([$enemy_id]);
    $enemy = $stmt->fetch(PDO::FETCH_ASSOC);

    $new_hp = max(0, $enemy['current_hp'] - $total_damage);

    // 🔹 Actualizar HP
    $stmt = $con->prepare("UPDATE room_players SET current_hp = ?, is_alive = ? WHERE id_user = ?");
    $stmt->execute([$new_hp, $new_hp > 0 ? 1 : 0, $enemy_id]);

    // 🔹 Registrar evento
    $stmt = $con->prepare("
        INSERT INTO game_events (game_id, timestamp, event_type, weapon_id, damage, points_awarded)
        VALUES (?, NOW(), 'Disparo', ?, ?, ?)
    ");
    $points_awarded = $total_damage / 10;
    $stmt->execute([$enemy['id_games'], $id_weapon, $total_damage, $points_awarded]);

    // 🔹 Actualizar puntos del jugador
    $stmt = $con->prepare("UPDATE users SET points = points + ? WHERE id_user = ?");
    $stmt->execute([$points_awarded, $id_user]);

    // 🔹 Comprobar si sube de nivel
    $stmt = $con->prepare("
        SELECT u.points, u.level_id, l_next.level_id AS next_level, l_next.min_points
        FROM users u
        LEFT JOIN levels l_next ON l_next.level_id = u.level_id + 1
        WHERE u.id_user = ?
    ");
    $stmt->execute([$id_user]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($player && $player['next_level'] && $player['points'] >= $player['min_points']) {
        // 🧩 Sube de nivel
        $stmt = $con->prepare("UPDATE users SET level_id = ? WHERE id_user = ?");
        $stmt->execute([$player['next_level'], $id_user]);
        $level_up_message = "🎖️ ¡Has subido al " . $player['next_level'] . "!";
    } else {
        $level_up_message = "";
    }

    // 🔹 Mensaje de resultado
    $message = "💥 Has disparado a {$body['name']} con {$weapon['name']} causando {$total_damage} de daño.";
    if ($new_hp <= 0) $message .= " 🔻 Enemigo eliminado.";
    if ($level_up_message) $message .= " " . $level_up_message;

    echo json_encode([
        "status" => "success",
        "message" => $message,
        "new_hp" => $new_hp
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Error interno: " . $e->getMessage()]);
}
