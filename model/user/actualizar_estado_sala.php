<?php
require_once("../../database/conexion.php");
session_start();

$db = new Database();
$con = $db->conectar();

// ⚙️ Verificar sesión válida
if (!isset($_SESSION['id_user']) || !isset($_SESSION['usuario'])) {
    http_response_code(403);
    echo json_encode(["error" => "Sesión no válida"]);
    exit;
}

$id_user = $_POST['id_user'] ?? $_SESSION['id_user'];
$id_game = $_POST['id_game'] ?? 0;
$accion  = $_POST['accion'] ?? '';

if (!$id_user || !$id_game || !$accion) {
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

switch ($accion) {

    // ✅ Jugador marca "Listo"
    case 'ready':
        $stmt = $con->prepare("UPDATE room_players SET ready = 1 WHERE id_user = ? AND id_games = ?");
        $stmt->execute([$id_user, $id_game]);
        break;

    // ✅ Cancela "Listo"
    case 'no_ready':
        $stmt = $con->prepare("UPDATE room_players SET ready = 0 WHERE id_user = ? AND id_games = ?");
        $stmt->execute([$id_user, $id_game]);
        break;

    // ✅ Iniciar partida
    case 'iniciar':
        $stmt = $con->prepare("UPDATE games SET started = 1 WHERE id_games = ?");
        $stmt->execute([$id_game]);
        break;

    // ✅ Salir de la sala
    case 'salir':
        $stmt = $con->prepare("DELETE FROM room_players WHERE id_user = ? AND id_games = ?");
        $stmt->execute([$id_user, $id_game]);

        // Si no quedan jugadores, eliminar la partida
        $stmt = $con->prepare("SELECT COUNT(*) FROM room_players WHERE id_games = ?");
        $stmt->execute([$id_game]);
        $restantes = $stmt->fetchColumn();

        if ($restantes == 0) {
            $con->prepare("DELETE FROM games WHERE id_games = ?")->execute([$id_game]);
        }

        unset($_SESSION['id_game']);
        break;

    // 🔁 Cambiar de equipo
    case 'cambiar_equipo':
        // Obtener equipo actual
        $stmt = $con->prepare("SELECT team FROM room_players WHERE id_user = ? AND id_games = ?");
        $stmt->execute([$id_user, $id_game]);
        $team_actual = (int)$stmt->fetchColumn();

        if ($team_actual > 0) {
            $nuevo_team = ($team_actual == 1) ? 2 : 1;

            // Verificar si hay espacio (máximo 5 jugadores por equipo)
            $stmt = $con->prepare("SELECT COUNT(*) FROM room_players WHERE id_games = ? AND team = ?");
            $stmt->execute([$id_game, $nuevo_team]);
            $count = (int)$stmt->fetchColumn();

            if ($count < 5) {
                $stmt = $con->prepare("UPDATE room_players SET team = ?, ready = 0 WHERE id_user = ? AND id_games = ?");
                $stmt->execute([$nuevo_team, $id_user, $id_game]);
                echo json_encode(["success" => true, "accion" => "cambiar_equipo", "ok" => true, "nuevo_team" => $nuevo_team]);
                exit;
            } else {
                echo json_encode(["success" => true, "accion" => "cambiar_equipo", "ok" => false, "msg" => "Equipo lleno"]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "accion" => "cambiar_equipo", "ok" => false, "msg" => "Jugador no encontrado"]);
            exit;
        }
        break;
}

// 🔄 Actualizar datos del usuario (por si cambió algo)
$stmt = $con->prepare("SELECT username FROM users WHERE id_user = ?");
$stmt->execute([$id_user]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData) {
    $_SESSION['usuario'] = $userData['username'];
}

echo json_encode(["success" => true, "accion" => $accion]);
