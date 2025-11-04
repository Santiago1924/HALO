<?php
session_start();
require_once("../../controller/validar_sesion.php");
require_once("../../database/conexion.php");

if (!isset($_POST['id_world'])) {
    header("Location: select_world.php");
    exit();
}

$id_user  = $_SESSION['id_user'];
$id_world = $_POST['id_world'];

$db = new Database();
$con = $db->conectar();

// Guardar mundo en usuario
$stmt = $con->prepare("UPDATE users SET id_world = ? WHERE id_user = ?");
$stmt->execute([$id_world, $id_user]);

// ✅ Redirigir directo a lista de salas del mundo
header("Location: world_rooms.php?id_world=" . $id_world);
exit();
