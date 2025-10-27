<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_POST['id_user'] ?? null;
    $world_id = $_POST['world_id'] ?? null;

    if (!$id_user || !$world_id) {
        die("Faltan datos para unirse al mundo.");
    }

    // 🔹 Guardar el mundo actual
    $_SESSION['world_id'] = $world_id;

    // 🔹 Conectar base de datos
    $conexion = new mysqli("localhost", "root", "", "halo_style");
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    // 🔹 Restablecer HP de todos los jugadores (o del enemigo, si lo prefieres)
    $conexion->query("UPDATE room_players SET current_hp = 100");

    // 🔹 Redirigir al combate
    header("Location: ../model/user/battle.php");
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>
