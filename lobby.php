<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Lobby | Halo Style</title>
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <!-- Menú lateral -->
  <div class="menu">
    <a href="partidas.php" class="menu-btn">PARTIDAS</a>
    <a href="avatar.php" class="menu-btn">AVATAR</a>
    <a href="armas.php" class="menu-btn">ARMAS</a>
  </div>

  <!-- Avatar central -->
  <div class="avatar-container">
    <img src="images/spartan.png" alt="Avatar" class="avatar">
  </div>

  <!-- Bienvenida -->
  <div class="welcome">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> 👋</h2>
  </div>

  <!-- Botón jugar -->
  <div class="play-container">
    <form action="partidas.php" method="GET">
      <button class="play-btn">JUGAR</button>
    </form>
  </div>

</body>
</html>
