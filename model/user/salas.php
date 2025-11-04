<?php
session_start();
require_once("../../database/conexion.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

$id_user = (int)$_SESSION['id_user'];
$id_world = isset($_POST['id_world']) ? (int)$_POST['id_world'] : 0;
$id_room = isset($_POST['id_room']) ? (int)$_POST['id_room'] : 0;

if ($id_world <= 0 || $id_room <= 0) {
    die("<h2 style='color:red;text-align:center;margin-top:50px'>
    ❌ Error: No se recibió el mundo o la sala correctamente.</h2>");
}

// ✅ Obtener nivel del jugador
$stmt = $con->prepare("SELECT level_id FROM users WHERE id_user = ?");
$stmt->execute([$id_user]);
$player_level = (int)$stmt->fetchColumn();

try {
    $con->beginTransaction();

    // 🧹 Limpiar registros previos del jugador en otras partidas
    $stmt = $con->prepare("DELETE FROM room_players WHERE id_user = ?");
    $stmt->execute([$id_user]);

    // 1️⃣ Buscar partida abierta en esa sala
    $stmt = $con->prepare("
        SELECT g.id_games,
               (SELECT COUNT(*) FROM room_players rp WHERE rp.id_games = g.id_games AND rp.status = 'active') AS jugadores
        FROM games g
        WHERE g.id_room = ? AND g.started = 0
        ORDER BY g.id_games DESC LIMIT 1
    ");
    $stmt->execute([$id_room]);
    $game = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2️⃣ Crear nueva partida si no hay activa o está llena
    if (!$game || ($game['jugadores'] ?? 0) >= 10) {
        $stmt = $con->prepare("
            INSERT INTO games (id_room, start_date, end_date, max_players, started)
            VALUES (?, NOW(), DATE_ADD(NOW(), INTERVAL 1 HOUR), 10, 0)
        ");
        $stmt->execute([$id_room]);
        $id_game = $con->lastInsertId();
    } else {
        $id_game = $game['id_games'];
    }

    // 3️⃣ Si la partida tiene jugadores, verificar compatibilidad de nivel
    $stmt = $con->prepare("
        SELECT AVG(u.level_id) AS promedio_nivel, COUNT(*) AS cantidad
        FROM room_players rp
        JOIN users u ON rp.id_user = u.id_user
        WHERE rp.id_games = ? AND rp.status = 'active'
    ");
    $stmt->execute([$id_game]);
    $dataNivel = $stmt->fetch(PDO::FETCH_ASSOC);
    $promedio = (float)$dataNivel['promedio_nivel'];
    $cantidad = (int)$dataNivel['cantidad'];

    $tolerancia = 0; // 🔒 mismo nivel exacto requerido
    if ($cantidad > 0 && $player_level != round($promedio)) {

        $con->rollBack();
        echo "<script>
            alert('⚠️ Nivel no compatible. Solo jugadores con nivel similar pueden unirse (diferencia máxima: ±{$tolerancia}).');
            window.location.href='world_rooms.php?id_world={$id_world}';
        </script>";
        exit();
    }

    // 4️⃣ Verificar que la partida no haya iniciado
    $stmt = $con->prepare("SELECT started FROM games WHERE id_games = ?");
    $stmt->execute([$id_game]);
    if ((int)$stmt->fetchColumn() === 1) {
        $con->rollBack();
        echo "<script>
            alert('🚫 La partida ya comenzó, busca otra sala.');
            window.location.href='world_rooms.php?id_world={$id_world}';
        </script>";
        exit();
    }

    // 5️⃣ Contar jugadores por equipo para balancear
    $stmtA = $con->prepare("SELECT COUNT(*) FROM room_players WHERE id_games = ? AND team = 1 AND status = 'active'");
    $stmtB = $con->prepare("SELECT COUNT(*) FROM room_players WHERE id_games = ? AND team = 2 AND status = 'active'");
    $stmtA->execute([$id_game]);
    $stmtB->execute([$id_game]);
    $countA = (int)$stmtA->fetchColumn();
    $countB = (int)$stmtB->fetchColumn();
    $team = ($countA <= $countB) ? 1 : 2;

    // 6️⃣ Insertar jugador
    $stmt = $con->prepare("
        INSERT INTO room_players (id_games, id_user, join_date, is_alive, current_hp, ready, team, status)
        VALUES (?, ?, NOW(), 1, 100, 0, ?, 'active')
    ");
    $stmt->execute([$id_game, $id_user, $team]);

    $con->commit();

    // ✅ Redirigir a la sala de espera
    header("Location: espera.php?id_game=" . $id_game);
    exit();
} catch (Exception $e) {
    $con->rollBack();
    die("<h2 style='color:red;text-align:center;margin-top:50px'>
    ❌ Error al unirse a la sala: {$e->getMessage()}</h2>");
}
