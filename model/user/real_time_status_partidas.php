<?php
require_once("../../database/conexion.php");

if (!isset($_POST['id_user'])) {
  http_response_code(400);
  exit("Falta el ID del usuario");
}

$id_user = (int)$_POST['id_user'];

$conn = new mysqli("localhost", "root", "", "halo_style");
if ($conn->connect_error) {
  die("<div class='alert alert-danger text-center'>❌ Error de conexión a la base de datos.</div>");
}

// 🔹 Obtener totales
$sql = "
  SELECT 
    COUNT(*) AS total_partidas,
    SUM(CASE WHEN result = 'victoria' THEN 1 ELSE 0 END) AS partidas_ganadas,
    SUM(CASE WHEN result = 'derrota' THEN 1 ELSE 0 END) AS partidas_perdidas,
    SUM(CASE WHEN result = 'empate' THEN 1 ELSE 0 END) AS partidas_empatadas
  FROM historial_partidas
  WHERE id_user = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();

$totalPartidas = $datos['total_partidas'] ?? 0;
$partidasGanadas = $datos['partidas_ganadas'] ?? 0;
$partidasPerdidas = $datos['partidas_perdidas'] ?? 0;
$partidasEmpatadas = $datos['partidas_empatadas'] ?? 0;
$porcentajeVictorias = ($totalPartidas > 0) ? round(($partidasGanadas / $totalPartidas) * 100, 1) : 0;

// 🔹 Obtener puntos actuales
$qPoints = $conn->prepare("SELECT points FROM users WHERE id_user = ?");
$qPoints->bind_param("i", $id_user);
$qPoints->execute();
$userPoints = $qPoints->get_result()->fetch_assoc()['points'] ?? 0;

// 🔹 Obtener historial
$queryHistorial = $conn->prepare("
  SELECT result, puntos_cambiados, puntos_totales, fecha_jugada
  FROM historial_partidas
  WHERE id_user = ?
  ORDER BY fecha_jugada DESC
  LIMIT 10
");
$queryHistorial->bind_param("i", $id_user);
$queryHistorial->execute();
$historial = $queryHistorial->get_result();

// 🔹 Render HTML dinámico (igual a la estructura de antes)
?>

<div class="card bg-dark text-light shadow-lg border-info border-2 rounded-4">
  <div class="card-header bg-gradient bg-info text-dark text-center fw-bold fs-5">
    ⚔️ Historial de Batallas
  </div>

  <div class="card-body">
    <div class="row text-center mb-4">
      <div class="col-md-4 mb-3">
        <div class="p-3 border rounded bg-success bg-opacity-25">
          <h6 class="text-uppercase text-success mb-1">Victorias</h6>
          <h3 class="fw-bold"><?= $partidasGanadas ?></h3>
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <div class="p-3 border rounded bg-danger bg-opacity-25">
          <h6 class="text-uppercase text-danger mb-1">Derrotas</h6>
          <h3 class="fw-bold"><?= $partidasPerdidas ?></h3>
        </div>
      </div>

      <div class="col-md-4 mb-3">
        <div class="p-3 border rounded bg-secondary bg-opacity-25">
          <h6 class="text-uppercase text-secondary mb-1">Empates</h6>
          <h3 class="fw-bold"><?= $partidasEmpatadas ?></h3>
        </div>
      </div>
    </div>

    <hr class="border-info">

    <div class="text-center mb-4">
      <h6 class="text-info">Partidas jugadas</h6>
      <h4 class="fw-semibold"><?= $totalPartidas ?></h4>

      <h6 class="text-light mt-3 mb-2">Porcentaje de Victorias</h6>
      <div class="progress mx-auto" style="width: 60%; height:20px;">
        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
          role="progressbar"
          style="width: <?= $porcentajeVictorias ?>%;">
          <?= $porcentajeVictorias ?>%
        </div>
      </div>

      <h5 class="text-info fw-bold mt-4">⭐ Puntos Totales:
        <span class="text-warning"><?= $userPoints ?></span>
      </h5>
    </div>

    <hr class="border-info">

    <h5 class="text-center text-info mb-3">🕒 Últimas Partidas</h5>
    <div class="table-responsive">
      <table class="table table-dark table-hover align-middle text-center">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Resultado</th>
            <th>Puntos ganados/perdidos</th>
            <th>Puntos totales</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($historial->num_rows > 0): ?>
            <?php while ($row = $historial->fetch_assoc()): ?>
              <tr>
                <td><?= date("d/m/Y H:i", strtotime($row['fecha_jugada'])) ?></td>
                <td>
                  <?php
                  $color = [
                    'victoria' => 'text-success',
                    'derrota' => 'text-danger',
                    'empate' => 'text-warning'
                  ][$row['result']] ?? 'text-light';
                  ?>
                  <strong class="<?= $color ?>"><?= ucfirst($row['result']) ?></strong>
                </td>
                <td class="<?= ($row['puntos_cambiados'] >= 0) ? 'text-success' : 'text-danger' ?>">
                  <?= ($row['puntos_cambiados'] >= 0 ? '+' : '') . $row['puntos_cambiados'] ?>
                </td>
                <td><?= $row['puntos_totales'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center text-muted">No hay partidas registradas aún.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>