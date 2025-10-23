<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

$username = $_SESSION['usuario'];

//  Obtener datos del usuario
$stmt = $conexion->prepare("
  SELECT u.id_user, u.points, u.level_id, l.name AS level_name
  FROM users u
  LEFT JOIN levels l ON u.level_id = l.level_id
  WHERE u.username = ?
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$id_user   = $user['id_user'];
$levelId   = $user['level_id'] ?? 1;
$levelName = $user['level_name'] ?? "Recluta";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Salas | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/worlds.css">
  <style>
    /*  Efecto de bloqueo visual */
    .locked {
      filter: grayscale(100%) brightness(0.6);
      pointer-events: none;
      position: relative;
    }

    .locked::after {
      content: "🔒 Bloqueado";
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: rgba(0, 0, 0, 0.75);
      color: #ff0000;
      padding: 10px 18px;
      border-radius: 10px;
      font-weight: bold;
      text-shadow: 0 0 8px rgba(255, 0, 0, 0.6);
    }

    .require-text {
      font-size: 0.9rem;
      color: #bbb;
      margin-top: 8px;
    }
  </style>
</head>
<body class="background">

  <!--  BOTONES SUPERIORES -->
  <div class="volver-container top-left">
    <a href="lobby.php" class="volver-btn">⬅️ Volver al Lobby</a>
  </div>

  <!--  Título -->
  <h1 class="titulo">🌌 Salas disponibles, <?php echo htmlspecialchars($username); ?> (<?php echo $levelName; ?>)</h1>

  <!--  Tarjetas de Mapas -->
  <div class="worlds-container">
    <?php
    $query = "SELECT id_world, name, image, required_level FROM worlds ORDER BY id_world ASC";
    $result = $conexion->query($query);

    while ($world = $result->fetch_assoc()) {
      $bloqueado = $levelId < $world['required_level'];
    ?>
      <div class="world-card <?php echo $bloqueado ? 'locked' : ''; ?>">
        <div class="world-img-container">
          <img src="../../<?php echo htmlspecialchars($world['image']); ?>" 
               alt="<?php echo htmlspecialchars($world['name']); ?>" 
               class="world-img">
        </div>
        <h3><?php echo htmlspecialchars($world['name']); ?></h3>

        <?php if (!$bloqueado) { ?>
          <form action="../../controller/join_world.php" method="POST">
            <input type="hidden" name="world_id" value="<?php echo $world['id_world']; ?>">
            <input type="hidden" name="id_user" value="<?php echo $id_user; ?>">
            <button type="submit" class="select-btn">🚀 Entrar</button>
          </form>
        <?php } else { ?>
          <div class="require-text">🔒 Requiere nivel <?php echo $world['required_level']; ?></div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>

</body>
</html>
