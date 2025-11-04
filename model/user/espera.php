<?php
session_start();
require_once("../../database/conexion.php");

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_user'])) {
  header("Location: ../../index.php");
  exit();
}

$db = new Database();
$con = $db->conectar();

$id_user = $_SESSION['id_user'];
$id_game = isset($_GET['id_game']) ? (int)$_GET['id_game'] : 0;

if ($id_game <= 0) {
  die("<h2 style='color:red;text-align:center;margin-top:50px'>❌ Partida no válida.</h2>");
}

// ✅ Obtener información del mundo
$stmt = $con->prepare("
  SELECT w.id_world, w.name AS world_name, w.image
  FROM games g
  JOIN rooms r ON g.id_room = r.id_room
  JOIN worlds w ON r.world_id = w.id_world
  WHERE g.id_games = ?
");
$stmt->execute([$id_game]);
$world = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$world) die("❌ Mundo no encontrado");

// ✅ Obtener nivel del jugador
$stmt = $con->prepare("
  SELECT u.level_id, l.name AS level_name
  FROM users u
  LEFT JOIN levels l ON u.level_id = l.level_id
  WHERE u.id_user = ?
");
$stmt->execute([$id_user]);
$player = $stmt->fetch(PDO::FETCH_ASSOC);
$player_level = $player['level_name'] ?? 'Sin nivel';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($world['world_name']) ?> - Sala de Espera</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      background: url('../../img/mapas/sala_partida.jpeg') center/cover no-repeat fixed;
      color: #fff;
      font-family: 'Segoe UI', sans-serif;
      text-align: center;
    }

    .container {
      background: rgba(0, 0, 0, 0.75);
      margin-top: 60px;
      padding: 20px;
      border-radius: 10px;
      display: inline-block;
      width: 85%;
      box-shadow: 0 0 12px #00eaff;
    }

    .team-container {
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .team-box {
      width: 48%;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 10px;
      padding: 10px;
      min-height: 280px;
    }

    .team-box h4 {
      color: #00eaff;
      font-weight: bold;
      margin-bottom: 15px;
    }

    .player-slot {
      margin: 6px auto;
      padding: 10px;
      background: rgba(255, 255, 255, 0.08);
      border-radius: 6px;
      width: 90%;
      text-align: center;
      font-size: 14px;
    }

    .empty-slot {
      color: #999;
      font-style: italic;
    }

    .ready {
      color: #00ff88;
      font-weight: bold;
    }

    .waiting {
      color: #ffcc00;
    }

    .btn {
      background: #00bcd4;
      border: none;
      color: white;
      font-weight: bold;
      padding: 8px 16px;
      border-radius: 8px;
      margin: 5px;
    }

    .level-box {
      background: rgba(0, 234, 255, 0.15);
      border: 1px solid #00eaff;
      padding: 8px 16px;
      border-radius: 8px;
      display: inline-block;
      margin-top: 10px;
      font-weight: bold;
      color: #00eaff;
    }

    #estado {
      font-weight: bold;
      font-size: 18px;
      margin-top: 15px;
      color: #00eaff;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>⚔️ Sala de Espera - <?= htmlspecialchars($world['world_name']) ?></h2>
    <div class="level-box">👤 Nivel del jugador: <?= htmlspecialchars($player_level) ?></div>
    <div id="estado">Cargando...</div>

    <div class="team-container">
      <div class="team-box" id="teamA">
        <h4>Equipo A</h4>
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <div class="player-slot empty-slot" id="slotA<?= $i ?>">🕳 Vacante disponible</div>
        <?php endfor; ?>
      </div>

      <div class="team-box" id="teamB">
        <h4>Equipo B</h4>
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <div class="player-slot empty-slot" id="slotB<?= $i ?>">🕳 Vacante disponible</div>
        <?php endfor; ?>
      </div>
    </div>

    <div id="acciones" class="mt-4"></div>
  </div>
  <script>
    const id_user = <?= $id_user ?>;
    const id_game = <?= $id_game ?>;
    const id_world = <?= $world['id_world'] ?>;

    let contador = 5;
    let cuentaAtras = null;

    // 🔁 actualizar sala
    function actualizar() {
      $.post("verificar_estado_sala.php", {
        id_game,
        id_user
      }, function(data) {

        if (data.error) {
          $("#estado").text("⚠️ " + (data.msg || "Error"));
          return;
        }

        for (let i = 1; i <= 5; i++) {
          $("#slotA" + i).html("🕳 Vacante disponible").addClass("empty-slot").removeAttr("data-userid");
          $("#slotB" + i).html("🕳 Vacante disponible").addClass("empty-slot").removeAttr("data-userid");
        }

        let A = 1,
          B = 1;
        data.jugadores.forEach(j => {

          // ✅ Estado corregido
          let estado;
          if (j.status === "left") {
            estado = `<span style="color:#ff3030;font-weight:bold;">🚫 Abandonó</span>`;
          } else if (j.online == 0) {
            estado = `<span style="color:#c7c7c7;font-weight:bold;">⚪ Inactivo</span>`;
          } else {
            estado = j.ready == 1 ?
              '<span class="ready">✔ Listo</span>' :
              '<span class="waiting">⏳ Esperando</span>';
          }

          const texto = `<strong>${j.username}</strong> ${estado}`;

          if (j.team == 1 && A <= 5) {
            $("#slotA" + A).html(texto).removeClass("empty-slot").attr("data-userid", j.id_user);
            A++;
          } else if (j.team == 2 && B <= 5) {
            $("#slotB" + B).html(texto).removeClass("empty-slot").attr("data-userid", j.id_user);
            B++;
          }
        });

        $("#estado").text(data.texto_estado);

        let botones = "";
        if (!data.este_ready) {
          botones += `<button class='btn' onclick="accion('ready')">✔ Listo</button>`;
          botones += `<button class='btn' onclick="accion('salir')">❌ Salir</button>`;
        } else {
          botones += `<button class='btn' onclick="accion('no_ready')">Cancelar</button>`;
        }

        if (!data.started && !cuentaAtras) {
          botones += `<button class='btn btn-warning' onclick="accion('cambiar_equipo')">🔁 Equipo</button>`;
        }

        $("#acciones").html(botones);

        if (data.ready_all && data.teamA >= 1 && data.teamB >= 1) {
          if (!cuentaAtras) iniciarCuentaAtras();
        } else {
          if (cuentaAtras) {
            clearInterval(cuentaAtras);
            cuentaAtras = null;
            contador = 5;
          }
        }

        if (data.started == 1) {
          clearInterval(cuentaAtras);
          window.location.href = "battle.php?id_game=" + id_game;
        }
      }, "json");
    }

    // ⚙️ acciones
    function accion(tipo) {
      $.post("actualizar_estado_sala.php", {
        id_user,
        id_game,
        accion: tipo
      }, function() {
        if (tipo === "salir") {
          document.cookie = "room_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
          window.location.href = "world_rooms.php?id_world=" + id_world;
        }
      });
    }

    // ⏳ cuenta
    function iniciarCuentaAtras() {
      cuentaAtras = setInterval(() => {
        $("#estado").text("🚀 En " + contador + "s...");
        contador--;
        if (contador < 0) {
          clearInterval(cuentaAtras);
          $.post("actualizar_estado_sala.php", {
            id_game,
            accion: "iniciar"
          });
        }
      }, 1000);
    }

    // 🚪 cerrar pestaña = abandono real
    window.addEventListener("beforeunload", () => {
      const blob = new Blob([JSON.stringify({
        id_user,
        id_game
      })], {
        type: "application/json"
      });
      navigator.sendBeacon("player_exit.php", blob);
      document.cookie = "room_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    });

    setInterval(actualizar, 1000);
  </script>

</body>

</html>