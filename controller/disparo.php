<?php
session_start();
header('Content-Type: application/json');

// 🔹 Conectar a la base de datos
$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit();
}

// 🔹 Datos recibidos desde battle.php
$id_user  = intval($_POST['id_user']);        // jugador actual
$enemy_id = intval($_POST['enemy_id']);       // id_room_player del enemigo
$id_weapon = intval($_POST['id_weapon']);
$id_body   = intval($_POST['id_body']);

// 🔹 Validación rápida
if (!$id_user || !$enemy_id || !$id_weapon || !$id_body) {
    echo json_encode(["error" => "Faltan datos para procesar el disparo"]);
    exit();
}

// 🔹 Obtener daño base del arma
$q_weapon = $conexion->query("SELECT damage FROM weapons WHERE id_weapons = $id_weapon");
$weapon = $q_weapon->fetch_assoc();
$damage_base = $weapon['damage'] ?? 10; // daño por defecto

// 🔹 Multiplicador por parte del cuerpo
$q_body = $conexion->query("SELECT multiplier FROM damage_part_body WHERE id_damage_body = $id_body");
$body = $q_body->fetch_assoc();
$mult = $body['multiplier'] ?? 1.0;

// 🔹 Calcular daño final
$damage = round($damage_base * $mult);

// 🔹 Obtener vida actual del enemigo
$q_enemy = $conexion->query("SELECT current_hp FROM room_players WHERE id_room_player = $enemy_id");
$enemy = $q_enemy->fetch_assoc();

if (!$enemy) {
    echo json_encode(["error" => "No se encontró al enemigo."]);
    exit();
}

$enemy_hp = max(0, $enemy['current_hp'] - $damage);

// 🔹 Actualizar vida del enemigo
$conexion->query("UPDATE room_players SET current_hp = $enemy_hp WHERE id_room_player = $enemy_id");

// 🔹 Registrar resultado
$message = "💥 Disparo acertado: causaste $damage de daño. HP enemigo: $enemy_hp / 100";

// 🔹 Enviar respuesta al cliente
echo json_encode([
    "success" => true,
    "message" => $message,
    "enemy_hp" => $enemy_hp
]);

$conexion->close();
?>
