<?php
session_start();
require_once("../../controller/validar_sesion.php");
require_once("../../database/conexion.php");

$db = new Database();
$con = $db->conectar();

$id_user = $_SESSION['id_user'];
$username = $_SESSION['usuario'];

// Obtener nivel actual del jugador
$query = $con->prepare("
  SELECT u.level_id, l.name AS level_name
  FROM users u
  LEFT JOIN levels l ON u.level_id = l.level_id
  WHERE u.id_user = ?
");
$query->execute([$id_user]);
$user = $query->fetch(PDO::FETCH_ASSOC);
$level_id = $user['level_id'] ?? 1;
$level_name = $user['level_name'] ?? "Recluta";

// Obtener mundos
$worlds = $con->query("SELECT id_world, name, image, required_level FROM worlds ORDER BY id_world ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Selecciona tu Mundo</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <style>
    body {
      /* Estilos de fondo Corregidos para evitar duplicación y asegurar cobertura */
      background: url(../../img/mapas/select_world.png);
      background-size: cover;
      /* ¡Asegura que cubra toda la pantalla! */
      background-repeat: no-repeat;
      /* Evita que la imagen se duplique */
      background-attachment: fixed;
      /* Fija la imagen durante el scroll */
      background-position: center center;
      /* Centra la imagen */

      color: white;
      min-height: 100vh;
    }

    .world-card {
      background: rgba(0, 0, 0, 0.85);
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      margin: 15px;
      transition: .3s;
    }

    .world-card:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px #00bcd4;
    }

    .locked {
      filter: grayscale(100%) brightness(.5);
      pointer-events: none;
      position: relative;
    }

    .locked::after {
      content: "Nivel requerido";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(253, 253, 253, 0.8);
      padding: 8px 15px;
      border-radius: 8px;
      color: #ff4444;
      font-weight: bold;
    }

    .select-btn {
      background: #00bcd4;
      border: none;
      padding: 8px 18px;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }

    .back-btn {
      /* Botón "Volver" ajustado para ser responsive (se pega a la derecha) */
      position: absolute;
      top: 10px;
      right: 10px;
      /* Ajustado de 1400px a 10px para mejor visualización */
      left: auto;
      background: linear-gradient(90deg, #00bcd4, #005f73);
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: bold;
      text-decoration: none;
      z-index: 1000;
    }

    .titulo {
      color: black;
      padding: 75px;
    }

    .name_world {
      color: white;
    }
  </style>
</head>

<body>

  <a href="lobby.php" class="back-btn">⬅ Volver</a>

  <div class="container titulo mt-6">
    <h2>🌍 Selecciona tu Mundo</h2>
    <p class="name_world">Jugador: <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($level_name) ?>)</p>

    <div class="row">

      <?php foreach ($worlds as $world): ?>
        <?php $bloqueado = $level_id < $world['required_level']; ?>

        <div class="col-md-4">
          <div class="world-card <?= $bloqueado ? 'locked' : '' ?>">
            <img src="../../<?= $world['image'] ?>" class="img-fluid rounded mb-2">
            <h4 class="name_world "><?= $world['name'] ?></h4>

            <?php if (!$bloqueado): ?>
              <form action="guardar_mundo.php" method="POST">
                <input type="hidden" name="id_world" value="<?= $world['id_world'] ?>">
                <button type="submit" class="select-btn mt-2">Seleccionar</button>
              </form>
            <?php else: ?>
              <p class="name_world ">🔒 Nivel requerido: <?= $world['required_level'] ?></p>
            <?php endif; ?>

          </div>
        </div>

      <?php endforeach; ?>

    </div>
  </div>

</body>

</html>