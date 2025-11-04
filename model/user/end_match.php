<?php
// end_match.php
header('Content-Type: application/json');
require_once("../../database/conexion.php");

$db = new Database();
$con = $db->conectar();

$id_game = (int)($_POST['id_game'] ?? 0);
$reason = $_POST['reason'] ?? 'manual';
$winner_team = isset($_POST['winner_team']) ? (int)$_POST['winner_team'] : null;

if (!$id_game) {
    echo json_encode(['error' => 'missing id_game']);
    exit;
}

// reglas de puntos
$POINTS = [
    'victoria' => 50,
    'derrota'  => -20,
    'abandono' => -30,
    'empate'   => 0
];

try {
    $con->beginTransaction();

    // 🛑 CORRECCIÓN 1: BLOQUEO DE EJECUCIÓN DUPLICADA
    // Si ya existe CUALQUIER registro para este id_game, asumimos que ya se procesó.
    $checkProcessed = $con->prepare("SELECT COUNT(*) FROM historial_partidas WHERE id_game = ?");
    $checkProcessed->execute([$id_game]);
    if ($checkProcessed->fetchColumn() > 0) {
        $con->commit();
        echo json_encode(['ok' => true, 'message' => 'Partida ya procesada. Bloqueo de repetición activado.']);
        exit;
    }
    // ---------------------------------------------------------------------

    // obtener lista de jugadores en la partida
    $stmt = $con->prepare("SELECT rp.id_user, rp.team, rp.is_alive, u.points FROM room_players rp JOIN users u ON rp.id_user = u.id_user WHERE rp.id_games = ?");
    $stmt->execute([$id_game]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$players) {
        $con->commit();
        echo json_encode(['ok' => true, 'message' => 'no players']);
        exit;
    }

    // Lógica para determinar el equipo ganador ($winner_team) y el resultado global ($match_result_global)
    if ($winner_team === null) {
        $counts = [1 => 0, 2 => 0];
        foreach ($players as $p) if ($p['is_alive']) $counts[(int)$p['team']]++;
        if ($counts[1] === $counts[2]) {
            $match_result_global = 'empate';
        } else {
            $winner_team = ($counts[1] > $counts[2]) ? 1 : 2;
            $match_result_global = 'victoria';
        }
    } else {
        $match_result_global = 'victoria';
    }

    if ($reason === 'timeout') {
        $aliveByTeam = [1 => 0, 2 => 0];
        foreach ($players as $p) if ($p['is_alive']) $aliveByTeam[(int)$p['team']]++;
        if ($aliveByTeam[1] > 0 && $aliveByTeam[2] > 0) {
            $match_result_global = 'empate';
            $winner_team = null;
        } else {
            if ($aliveByTeam[1] === 0 && $aliveByTeam[2] > 0) {
                $winner_team = 2;
                $match_result_global = 'victoria';
            }
            if ($aliveByTeam[2] === 0 && $aliveByTeam[1] > 0) {
                $winner_team = 1;
                $match_result_global = 'victoria';
            }
        }
    }

    if ($reason === 'opponent_left' && $winner_team) $match_result_global = 'victoria';

    // Asignación de resultados, preparación de historial y actualización de puntos
    $resultsToInsert = [];
    $updatesToUsers = []; // Array para agrupar las actualizaciones de puntos

    // Recorremos y asignamos por equipo
    foreach ($players as $p) {
        $p_user = (int)$p['id_user'];
        $p_team = (int)$p['team'];
        $oldPoints = (int)$p['points'];

        $result = 'derrota';
        $pointsChange = $POINTS['derrota'];

        // Lógica para determinar el resultado del jugador ($result y $pointsChange)
        if ($match_result_global === 'empate') {
            $result = 'empate';
            $pointsChange = $POINTS['empate'];
        } elseif ($match_result_global === 'victoria') {
            if ($winner_team === null) {
                $result = 'empate';
                $pointsChange = $POINTS['empate'];
            } else {
                if ($p_team === $winner_team) {
                    $result = 'victoria';
                    $pointsChange = $POINTS['victoria']; // +50 Puntos
                } else {
                    $result = 'derrota';
                    $pointsChange = $POINTS['derrota'];
                }
            }
        }

        if ($reason === 'abandono') {
            // Lógica de abandono...
        }

        $newPoints = $oldPoints + $pointsChange;
        if ($newPoints < 0) $newPoints = 0;

        // ✅ CORRECCIÓN 2: Guardar la actualización para ejecutarla después
        // El UPDATE users NO se realiza aquí.
        $updatesToUsers[] = [
            'id_user' => $p_user,
            'new_points' => $newPoints
        ];

        // preparar historial (usando los nuevos puntos calculados)
        $resultsToInsert[] = [
            'id_user' => $p_user,
            'result' => $result,
            'puntos_cambiados' => $pointsChange,
            'puntos_totales' => $newPoints
        ];
    }

    // ---------------------------------------------------------------------
    // ✅ EJECUTAR ACTUALIZACIÓN DE PUNTOS DE USUARIO UNA SOLA VEZ
    $updateStmt = $con->prepare("UPDATE users SET points = ? WHERE id_user = ?");
    foreach ($updatesToUsers as $u) {
        $updateStmt->execute([$u['new_points'], $u['id_user']]);
    }

    // Insertar en historial_partidas (también se ejecuta una sola vez por jugador)
    $ins = $con->prepare("INSERT INTO historial_partidas (id_user, id_game, result, puntos_cambiados, puntos_totales, fecha_jugada) VALUES (?,?,?,?,?,NOW())");
    foreach ($resultsToInsert as $r) {
        $ins->execute([$r['id_user'], $id_game, $r['result'], $r['puntos_cambiados'], $r['puntos_totales']]);
    }

    // marcar partida finalizada
    $u = $con->prepare("UPDATE games SET started = 0, end_date = NOW() WHERE id_games = ?");
    $u->execute([$id_game]);

    $con->commit();

    echo json_encode(['ok' => true, 'match_result' => $match_result_global, 'winner_team' => $winner_team, 'message' => 'Partida finalizada']);
    exit;
} catch (Exception $e) {
    if ($con->inTransaction()) $con->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
