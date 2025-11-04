<?php
require_once("../../database/conexion.php");
session_start();

// 🔒 Verificar sesión
if (!isset($_SESSION['usuario'])) {
  header("Location: ../../index.php");
  exit();
}

$db = new Database();
$con = $db->conectar();
$username = $_SESSION['usuario'];

// 🧠 Obtener nivel actual del usuario
$stmt = $con->prepare("
    SELECT u.level_id, l.name AS level_name
    FROM users u
    LEFT JOIN levels l ON u.level_id = l.level_id
    WHERE u.username = ?
");
$stmt->execute([$username]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$userLevel = intval($userData['level_id'] ?? 0);
$userLevelName = $userData['level_name'] ?? 'Recluta';

// 📦 Obtener todas las armas con su tipo
$query = $con->prepare("
    SELECT w.*, wt.type_name, l.name AS required_level_name
    FROM weapons w
    LEFT JOIN levels l ON w.level_arm = l.level_id
    LEFT JOIN weapon_types wt ON w.id_type = wt.id_type
    ORDER BY w.level_arm ASC
");
$query->execute();
$weapons = $query->fetchAll(PDO::FETCH_ASSOC);

// 🧩 Obtener tipos de arma
$typeQuery = $con->prepare("SELECT id_type, type_name FROM weapon_types ORDER BY id_type ASC");
$typeQuery->execute();
$weaponTypes = $typeQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Arsenal del Jugador | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/weapons.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">


  <style>
    .back-btn {
      position: absolute;
      top: 10px;
      left: 18px;
      background: linear-gradient(90deg, #00bcd4, #005f73);
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      font-weight: bold;
      text-decoration: none;
    }
  </style>


</head>

<body class="background">

  <!-- 🔙 Botón volver -->
  <div class="volver-container top-left">
    <a href="lobby.php" class="back-btn">⬅ Volver</a>
  </div>

  <h4 class="titulo">⚔️ Arsenal del Jugador</h4>


  <!-- 🎛️ Filtro por tipo de arma (sin “Todos”) -->
  <div class="filtro-container text-center mt-4">
    <label for="tipoFiltro" class="filtro-label text-light me-2">Filtrar por tipo:</label>
    <select id="tipoFiltro" class="form-select d-inline-block w-auto">
      <?php foreach ($weaponTypes as $type): ?>
        <option value="<?= htmlspecialchars($type['id_type']) ?>">
          <?= htmlspecialchars($type['type_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- 🎯 Carrusel de armas -->
  <div class="character-slider">
    <button class="slider-btn prev-btn" id="prev-btn">&lt;</button>

    <div class="slider-viewport">
      <div class="avatars-container" id="weapons-container">
        <?php if (empty($weapons)): ?>
          <div class="text-center text-light">
            <h3>⚠️ No hay armas registradas en el juego.</h3>
          </div>
        <?php else: ?>
          <?php foreach ($weapons as $weapon):
            $isUnlocked = ($userLevel >= $weapon['level_arm']);
          ?>
            <div class="character-card <?= $isUnlocked ? 'unlocked' : 'locked' ?>"
              data-tipo="<?= htmlspecialchars($weapon['id_type']) ?>">
              <div class="weapon-img-container">
                <img src="../../img/armas/<?= htmlspecialchars($weapon['image_url']) ?>"
                  alt="<?= htmlspecialchars($weapon['name']) ?>"
                  class="weapon-img <?= !$isUnlocked ? 'img-locked' : '' ?>">
              </div>

              <h3><?= htmlspecialchars($weapon['name']) ?></h3>
              <p><strong>Tipo:</strong> <?= htmlspecialchars($weapon['type_name'] ?? 'Desconocido') ?></p>
              <p><strong>Daño:</strong> <?= htmlspecialchars($weapon['damage']) ?></p>
              <p><strong>Balas:</strong> <?= htmlspecialchars($weapon['bullets']) ?></p>
              <p class="nivel-requerido">
                Nivel requerido: <?= htmlspecialchars($weapon['required_level_name'] ?? 'Desconocido') ?>
              </p>

              <?php if ($isUnlocked): ?>
                <button class="select-btn disponible" disabled>✅ Disponible</button>
              <?php else: ?>
                <button class="select-btn bloqueado" disabled>🔒 Bloqueada</button>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <button class="slider-btn next-btn" id="next-btn">&gt;</button>
  </div>

  <script>
    const sliderContainer = document.getElementById('weapons-container');
    const cards = document.querySelectorAll('.character-card');
    let currentIndex = 0;

    function getVisibleCards() {
      return 3;
    }

    function getStepSize() {
      const cardRect = cards[0]?.getBoundingClientRect();
      return cardRect ? cardRect.width + 25 : 0;
    }

    function updateSlider() {
      const visibleCards = getVisibleCards();
      const stepSize = getStepSize();
      const totalCards = cards.length;
      const maxIndex = Math.max(0, totalCards - visibleCards);

      if (currentIndex > maxIndex) currentIndex = maxIndex;

      const offset = -currentIndex * stepSize;
      sliderContainer.style.transform = `translateX(${offset}px)`;

      document.getElementById('prev-btn').disabled = currentIndex === 0;
      document.getElementById('next-btn').disabled = currentIndex >= maxIndex;
    }

    // 🎯 Filtrar armas por tipo seleccionado
    const filtro = document.getElementById('tipoFiltro');
    filtro.addEventListener('change', () => {
      const tipoSeleccionado = filtro.value;
      document.querySelectorAll('.character-card').forEach(card => {
        card.style.display = (card.dataset.tipo === tipoSeleccionado) ? 'block' : 'none';
      });
      currentIndex = 0;
      updateSlider();
    });

    document.getElementById('next-btn').addEventListener('click', () => {
      const totalCards = document.querySelectorAll('.character-card').length;
      if (currentIndex < totalCards - getVisibleCards()) {
        currentIndex++;
        updateSlider();
      }
    });

    document.getElementById('prev-btn').addEventListener('click', () => {
      if (currentIndex > 0) {
        currentIndex--;
        updateSlider();
      }
    });

    // Inicializar mostrando el primer tipo automáticamente
    filtro.selectedIndex = 0;
    filtro.dispatchEvent(new Event('change'));

    updateSlider();
    window.addEventListener('resize', updateSlider);
  </script>

</body>

</html>