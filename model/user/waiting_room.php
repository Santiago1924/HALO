<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../../index.php");
  exit();
}

$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

$username = $_SESSION['usuario'];
$id_user = $conexion->query("SELECT id_user FROM users WHERE username = '$username'")->fetch_assoc()['id_user'];

$id_lobby = intval($_GET['world_id'] ?? 1);

// Crear lobby si no existe
$lobby = $conexion->query("SELECT * FROM game_lobby WHERE id_lobby = $id_lobby")->fetch_assoc();
if (!$lobby) {
  $conexion->query("INSERT INTO game_lobby (lobby_name) VALUES ('Sala #$id_lobby')");
  $lobby = $conexion->query("SELECT * FROM game_lobby WHERE id_lobby = $id_lobby")->fetch_assoc();
}

// Insertar jugador si no está
$exists = $conexion->query("SELECT * FROM lobby_players WHERE id_user = $id_user AND id_lobby = $id_lobby");
if ($exists->num_rows === 0) {
  $conexion->query("INSERT INTO lobby_players (id_lobby, id_user) VALUES ($id_lobby, $id_user)");
}

// Obtener jugadores
$players = $conexion->query("
  SELECT u.username, a.image_url
  FROM lobby_players lp
  JOIN users u ON lp.id_user = u.id_user
  LEFT JOIN avatars a ON u.id_avatar = a.id_avatar
  WHERE lp.id_lobby = $id_lobby
");

$total_players = $players->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Sala de Espera | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body { background-color: #0d0d0d; color: white; text-align: center; padding: 40px; }
    .player-card { display: inline-block; margin: 10px; padding: 10px; border: 1px solid #555; border-radius: 10px; width: 150px; background-color: #1a1a1a; }
    .player-card img { width: 100px; border-radius: 10px; }
    .start-btn { margin-top: 20px; padding: 10px 20px; font-weight: bold; border-radius: 10px; }
  </style>
</head>
<body>

  <h1>🕹️ Sala de Espera: <?php echo htmlspecialchars($lobby['lobby_name']); ?></h1>
  <h4>Jugadores conectados: <span id="count"><?php echo $total_players; ?></span></h4>

  <div id="player-list">
    <?php while ($p = $players->fetch_assoc()) { ?>
      <div class="player-card">
        <img src="../../<?php echo htmlspecialchars($p['image_url'] ?? 'img/default.png'); ?>" alt="avatar">
        <p><?php echo htmlspecialchars($p['username']); ?></p>
      </div>
    <?php } ?>
  </div>

  <?php if ($total_players >= 2): ?>
    <form action="../../controller/start_game.php" method="POST">
      <input type="hidden" name="id_lobby" value="<?php echo $id_lobby; ?>">
      <button type="submit" class="btn btn-success start-btn">🚀 Empezar partida</button>
    </form>
  <?php else: ?>
    <p style="margin-top:20px;">Esperando más jugadores...</p>
  <?php endif; ?>

  <script>
    // 🔄 Actualizar lista y contar jugadores
    setInterval(() => {
      $("#player-list").load(location.href + " #player-list>*", "");
      $.get("../../controller/check_lobby_status.php", { id_lobby: <?php echo $id_lobby; ?> }, function(data) {
        const res = JSON.parse(data);
        $("#count").text(res.players);
        if (res.started == 1) {
          window.location.href = "battle.php";
        }
      });
    }, 2000);
  </script>

</body>
</html>
