<?php 
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../../index.php");
  exit();
}

// 🔹 Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "halo_style");
if ($conexion->connect_error) {
  die("Error de conexión: " . $conexion->connect_error);
}

$username = $_SESSION['usuario'];

// 🔹 Obtener datos del jugador
$stmt = $conexion->prepare("
  SELECT u.id_user, u.id_avatar, a.name AS avatar_name, a.image_url AS avatar_img
  FROM users u
  LEFT JOIN avatars a ON u.id_avatar = a.id_avatar
  WHERE u.username = ?
");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$id_user = $user['id_user'];
$avatar = $user['avatar_name'] ?? 'Desconocido';
$avatar_img = $user['avatar_img'] ?? 'img/default.png';

// 🔹 Buscar enemigo (cualquier jugador distinto)
$enemy = $conexion->query("
  SELECT rp.id_room_player, rp.current_hp, u.username, a.image_url
  FROM room_players rp
  JOIN users u ON rp.id_user = u.id_user
  LEFT JOIN avatars a ON u.id_avatar = a.id_avatar
  WHERE rp.id_user != $id_user
  LIMIT 1
")->fetch_assoc();

$enemy_hp = $enemy['current_hp'] ?? 100;
$enemy_name = $enemy['username'] ?? "Enemigo";
$enemy_img = $enemy['image_url'] ?? "img/personajes/elite.png";

// 🔹 Obtener vida actual del jugador
$player = $conexion->query("
  SELECT current_hp 
  FROM room_players 
  WHERE id_user = $id_user
")->fetch_assoc();
$player_hp = $player['current_hp'] ?? 100;

// 🔹 Obtener armas y partes del cuerpo
$weapons = $conexion->query("SELECT id_weapons, name FROM weapons");
$body_parts = $conexion->query("SELECT id_damage_body, name FROM damage_part_body");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Combate Online | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      background-color: #0a0a0a;
      color: #fff;
      text-align: center;
      padding: 20px;
    }
    .arena {
      display: flex;
      justify-content: space-around;
      align-items: center;
      margin-top: 30px;
    }
    .player, .enemy {
      text-align: center;
    }
    img {
      max-width: 180px;
      border-radius: 10px;
      margin-bottom: 10px;
    }
    .health-bar {
      width: 220px;
      height: 25px;
      background-color: #333;
      border-radius: 10px;
      overflow: hidden;
      border: 2px solid #666;
      margin: 10px auto;
    }
    .health-fill {
      height: 100%;
      background: linear-gradient(to right, #00ff00, #ff0000);
      transition: width 0.3s;
    }
    .volver-btn {
      background-color: #dc3545;
      color: white;
      padding: 10px 15px;
      border-radius: 10px;
      text-decoration: none;
      font-weight: bold;
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .volver-btn:hover {
      background-color: #b52a35;
    }
    #battle-log {
      margin-top: 20px;
      max-width: 400px;
      margin-left: auto;
      margin-right: auto;
    }
  </style>
</head>
<body>

  <a href="lobby.php" class="volver-btn">⬅️ Volver al Lobby</a>
  <h1>⚔️ Combate Online</h1>

  <div class="arena">
    <div class="player">
      <h3>🟩 Tú: <?php echo htmlspecialchars($username); ?></h3>
      <img src="../../<?php echo htmlspecialchars($avatar_img); ?>" alt="Tu avatar">
      <div class="health-bar">
        <div id="player-hp" class="health-fill" style="width: <?php echo $player_hp; ?>%;"></div>
      </div>
      <p><strong id="player-hp-text"><?php echo $player_hp; ?> / 100 HP</strong></p>
    </div>

    <div class="enemy">
      <h3>🔴 Enemigo: <?php echo htmlspecialchars($enemy_name); ?></h3>
      <img src="../../<?php echo htmlspecialchars($enemy_img); ?>" alt="Enemigo">
      <div class="health-bar">
        <div id="enemy-hp" class="health-fill" style="width: <?php echo $enemy_hp; ?>%;"></div>
      </div>
      <p><strong id="enemy-hp-text"><?php echo $enemy_hp; ?> / 100 HP</strong></p>
    </div>
  </div>

  <!-- Formulario de ataque -->
  <form id="attack-form" class="mt-4">
    <input type="hidden" name="id_user" value="<?php echo $id_user; ?>">
    <input type="hidden" name="enemy_id" value="<?php echo $enemy['id_room_player']; ?>">

    <label>Selecciona un arma:</label>
    <select name="id_weapon" class="form-control" required>
      <option value="">-- Selecciona arma --</option>
      <?php while ($row = $weapons->fetch_assoc()) { ?>
        <option value="<?php echo $row['id_weapons']; ?>"><?php echo $row['name']; ?></option>
      <?php } ?>
    </select>

    <label>Selecciona parte del cuerpo:</label>
    <select name="id_body" class="form-control" required>
      <option value="">-- Parte del cuerpo --</option>
      <?php while ($part = $body_parts->fetch_assoc()) { ?>
        <option value="<?php echo $part['id_damage_body']; ?>"><?php echo $part['name']; ?></option>
      <?php } ?>
    </select>

    <button type="submit" class="btn btn-success mt-3">💥 Disparar</button>
  </form>

  <div id="battle-log" class="alert alert-info" style="display:none;"></div>

  <script>
    // 💥 Disparo con AJAX
    $("#attack-form").on("submit", function(e) {
      e.preventDefault();
      $.post("../../controller/disparo.php", $(this).serialize(), function(data) {
        let res = JSON.parse(data);
        $("#battle-log").show().text(res.message);
        updateBars();
      });
    });

    // 🔄 Actualización periódica de HP
    function updateBars() {
      $.get("../../controller/get_hp.php", function(data) {
        let hp = JSON.parse(data);
        $("#player-hp").css("width", hp.player_hp + "%");
        $("#enemy-hp").css("width", hp.enemy_hp + "%");
        $("#player-hp-text").text(hp.player_hp + " / 100 HP");
        $("#enemy-hp-text").text(hp.enemy_hp + " / 100 HP");
      });
    }

    setInterval(updateBars, 2000);
  </script>

</body>
</html>
