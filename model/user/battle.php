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
$id_game = $_GET['id_game'] ?? 0;
if ($id_game <= 0) die("❌ Partida inválida.");

// ✅ Verificar partida iniciada
$stmt = $con->prepare("SELECT started FROM games WHERE id_games = ?");
$stmt->execute([$id_game]);
if ((int)$stmt->fetchColumn() !== 1) {
    header("Location: espera.php?id_game=$id_game");
    exit();
}

// ✅ Obtener info del mundo
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
$mapa_img = "../../" . htmlspecialchars($world['image']);

// ✅ Datos jugador actual
$stmt = $con->prepare("
    SELECT u.username, u.id_user, u.level_id, l.name AS level_name,
            a.image_url AS avatar_img, rp.team, rp.current_hp
    FROM room_players rp
    JOIN users u ON rp.id_user = u.id_user
    LEFT JOIN levels l ON u.level_id = l.level_id
    LEFT JOIN avatars a ON u.id_avatar = a.id_avatar
    WHERE rp.id_games = ? AND rp.id_user = ?
");
$stmt->execute([$id_game, $id_user]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("❌ No estás en esta partida.");

$nivel = (int)$user['level_id'];
$avatar_img = "../../" . ($user['avatar_img'] ?? "img/personajes/default_avatar.png");

// ✅ Armas disponibles (AÑADIDO: w.bullets)
$stmt = $con->prepare("
    SELECT w.id_weapons, w.name, w.damage, w.image_url, w.level_arm, w.bullets,
            COALESCE(v.video_url, '') AS video_url
    FROM weapons w
    LEFT JOIN weapon_videos v ON w.id_weapons = v.id_weapon
    WHERE w.level_arm <= ?
    ORDER BY w.level_arm ASC
");
$stmt->execute([$nivel]);
$armas = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$armas) die("⚠️ No tienes armas disponibles.");

foreach ($armas as &$a) {
    $a['img_full'] = "../../img/armas/" . $a['image_url'];
    $a['video_full'] = $a['video_url'] ? "../../" . $a['video_url'] : "../../video/animacion_armas/videospistola_magnum.mp4";
}
unset($a);

$matchDuration = 5 * 60;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Batalla - Halo Style</title>
    <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <audio id="battle_audio" autoplay loop>
        <source src="../../audio/battle_audio.mp3" type="audio/mp3">
        Tu navegador no soporta audio HTML5.
    </audio>

    <style>
        /* Estilos mínimos, el resto con Bootstrap */
        body {
            margin: 0;
            background: black;
            color: white;
            font-family: 'Segoe UI';
            overflow: hidden;
        }

        #battle-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        #map-layer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('<?= $mapa_img ?>') center/cover no-repeat;
            opacity: 0.25;
            z-index: 2;
        }

        /* Estilos para timer y otros fijos que no son de Bootstrap */
        #global-timer {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.75);
            border: 2px solid #00eaff;
            border-radius: 8px;
            padding: 6px 20px;
            font-weight: bold;
            font-size: 22px;
            color: #00eaff;
            z-index: 10;
            box-shadow: 0 0 15px #00eaff;
        }

        /* Contenedores de información - AHORA EN EL LADO DERECHO */
        .player-info {
            position: fixed;
            bottom: 110px;
            right: 30px;
            left: auto;
            z-index: 10;
            width: 320px;
        }

        /* Abajo a la derecha */
        .weapon-info {
            position: fixed;
            top: 30px;
            right: 30px;
            left: auto;
            z-index: 10;
            width: 280px;
        }

        /* Arriba a la derecha */

        /* Estilos de oponentes */
        .opponent {
            background: rgba(0, 0, 0, 0.65);
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            width: 100%;
            box-shadow: 0 0 8px rgba(0, 234, 255, 0.4);
            border: 1px solid #00eaff;
        }

        .opponent img {
            width: 70px;
            border-radius: 8px;
        }

        .health-bar {
            width: 100%;
            height: 10px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            overflow: hidden;
            margin-top: 6px;
        }

        .health-fill {
            height: 100%;
            background: linear-gradient(90deg, #0f0, #f00);
            width: 100%;
            transition: width .3s;
        }

        /* Panel de control */
        .weapon-panel {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(0, 0, 0, 0.9);
            border-top: 2px solid #00eaff;
            padding: 12px 0;
            z-index: 10;
        }

        /* Log de daño */
        #damage-log {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 12;
            text-align: right;
        }

        .damage-msg {
            background: rgba(0, 0, 0, 0.8);
            padding: 8px 12px;
            margin-top: 6px;
            border-radius: 6px;
            animation: fadeOut 2.2s forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        /* Selects y Botones con estilo futurista */
        .form-select,
        .form-control,
        #target-select {
            background: rgba(0, 0, 0, 0.85);
            border: 1px solid #00eaff;
            color: white !important;
            box-shadow: 0 0 5px rgba(0, 234, 255, 0.4);
        }

        .btn-fire {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 0 8px #ff4444;
        }

        .btn-fire:hover {
            background: #ff2222;
        }

        .btn-exit {
            background: #00eaff;
            color: black;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 0 8px #00eaff;
        }

        .btn-exit:hover {
            background: #00b8cc;
        }
    </style>
    <script>
        // 🔒 Evitar volver con el navegador
        history.pushState(null, null, location.href);
        window.onpopstate = () => history.go(1);
    </script>
</head>

<body>

    <video id="battle-video" autoplay muted loop>
        <source src="<?= htmlspecialchars($armas[0]['video_full']) ?>" type="video/mp4">
    </video>
    <div id="map-layer"></div>
    <div id="global-timer">⏱️ 05:00</div>

    <div class="container opponents">
        <div class="row justify-content-center g-3 mt-5" id="opponentZone">
        </div>
    </div>

    <div class="weapon-panel">
        <div class="container">
            <div class="row align-items-center justify-content-center g-2">
                <div class="col-auto">
                    <select id="arma" class="form-select">
                        <?php foreach ($armas as $a):
                            $opt = [
                                "id_weapons" => (int)$a['id_weapons'],
                                "name" => $a['name'],
                                "damage" => (int)$a['damage'],
                                "bullets" => (int)$a['bullets'],
                                "img" => $a['img_full'],
                                "video" => $a['video_full']
                            ];
                        ?>
                            <option value='<?= json_encode($opt) ?>'>
                                <?= htmlspecialchars($a['name']) ?> — <?= (int)$a['damage'] ?> dmg
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="zona-select" class="form-select">
                        <option value="head">Cabeza (x1.8)</option>
                        <option value="body" selected>Cuerpo (x1.0)</option>
                        <option value="legs">Piernas (x0.7)</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select id="target-select" class="form-select">
                        <option value="">Selecciona oponente</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button id="fire-btn" class="btn btn-fire rounded-0">💥 Disparar</button>
                </div>
                <div class="col-auto">
                    <button id="exit-btn" class="btn btn-exit rounded-0">🚪 Abandonar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="player-info card bg-dark border border-info shadow-lg">
        <div class="card-body">
            <img src="<?= htmlspecialchars($avatar_img) ?>" alt="avatar" class="img-fluid rounded-circle mx-auto d-block" style="width:110px;">
            <h4 class="mt-2 text-info text-center"><?= htmlspecialchars($user['username']) ?></h4>
            <p class="mb-1 text-center">Nivel: <?= htmlspecialchars($user['level_name']) ?> | Equipo: <?= htmlspecialchars($user['team']) ?></p>
            <div class="health-bar">
                <div class="health-fill" id="vidaJugador" style="width:100%"></div>
            </div>
        </div>
    </div>

    <div class="weapon-info card bg-dark border border-info shadow-lg">
        <div class="card-body text-center">
            <h5 class="text-warning">Arma seleccionada</h5>
            <img id="arma-img" src="<?= htmlspecialchars($armas[0]['img_full']) ?>" class="img-fluid my-2" style="width:150px;">
            <p id="arma-nombre" class="fw-bold fs-5 mb-1"><?= htmlspecialchars($armas[0]['name']) ?></p>
            <p id="arma-damage" class="text-danger mb-1"><?= (int)$armas[0]['damage'] ?> dmg</p>
            <p id="arma-bullets" class="text-warning fw-bold">Balas: <?= (int)$armas[0]['bullets'] ?></p>
        </div>
    </div>

    <div id="damage-log"></div>

    <script>
        const id_user = <?= (int)$id_user ?>;
        const id_game = <?= (int)$id_game ?>;
        let armaActual = JSON.parse($("#arma").val());
        let inMatch = true;
        let isFiring = false; // ✅ Cooldown state
        let selectedTargetId = null; // ✅ Persistence state

        // Timer: inicializamos remaining con valor del servidor
        let remaining = <?= $matchDuration ?>;

        // Función para mostrar mensajes de daño
        function msg(m) {
            const e = $(`<div class='damage-msg text-white'>${m}</div>`);
            $("#damage-log").append(e);
            setTimeout(() => e.remove(), 2200);
        }

        // Función para actualizar la UI del arma (daño y balas)
        function updateWeaponUI() {
            $("#arma-img").attr("src", armaActual.img);
            $("#arma-nombre").text(armaActual.name);
            $("#arma-damage").text(armaActual.damage + " dmg");
            $("#battle-video source").attr("src", armaActual.video);
            $("#battle-video")[0].load();
            checkFireStatus(); // Revisa el estado del botón después de cambiar de arma
        }

        // Función para revisar el estado del botón (Cooldown y Balas)
        function checkFireStatus() {
            const fireBtn = $("#fire-btn");
            const bulletsDisplay = $("#arma-bullets");

            if (armaActual.bullets <= 0) {
                fireBtn.prop('disabled', true).removeClass('btn-fire').addClass('btn-secondary').text("❌ Recargar");
                bulletsDisplay.html(`Balas: <span class="text-danger fw-bold">0</span>`);
            } else if (isFiring) {
                // En cooldown, solo actualiza el texto si no está ya en el estado de "Cargando..."
                if (fireBtn.text() !== "Cargando...") {
                    fireBtn.prop('disabled', true).text("Cargando...");
                }
            } else {
                fireBtn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-fire').text("💥 Disparar");
                bulletsDisplay.html(`Balas: <span class="text-warning fw-bold">${armaActual.bullets}</span>`);
            }
        }

        // ✅ Cooldown - Establece el tiempo de espera
        function setCooldown() {
            isFiring = true;
            checkFireStatus();

            setTimeout(() => {
                isFiring = false;
                checkFireStatus();
            }, 1500); // 1.5 segundos
        }

        function finalizar(tipo) {
            if (!inMatch) return;
            inMatch = false;

            // limpiar timer persistente
            try {
                localStorage.removeItem("timer_match_" + id_game);
            } catch (e) {}

            const overlay = $("<div>").css({
                position: "fixed",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
                background: "rgba(0,0,0,0.85)",
                color: "#fff",
                "font-size": "40px",
                "text-align": "center",
                "z-index": 9999,
                "padding-top": "30vh"
            });

            const textos = {
                victoria: "🏆 ¡VICTORIA! +50 puntos",
                derrota: "💀 DERROTA −20 puntos",
                empate: "⚖️ EMPATE +0 puntos",
                abandono: "🚪 Has abandonado la partida −30 puntos"
            };
            overlay.html(textos[tipo] || "Partida terminada");
            $("body").append(overlay);

            // 🔹 Enviar resultado al servidor
            let params = `id_game=${id_game}&reason=${tipo}`;
            if (tipo === "victoria") params += `&winner_team=<?= (int)$user['team'] ?>`;

            fetch("end_match.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: params
            });

            setTimeout(() => window.location.href = "lobby.php", 3500);
        }

        function actualizar() {
            if (!inMatch) return;

            // 💡 Almacena la selección actual del oponente antes de actualizar
            const currentTarget = $("#target-select").val();

            $.post("real_time_status.php", {
                id_game,
                id_user
            }, data => {
                if (!data || data.error) return;
                $("#opponentZone").html("");
                let opts = '<option value="">Selecciona oponente</option>';

                data.oponentes.forEach(o => {
                    const img = o.avatar_img ? "../../" + o.avatar_img : "../../img/personajes/default_avatar.png";

                    // Usa clases de Bootstrap para las columnas
                    $("#opponentZone").append(`<div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <div class="opponent card border-info bg-dark shadow-sm" data-id="${o.id_user}">
                            <img src="${img}" class="img-fluid mx-auto mt-2">
                            <p class="text-white mt-2 mb-1">${o.username}</p>
                            <div class='health-bar'><div class='health-fill' style='width:${o.hp}%'></div></div>
                        </div>
                    </div>`);

                    opts += `<option value="${o.id_user}">${o.username}</option>`;
                });

                $("#target-select").html(opts);

                // ✅ PERSISTENCIA: Reestablece el oponente si estaba seleccionado y aún existe
                if (currentTarget && data.oponentes.some(o => o.id_user == currentTarget)) {
                    $("#target-select").val(currentTarget);
                } else {
                    // Si el oponente desapareció o no había selección, asegura que selectedTargetId sea null
                    selectedTargetId = null;
                }

                $("#vidaJugador").css("width", data.mi_hp + "%");
                if (data.mi_hp <= 0) finalizar("derrota");
                // ✅ Lógica de victoria por abandono/eliminación
                if (data.oponentes.length === 0) {
                    msg("🚨 Oponente abandonó la partida");
                    finalizar("victoria");
                }
            }, 'json');
        }
        setInterval(actualizar, 1000);

        // ✅ Manejo del cambio de arma y actualización de UI
        $("#arma").change(function() {
            // Actualiza el objeto armaActual y su conteo de balas
            const newArma = JSON.parse($(this).val());
            armaActual = {
                ...newArma,
                // Clona el valor de balas para que el cliente lo descuente
                bullets: newArma.bullets
            };
            updateWeaponUI();
        });

        // Inicializa la UI del arma al cargar
        updateWeaponUI();


        $("#fire-btn").click(() => {
            // ✅ Cooldown y Balas: Se detiene si está disparando o no tiene balas
            if (isFiring || !inMatch || armaActual.bullets <= 0) return;

            const target = $("#target-select").val(),
                zona = $("#zona-select").val();
            if (!target) return msg("Selecciona un oponente");

            // ✅ Descuento de bala y Cooldown (cliente)
            armaActual.bullets--;
            setCooldown();
            checkFireStatus();

            $.post("real_time_damage.php", {
                id_game,
                id_user,
                weapon_id: armaActual.id_weapons,
                target,
                zona
            }, res => {
                if (res.error) return msg(res.error);
                msg(res.msg);
                if (typeof res.target_hp !== 'undefined') {
                    $(`#opponentZone .opponent[data-id='${target}'] .health-fill`).css("width", Math.max(0, res.target_hp) + "%");
                }
                if (res.target_hp <= 0) msg("💀 Oponente eliminado");
                if (res.ended) finalizar(res.winner_team === <?= (int)$user['team'] ?> ? "victoria" : "derrota");
            }, 'json');
        });

        $("#exit-btn").click(() => {
            if (confirm("¿Seguro que deseas abandonar la partida? Perderás puntos.")) {

                // ✅ 1. Codificar los datos como URLSearchParams (Form-urlencoded)
                const exitData = new URLSearchParams();
                exitData.append('id_user', id_user);
                exitData.append('id_game', id_game);

                // ✅ 2. Enviar datos al servidor usando el formato correcto (POST)
                fetch("player_exit.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        }, // Usa form-urlencoded
                        body: exitData.toString()
                    })
                    .then(r => r.json()).then(r => {
                        if (r.error) {
                            msg(`❌ Error al abandonar: ${r.error}`);
                            return;
                        }

                        if (r.end_match) {
                            // El jugador que abandona no necesita este mensaje
                        }

                        // limpiar timer persistente al abandonar
                        try {
                            localStorage.removeItem("timer_match_" + id_game);
                        } catch (e) {}

                        // ✅ 3. El jugador que presiona siempre declara ABANDONO, perdiendo puntos.
                        finalizar("abandono");
                    });
            }
        });

        // ✅ Listener para persistir la selección del oponente
        $("#target-select").on('change', function() {
            selectedTargetId = $(this).val();
        });

        /* ---------------- Timer persistente ---------------- */
        (function() {
            const storageKey = "timer_match_" + id_game;
            try {
                const saved = localStorage.getItem(storageKey);
                if (saved !== null) {
                    const parsed = parseInt(saved, 10);
                    if (!isNaN(parsed)) remaining = parsed;
                }
            } catch (e) {
                /* si error de storage, ignorar */
            }

            function updateTimerText() {
                let m = String(Math.floor(remaining / 60)).padStart(2, '0');
                let s = String(remaining % 60).padStart(2, '0');
                $("#global-timer").text(`⏱️ ${m}:${s}`);
            }
            updateTimerText();

            const timerInterval = setInterval(() => {
                if (!inMatch) return;
                remaining--;
                // guardar progreso
                try {
                    localStorage.setItem(storageKey, remaining);
                } catch (e) {}
                updateTimerText();
                if (remaining <= 0) {
                    clearInterval(timerInterval);
                    try {
                        localStorage.removeItem(storageKey);
                    } catch (e) {}
                    msg("⏰ Tiempo agotado");
                    finalizar("empate");
                }
            }, 1000);

            // si el usuario cierra la pestaña mientras la partida sigue, mantenemos el valor
            window.addEventListener("beforeunload", () => {
                try {
                    if (inMatch) localStorage.setItem(storageKey, remaining);
                    else localStorage.removeItem(storageKey);
                } catch (e) {}
            });
        })();
        /* -------------------------------------------------- */
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const audio = document.getElementById("battle_audio");

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