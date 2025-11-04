<?php
require_once("../../controller/validar_sesion.php");
require_once("../../database/conexion.php");

$db = new Database();
$con = $db->conectar();

$username = $_SESSION['usuario'] ?? 'Spartan';
$id_user  = $_SESSION['id_user'] ?? 0;

// ---------------- OBTENER INFO DEL USUARIO ----------------
try {
  $stmt = $con->prepare("
    SELECT u.id_user, u.points, u.level_id, l.name AS level_name, l.min_points
    FROM users u
    LEFT JOIN levels l ON u.level_id = l.level_id
    WHERE u.username = :username
  ");
  $stmt->execute(['username' => $username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  $points = $user['points'] ?? 0;
  $currentLevelId = $user['level_id'] ?? 1;
  $currentLevelName = $user['level_name'] ?? "Recluta";

  // Determinar si el usuario sube de nivel
  $stmtNext = $con->prepare("
    SELECT level_id, name, min_points FROM levels
    WHERE min_points <= ?
    ORDER BY min_points DESC
    LIMIT 1
  ");
  $stmtNext->execute([$points]);
  $next = $stmtNext->fetch(PDO::FETCH_ASSOC);

  if ($next && $next['level_id'] != $currentLevelId) {
    $update = $con->prepare("UPDATE users SET level_id = ? WHERE username = ?");
    $update->execute([$next['level_id'], $username]);
  }

  // ✅ Re-consultar datos reales después de actualizar
  $stmt = $con->prepare("
    SELECT u.id_user, u.points, u.level_id, l.name AS level_name
    FROM users u
    LEFT JOIN levels l ON u.level_id = l.level_id
    WHERE u.username = :username
  ");
  $stmt->execute(['username' => $username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  $points = $user['points'];
  $currentLevelId = $user['level_id'];
  $currentLevelName = $user['level_name'];

  // Imagen de rango según level_id
  switch ($currentLevelId) {
    case 1:
      $rangoImagen = "../../img/rangos/1RECRUIT.png";
      break;
    case 2:
      $rangoImagen = "../../img/rangos/2BRONZE.png";
      break;
    case 3:
      $rangoImagen = "../../img/rangos/3SILVER.png";
      break;
    case 4:
      $rangoImagen = "../../img/rangos/4GOLD.png";
      break;
    case 5:
      $rangoImagen = "../../img/rangos/5PLATINIUM.png";
      break;
    case 6:
      $rangoImagen = "../../img/rangos/6DIAMOND.png";
      break;
    case 7:
      $rangoImagen = "../../img/rangos/7DIAMOND.png";
      break;
    case 8:
      $rangoImagen = "../../img/rangos/8ONIX.png";
      break;
    case 9:
      $rangoImagen = "../../img/rangos/9ONIX.png";
      break;
    case 10:
    default:
      $rangoImagen = "../../img/rangos/10HERO.png";
      break;
  }

  // Avatar
  $avatarImg = '../../img/personajes/117.png';
  $avatarName = 'Spartan';

  $stmtAvatar = $con->prepare("
    SELECT a.image_url, a.name
    FROM users u
    LEFT JOIN avatars a ON u.id_avatar = a.id_avatar
    WHERE u.username = :username
  ");
  $stmtAvatar->execute(['username' => $username]);
  $row = $stmtAvatar->fetch(PDO::FETCH_ASSOC);

  if ($row && !empty($row['image_url'])) {
    $avatarImg = "../../" . ltrim($row['image_url'], "./");
    $avatarName = $row['name'] ?? $avatarName;
  }
} catch (PDOException $e) {
  error_log("Error en lobby.php: " . $e->getMessage());
}

// ---------------- VERIFICAR / ASIGNAR MUNDO ----------------
$stmtUserWorld = $con->prepare("SELECT id_world FROM users WHERE id_user = ?");
$stmtUserWorld->execute([$id_user]);
$id_world = $stmtUserWorld->fetchColumn();

if (!$id_world) {
  $defaultWorld = 1; // 🌍 Berserker por defecto
  $setWorld = $con->prepare("UPDATE users SET id_world = ? WHERE id_user = ?");
  $setWorld->execute([$defaultWorld, $id_user]);
  $id_world = $defaultWorld;
}

// Obtener datos del mundo
$worldQuery = $con->prepare("
  SELECT name AS world_name, image AS world_image
  FROM worlds
  WHERE id_world = ?
");
$worldQuery->execute([$id_world]);
$worldData = $worldQuery->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Lobby | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/stile.css">

  <style>
    .back-btn {
      position: absolute;
      top: 10px;
      left: 300px;
      background: linear-gradient(90deg, #00bcd4, #005f73);
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: bold;
      text-decoration: none;
    }
  </style>

</head>

<body class="halo-bg">
  <div class="container-fluid vh-100 d-flex align-items-center">
    <div class="row w-100 align-items-stretch">

      <!-- Columna Izquierda -->
      <div class="col-md-4 d-flex flex-column">
        <div class="column-box d-flex flex-column justify-content-between h-100">

          <!-- 🌍 Mapa del mundo -->
          <div class="w-100 mb-4">
            <img src="../../<?= htmlspecialchars($worldData['world_image']) ?>"
              class="d-block w-100 rounded"
              alt="<?= htmlspecialchars($worldData['world_name']) ?>">

            <div class="text-center mt-2 text-info fs-5">
              Mundo: <?= htmlspecialchars($worldData['world_name']) ?>
            </div>
          </div>

          <!-- Menu -->
          <div class="menu-left text-start mt-auto w-100">
            <button class="btn btn-outline-success nav-btn w-100 mb-3" onclick="window.location.href='partidas.php'">Partidas</button>
            <button class="btn btn-outline-warning nav-btn w-100 mb-3" onclick="window.location.href='weapons.php'">Armas</button>
            <button class="btn btn-outline-info nav-btn w-100" onclick="window.location.href='avatars.php'">Personajes</button>
          </div>
        </div>
      </div>

      <!-- Columna Central -->
      <div class="col-md-4 d-flex flex-column">
        <div class="column-box d-flex flex-column justify-content-center align-items-center text-center">
          <img src="<?= htmlspecialchars($avatarImg) ?>" alt="<?= htmlspecialchars($avatarName) ?>" class="spartan-img img-fluid mb-3">
          <p class="text-light fs-5">¡Prepárate para la batalla, <?= htmlspecialchars($avatarName) ?>!</p>
        </div>
      </div>

      <!-- Columna Derecha -->
      <div class="col-md-4 d-flex flex-column">
        <div class="column-box position-relative d-flex flex-column justify-content-center align-items-center">

          <!-- Logout -->
          <a href="../../controller/logout.php" class="back-btn">
            CERRAR SESIÓN
          </a>

          <div class="user-rank text-center mt-3">
            <div class="mb-3">
              <img src="<?= htmlspecialchars($rangoImagen) ?>" alt="Rango" width="250" height="250">
            </div>
            <h3>Bienvenido, <span class="text-primary"><?= htmlspecialchars($username) ?></span></h3>
            <p class="text-warning fs-5 mb-2">Rango: <?= htmlspecialchars($currentLevelName) ?></p>
            <p class="text-info fs-5 mb-0">Puntos: <?= $points ?> </p>
          </div>

          <!-- JUGAR -->
          <div class="play-btn-container mt-3">
            <button class="play-btn" onclick="window.location.href='select_world.php'">JUGAR</button>
          </div>

        </div>
      </div>

    </div>
  </div>

  <script src="../../controller/bootstrap/js/bootstrap.bundle.min.js"></script>
  <audio id="lobbyMusic" autoplay loop>
    <source src="../../audio/intro.mp3" type="audio/mpeg">
    Tu navegador no soporta audio HTML5.
  </audio>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const audio = document.getElementById("lobbyMusic");

      // Asegura que el audio empiece apenas el DOM está listo
      audio.volume = 0.4; // volumen moderado (40%)

      // Si el navegador bloquea el autoplay, intentamos reproducirlo tras una interacción mínima
      document.body.addEventListener('click', () => {
        if (audio.paused) audio.play();
      }, {
        once: true
      });
    });
  </script>

</body>

</html>