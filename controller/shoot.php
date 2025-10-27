<?php
session_start();
require_once("../database/conexion.php");
$db = new Database;
$con = $db->conectar();

if (!isset($_SESSION['usuario']) || !isset($_POST['enemy_id']) || !isset($_POST['damage'])) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$attacker_username = $_SESSION['usuario'];
$enemy_id = intval($_POST['enemy_id']); // ID del enemigo en la tabla room_players
$damage = intval($_POST['damage']);

// Verificar que el jugador enemigo existe
$checkEnemy = $con->prepare("SELECT current_hp FROM room_players WHERE id_room_player = ?");
$checkEnemy->execute([$enemy_id]);
$enemy = $checkEnemy->fetch(PDO::FETCH_ASSOC);

if ($enemy) {
    $newHP = max(0, $enemy['current_hp'] - $damage);

    // Actualizar la vida del enemigo
    $update = $con->prepare("UPDATE room_players SET current_hp = ? WHERE id_room_player = ?");
    $update->execute([$newHP, $enemy_id]);

    echo json_encode([
        "success" => true,
        "message" => "Impacto exitoso",
        "enemy_hp" => $newHP
    ]);
} else {
    echo json_encode(["error" => "Enemigo no encontrado"]);
}
?>
