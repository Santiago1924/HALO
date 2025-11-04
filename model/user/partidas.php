<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once("../../database/conexion.php");

$conn = new mysqli("localhost", "root", "", "halo_style");
if ($conn->connect_error) {
  die("<div class='alert alert-danger text-center'>❌ Error de conexión a la base de datos.</div>");
}

$username = $_SESSION['usuario'] ?? '';
if (!$username) {
  die("<div class='alert alert-warning text-center'>⚠️ Usuario no autenticado.</div>");
}

// 🧩 Obtener usuario actual y puntos
$queryUser = $conn->prepare("SELECT id_user, points FROM users WHERE username = ?");
$queryUser->bind_param("s", $username);
$queryUser->execute();
$userData = $queryUser->get_result()->fetch_assoc();
$userId = $userData['id_user'] ?? 0;
$userPoints = (int)($userData['points'] ?? 0);

// 🧩 Calcular estadísticas generales
$sql = "
    SELECT 
        COUNT(*) AS total_partidas,
        SUM(result = 'victoria') AS partidas_ganadas,
        SUM(result = 'derrota') AS partidas_perdidas,
        SUM(result = 'empate') AS partidas_empatadas,
        COALESCE(SUM(puntos_cambiados),0) AS total_puntos_cambiados
    FROM historial_partidas
    WHERE id_user = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();

$totalPartidas = (int)($datos['total_partidas'] ?? 0);
$partidasGanadas = (int)($datos['partidas_ganadas'] ?? 0);
$partidasPerdidas = (int)($datos['partidas_perdidas'] ?? 0);
$partidasEmpatadas = (int)($datos['partidas_empatadas'] ?? 0);
$porcentajeVictorias = ($totalPartidas > 0) ? round(($partidasGanadas / $totalPartidas) * 100, 1) : 0;
$totalPuntosCambiados = (int)($datos['total_puntos_cambiados'] ?? 0);

// 🧩 Obtener últimas partidas
$queryHistorial = $conn->prepare("
  SELECT result, puntos_cambiados, puntos_totales, fecha_jugada
  FROM historial_partidas
  WHERE id_user = ?
  ORDER BY fecha_jugada DESC
  LIMIT 10
");
$queryHistorial->bind_param("i", $userId);
$queryHistorial->execute();
$historial = $queryHistorial->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historial de Batallas</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <style>
    body {
      background: url("../../img/mapas/HISTORIAL_MAPA.jpeg") center/cover fixed;
      background-color: rgba(0, 0, 0, 0.65);
      background-blend-mode: multiply;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      color: white;
      padding-bottom: 60px;
    }

    .back-btn {
      background: linear-gradient(90deg, #00bcd4, #005f73);
      color: white;
      padding: 12px 25px;
      border-radius: 30px;
      font-weight: bold;
      text-decoration: none;
      box-shadow: 0 0 12px rgba(0, 212, 255, 0.6);
      transition: all 0.3s ease;
    }

    .back-btn:hover {
      background: linear-gradient(90deg, #00eaff, #0088cc);
      box-shadow: 0 0 25px rgba(0, 234, 255, 0.9);
      transform: translateY(-2px) scale(1.05);
    }
  </style>
</head>

<body>

  <div class="container mt-4">
    <div class="d-flex justify-content-start mb-3">
      <a href="lobby.php" class="back-btn">⬅️ Volver al Lobby</a>
    </div>

    <div class="card bg-dark border-info shadow-lg rounded-4 p-4">
      <div class="card-header text-center text-info fw-bold fs-4 border-info">
        ⚔️ Historial de Batallas — <?= htmlspecialchars($username) ?>
      </div>

      <div class="card-body">

        <div class="row text-center mb-4">
          <div class="col-md-3">
            <div class="p-3 border rounded bg-success bg-opacity-25">
              <h6 class="text-uppercase text-success mb-1">Victorias</h6>
              <h3 class="fw-bold"><?= $partidasGanadas ?></h3>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 border rounded bg-danger bg-opacity-25">
              <h6 class="text-uppercase text-danger mb-1">Derrotas</h6>
              <h3 class="fw-bold"><?= $partidasPerdidas ?></h3>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 border rounded bg-warning bg-opacity-25">
              <h6 class="text-uppercase text-warning mb-1">Empates</h6>
              <h3 class="fw-bold"><?= $partidasEmpatadas ?></h3>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 border rounded bg-info bg-opacity-25">
              <h6 class="text-uppercase text-info mb-1">Total Partidas</h6>
              <h3 class="fw-bold"><?= $totalPartidas ?></h3>
            </div>
          </div>
        </div>

        <hr class="border-info">

        <div class="text-center mb-4">
          <h6 class="text-light">Porcentaje de Victorias</h6>
          <div class="progress mx-auto" style="width: 60%; height: 20px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
              style="width: <?= $porcentajeVictorias ?>%;">
              <?= $porcentajeVictorias ?>%
            </div>
          </div>

          <h5 class="text-info fw-bold mt-4">
            ⭐ Puntos Totales: <span class="text-warning"><?= $userPoints ?></span>
          </h5>
        </div>

        <hr class="border-info">
        <h5 class="text-center text-info mb-3">🕒 Últimas 10 Partidas</h5>

        <div class="table-responsive">
          <table class="table table-dark table-striped table-bordered text-center align-middle">
            <thead class="bg-info text-dark">
              <tr>
                <th>Fecha</th>
                <th>Resultado</th>
                <th>Puntos ±</th>
                <th>Puntos Totales</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($historial->num_rows > 0): ?>
                <?php while ($row = $historial->fetch_assoc()): ?>
                  <tr>
                    <td><?= date("d/m/Y H:i", strtotime($row['fecha_jugada'])) ?></td>
                    <td class="<?=
                                $row['result'] === 'victoria' ? 'text-success' : ($row['result'] === 'derrota' ? 'text-danger' :
                                    'text-warning') ?>">
                      <strong><?= ucfirst($row['result']) ?></strong>
                    </td>
                    <td class="<?= ($row['puntos_cambiados'] >= 0) ? 'text-success' : 'text-danger' ?>">
                      <?= ($row['puntos_cambiados'] >= 0 ? '+' : '') . $row['puntos_cambiados'] ?>
                    </td>
                    <td><?= $row['puntos_totales'] ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-muted">Aún no has jugado partidas.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="../../controller/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>