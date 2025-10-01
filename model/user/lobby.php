<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

$username = $_SESSION['usuario'] ?? 'Spartan';

// 🪖 Nivel actual del jugador (ejemplo: 3) - Esto luego lo traes de tu BD
$nivel = 3;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lobby | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/style.css">
  <style>
    body {
      background: url("../../img/lobby.jpeg") no-repeat center center fixed;
      background-size: cover;
      color: #fff;
    }
    .spartan-img {
      margin-top: 60px;
      width: 200px;
      height: 500px;
    }
    .user-rank {
      text-align: center;
      margin-bottom: 30px;
      padding: 15px;
      background: rgba(0, 0, 0, 0.6);
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0, 255, 255, 0.4);
    }
    .locked {
      background-color: rgba(255, 0, 0, 0.2);
      border: 1px dashed #ff4d4d;
      cursor: not-allowed;
    }
    .locked:hover {
      background-color: rgba(255, 0, 0, 0.3);
    }
    .locked-text {
      font-size: 0.85rem;
      color: #ff4d4d;
    }
  </style>
</head>
<body class="text-light halo-bg">

<div class="container-fluid h-100">
  <div class="row h-100">

    <!-- COLUMNA IZQUIERDA - Carrusel + Botones -->
    <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
      <div id="haloCarousel" class="carousel slide w-100 mb-4" data-bs-ride="carousel">
        <div class="carousel-inner rounded shadow">
          <div class="carousel-item active">
            <img src="../../img/RECHARGE.jpeg" class="d-block w-100" alt="RECHARGE">
          </div>
          <div class="carousel-item">
            <img src="../../img/BREAKER.jpeg" class="d-block w-100" alt="BREAKER">
          </div>
          <div class="carousel-item">
            <img src="../../img/LIVE_FIRE.jpeg" class="d-block w-100" alt="LIVE_FIRE">
          </div>
        </div>
      </div>

      <!-- Botones de menú con niveles requeridos -->
      <div class="menu-left text-start w-100">

        <!-- ✅ Siempre desbloqueado -->
        <button class="btn btn-outline-light w-100 text-start mb-2">RECHARGE</button>

        <!-- 🔐 BREAKER requiere nivel 5 -->
        <?php if ($nivel >= 5): ?>
          <button class="btn btn-outline-light w-100 text-start mb-2">BREAKER</button>
        <?php else: ?>
          <button class="btn btn-outline-light w-100 text-start mb-2 locked" disabled>
            🔒 BREAKER
            <div class="locked-text">Requiere nivel 5</div>
          </button>
        <?php endif; ?>

        <!-- 🔐 LIVE_FIRE requiere nivel 10 -->
        <?php if ($nivel >= 10): ?>
          <button class="btn btn-outline-light w-100 text-start">LIVE_FIRE</button>
        <?php else: ?>
          <button class="btn btn-outline-light w-100 text-start locked" disabled>
            🔒 LIVE_FIRE
            <div class="locked-text">Requiere nivel 10</div>
          </button>
        <?php endif; ?>

      </div>
    </div>

    <!-- COLUMNA CENTRAL - Spartan -->
    <div class="col-md-4 d-flex flex-column justify-content-center align-items-center position-relative text-center">
      <img src="../../img/117.png" alt="Spartan" class="spartan-img img-fluid">
      <p class="text-muted mt-3">¡Prepárate para la batalla, soldado!</p>
    </div>

    <!-- COLUMNA DERECHA - Usuario + Desafíos -->
    <div class="col-md-4 d-flex flex-column justify-content-center">
      <div class="user-rank">
        <h3 class="mb-1">Bienvenido, <span class="text-primary"><?= htmlspecialchars($username) ?></span></h3>
        <p class="text-warning mb-0">Rango: Recluta</p>
        <p class="text-info mb-0">Nivel actual: <?= $nivel ?></p>
      </div>

      <div class="challenge-panel p-4 bg-dark bg-opacity-75 rounded shadow-lg">
        <h5 class="text-uppercase text-info mb-3">Desafíos Diarios</h5>
        <ul class="list-group list-group-flush mb-4">
          <li class="list-group-item bg-transparent text-light">🎯 Juega una partida</li>
          <li class="list-group-item bg-transparent text-light">⚔️ Gana 1 partida</li>
        </ul>

        <h5 class="text-uppercase text-warning mb-3">Desafíos Semanales</h5>
        <ul class="list-group list-group-flush">
          <li class="list-group-item bg-transparent text-light">🏆 Completa 4 partidas</li>
          <li class="list-group-item bg-transparent text-light">🔥 Gana 3 partidas</li>
        </ul>
      </div>
    </div>

  </div>
</div>

<script src="../../controller/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
