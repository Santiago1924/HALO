<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

// 📡 Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

$username = $_SESSION['usuario'] ?? 'Spartan';

// 🧠 Obtener datos del usuario
$stmt = $conexion->prepare("
  SELECT u.id_user, u.points, u.level_id, l.name AS level_name, l.min_points
  FROM users u
  LEFT JOIN levels l ON u.level_id = l.level_id
  WHERE u.username = ?
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$points = $user['points'] ?? 0;
$currentLevelId = $user['level_id'] ?? 1;
$currentLevelName = $user['level_name'] ?? "Recluta";

// 📈 Verificar si el usuario debe subir de nivel
$nextLevelQuery = $conexion->query("
  SELECT level_id, name, min_points FROM levels 
  WHERE min_points <= $points 
  ORDER BY min_points DESC 
  LIMIT 1
");

if ($nextLevelQuery && $nextLevelQuery->num_rows > 0) {
  $levelData = $nextLevelQuery->fetch_assoc();

  if ($levelData['level_id'] != $currentLevelId) {
    $update = $conexion->prepare("UPDATE users SET level_id = ? WHERE username = ?");
    $update->bind_param("is", $levelData['level_id'], $username);
    $update->execute();

    $currentLevelId = $levelData['level_id'];
    $currentLevelName = $levelData['name'];
  }
}

// 🏅 Imagen del rango según el nivel
$rangoImagen = "../../img/rangos/bronce.png"; // valor por defecto
if ($currentLevelId >= 2 && $currentLevelId < 4) {
  $rangoImagen = "../../img/rangos/cabo.png";
} elseif ($currentLevelId >= 4 && $currentLevelId < 6) {
  $rangoImagen = "../../img/rangos/sargento.png";
} elseif ($currentLevelId >= 6 && $currentLevelId < 8) {
  $rangoImagen = "../../img/rangos/teniente.png";
} elseif ($currentLevelId >= 8) {
  $rangoImagen = "../../img/rangos/comandante.png";
}

// ⚙️ Obtener avatar del usuario
$avatarImg = '../../img/personajes/117.png'; // por defecto
$avatarName = 'Spartan';

// Usamos PDO aquí para más flexibilidad (puedes mantener mysqli si prefieres)
try {
  $pdo = new PDO("mysql:host=localhost;dbname=halo_style;charset=utf8", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare("
    SELECT a.image_url, a.name
    FROM users u
    LEFT JOIN avatars a ON u.id_avatar = a.id_avatar
    WHERE u.username = ?
  ");
  $stmt->execute([$username]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($row && !empty($row['image_url'])) {
    // Aseguramos la ruta correcta relativa a model/user/
    $avatarImg = "../../" . ltrim($row['image_url'], "./");
    $avatarName = $row['name'] ?? $avatarName;
  }
} catch (PDOException $e) {
  error_log("Error al obtener avatar: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lobby | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/stile.css">
</head>
<body class="halo-bg">

  <div class="container-fluid vh-100 d-flex align-items-center">
    <div class="row w-100 align-items-stretch">

      <!-- 🧭 COLUMNA IZQUIERDA -->
      <div class="col-md-4 d-flex flex-column">
        <div class="column-box d-flex flex-column justify-content-between h-100">

          <!-- 🌀 Carrusel -->
          <div id="haloCarousel" class="carousel slide w-100 mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active">
                <img src="../../img/mapas/LIVE_FIRE.jpeg" class="d-block w-100" alt="LIVE_FIRE">
              </div>
              <div class="carousel-item">
                <img src="../../img/mapas/RECHARGE.jpeg" class="d-block w-100" alt="RECHARGE">
              </div>
              <div class="carousel-item">
                <img src="../../img/mapas/BREAKER.jpeg" class="d-block w-100" alt="BREAKER">
              </div>
            </div>
          </div>

          <!-- 📁 Menú de navegación -->
          <div class="menu-left text-start mt-auto w-100">
            <button class="btn btn-outline-success nav-btn w-100 mb-3" onclick="window.location.href='partidas.php'">Partidas</button>
            <button class="btn btn-outline-warning nav-btn w-100 mb-3" onclick="window.location.href='weapons.php'">Armas</button>
            <button class="btn btn-outline-info nav-btn w-100" onclick="window.location.href='avatars.php'">Personajes</button>
          </div>

        </div>
      </div>

      <!-- 🪖 COLUMNA CENTRAL -->
      <div class="col-md-4 d-flex flex-column">
        <div class="column-box d-flex flex-column justify-content-center align-items-center text-center">
          <img src="<?= htmlspecialchars($avatarImg) ?>" alt="<?= htmlspecialchars($avatarName) ?>" class="spartan-img img-fluid mb-3">
          <p class="text-light fs-5">¡Prepárate para la batalla, <?= htmlspecialchars($avatarName) ?>!</p>
        </div>
      </div>

      <!-- 📊 COLUMNA DERECHA -->
      <div class="col-md-4 d-flex flex-column">
        <div class="column-box d-flex flex-column justify-content-center align-items-center">

          <div class="user-rank text-center">
            <div class="mb-3">
              <img src="<?= $rangoImagen ?>" alt="Rango" width="250" height="250">
            </div>
            <h3>Bienvenido, <span class="text-primary"><?= htmlspecialchars($username) ?></span></h3>
            <p class="text-warning fs-5 mb-2">Rango: <?= htmlspecialchars($currentLevelName) ?></p>
            <p class="text-info fs-5 mb-0">Puntos: <?= $points ?> | Nivel actual: <?= $currentLevelId ?></p>
          </div>

          <!-- 🎮 Botón JUGAR -->
          <div class="play-btn-container mt-4">
            <button class="play-btn" onclick="window.location.href='sala.php'">JUGAR</button>
          </div>

        </div>
      </div>

    </div>
  </div>

  <script src="../../controller/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
  
</body>
</html>
