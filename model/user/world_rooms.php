<?php
session_start();
require_once("../../controller/validar_sesion.php");
require_once("../../database/conexion.php");

$db = new Database();
$con = $db->conectar();

// 🔐 Verificar sesión
if (!isset($_SESSION['id_user'])) {
  header("Location: ../../index.php");
  exit;
}

$id_user = $_SESSION['id_user'];
$id_world = $_GET['id_world'] ?? 0;

// 🚫 Validar ID del mundo
if ($id_world <= 0) {
  echo "<h2 class='text-center text-danger mt-5'>❌ Mundo no válido.</h2>";
  exit;
}

// ✅ Obtener información del mundo
$stmt = $con->prepare("SELECT name, image FROM worlds WHERE id_world = ?");
$stmt->execute([$id_world]);
$world = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$world) {
  echo "<h2 class='text-center text-danger mt-5'>❌ No se encontró el mundo.</h2>";
  exit;
}

// ✅ Obtener nivel del usuario actual
$stmtLevel = $con->prepare("SELECT level_id FROM users WHERE id_user = ?");
$stmtLevel->execute([$id_user]);
$userLevel = (int)$stmtLevel->fetchColumn();

// ✅ Obtener salas del mundo
$roomsQuery = "
    SELECT 
        r.id_room,
        r.created_at,
        (SELECT COUNT(*) FROM games g WHERE g.id_room = r.id_room AND g.started = 0) AS active_games,
        (SELECT COUNT(*) FROM room_players rp
         JOIN games g2 ON rp.id_games = g2.id_games
         WHERE g2.id_room = r.id_room 
           AND g2.started = 0
           AND rp.status = 'active') AS players_in_room,
        (SELECT g3.max_players 
         FROM games g3 
         WHERE g3.id_room = r.id_room 
           AND g3.started = 0
         ORDER BY g3.id_games DESC 
         LIMIT 1) AS max_players
    FROM rooms r
    WHERE r.world_id = ?
    ORDER BY r.id_room ASC
    LIMIT 6
";
$roomsStmt = $con->prepare($roomsQuery);
$roomsStmt->execute([$id_world]);
$rooms = $roomsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Salas - <?= htmlspecialchars($world['name']) ?></title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">

  <style>
    body {
      background: url('../../<?= htmlspecialchars($world['image']) ?>') center/cover fixed;
      color: white;
      backdrop-filter: blur(4px);
    }

    .room-card {
      background: rgba(0, 0, 0, 0.75);
      border-radius: 12px;
      padding: 15px;
      transition: 0.3s;
    }

    .room-card:hover {
      transform: scale(1.05);
      box-shadow: 0 0 12px #00eaff;
    }

    .btn-halo {
      background: #00eaff;
      border: none;
      font-weight: bold;
    }

    .btn-halo:hover {
      background: #00b8cc;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      margin-top: 15px;
    }

    .full-room {
      background: rgba(255, 0, 0, 0.4) !important;
    }

    .level-locked {
      background: rgba(255, 193, 7, 0.25);
      border: 1px solid #ffc107;
    }
  </style>
</head>

<body>

  <div class="container">

    <div class="top-bar">
      <a href="lobby.php" class="btn btn-danger">⬅ Lobby</a>
      <a href="select_world.php" class="btn btn-warning">🌍 Cambiar Mundo</a>
    </div>

    <h2 class="text-center mt-4">🌍 Mundo: <?= htmlspecialchars($world['name']) ?></h2>
    <p class="text-center">Salas disponibles — Nivel actual: <strong><?= $userLevel ?></strong></p>

    <div class="row mt-4">

      <?php if (count($rooms) == 0): ?>
        <div class="text-center text-light fs-4 mb-4">No hay salas disponibles en este mundo.</div>
      <?php endif; ?>

      <?php
      $contador = 1;
      $tolerancia = 1; // Nivel de diferencia permitido

      foreach ($rooms as $room):
        $max = $room['max_players'] ?? 10;
        $players = $room['players_in_room'] ?? 0;
        $full = $players >= $max;

        // 🔹 Calcular nivel promedio de jugadores en la sala
        $stmtNivelSala = $con->prepare("
      SELECT AVG(u.level_id) AS nivel_promedio
      FROM room_players rp
      JOIN users u ON rp.id_user = u.id_user
      JOIN games g ON rp.id_games = g.id_games
      WHERE g.id_room = ? AND g.started = 0 AND rp.status = 'active'
    ");
        $stmtNivelSala->execute([$room['id_room']]);
        $nivelPromedioSala = (float)$stmtNivelSala->fetchColumn();

        // 🔒 Determinar si el nivel del jugador es compatible
        $nivelBloqueado = false;
        if ($players > 0 && $nivelPromedioSala > 0) {
          $nivelBloqueado = abs($nivelPromedioSala - $userLevel) > $tolerancia;
        }
      ?>
        <div class="col-md-4 mb-3">
          <div class="room-card text-center <?= $full ? 'full-room' : ($nivelBloqueado ? 'level-locked' : '') ?>">

            <h5>Sala #<?= $contador ?></h5>
            <p>🕹 Partidas activas: <?= $room['active_games'] ?></p>
            <p>👥 Jugadores: <?= $players ?> / <?= $max ?></p>
            <p>📅 Creada: <?= $room['created_at'] ?></p>

            <?php if ($full): ?>
              <button class="btn btn-secondary w-100" disabled>⛔ Sala llena</button>
            <?php elseif ($nivelBloqueado): ?>
              <button class="btn btn-warning w-100" disabled>
                🔒 Nivel incompatible (Promedio: <?= round($nivelPromedioSala, 1) ?>)
              </button>
            <?php else: ?>
              <form method="POST" action="salas.php">
                <input type="hidden" name="id_world" value="<?= $id_world ?>">
                <input type="hidden" name="id_room" value="<?= $room['id_room'] ?>">
                <input type="hidden" name="sala_numero" value="<?= $contador ?>">
                <button type="submit" class="btn btn-halo w-100">Entrar</button>
              </form>
            <?php endif; ?>

          </div>
        </div>
      <?php
        $contador++;
      endforeach;
      ?>

    </div>
  </div>

  <script src="../../controller/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>