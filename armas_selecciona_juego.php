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

// 🧩 Guardar arma seleccionada por el usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_weapons'])) {
    $id_weapons = intval($_POST['id_weapons']);

    // Verificar que el arma exista antes de asignarla
    $check = $con->prepare("SELECT COUNT(*) FROM weapons WHERE id_weapons = ?");
    $check->execute([$id_weapons]);
    if ($check->fetchColumn() > 0) {
        $update = $con->prepare("UPDATE users SET id_weapons = ? WHERE username = ?");
        $update->execute([$id_weapons, $username]);
        $_SESSION['arma_seleccionada'] = $id_weapons;
        echo "<script>alert('✅ Arma seleccionada con éxito');</script>";
    } else {
        echo "<script>alert('⚠️ Arma no válida seleccionada.');</script>";
    }
}

// 📌 Obtener tipo seleccionado desde GET (filtro)
$selected_type = isset($_GET['id_type']) ? intval($_GET['id_type']) : 0;

// 🧨 Obtener todos los tipos de armas
$types_stmt = $con->prepare("SELECT * FROM weapon_types ORDER BY id_type ASC");
$types_stmt->execute();
$types = $types_stmt->fetchAll(PDO::FETCH_ASSOC);

// 🔍 Obtener armas filtradas por tipo si se seleccionó uno
if ($selected_type > 0) {
    $query = $con->prepare("SELECT * FROM weapons WHERE id_type = ?");
    $query->execute([$selected_type]);
} else {
    $query = $con->prepare("SELECT * FROM weapons");
    $query->execute();
}
$weapons = $query->fetchAll(PDO::FETCH_ASSOC);

// 🔎 Obtener arma actual del usuario (si existe)
$stmt = $con->prepare("SELECT id_weapons FROM users WHERE username = ?");
$stmt->execute([$username]);
$currentWeapon = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Seleccionar Arma | Halo Style</title>
  <link rel="stylesheet" href="../../controller/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../css/weapons.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="background">

  <!-- 🔙 Botón volver -->
  <div class="volver-container top-left">
    <a href="lobby.php" class="volver-btn">⬅ Volver al Lobby</a>
  </div>

  <h1 class="titulo">⚔️ Arsenal - Elige tu Arma</h1>

  <!-- 🧨 Filtro por tipo de arma -->
  <div class="container my-4 text-center">
    <form method="GET" class="d-inline-block">
      <label for="id_type" class="form-label fs-4 text-info">Filtrar por tipo:</label>
      <select name="id_type" id="id_type" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
        <option value="0">-- Todas las armas --</option>
        <?php foreach ($types as $type): ?>
          <option value="<?= $type['id_type'] ?>" <?= ($selected_type == $type['id_type']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($type['type_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <!-- 🎯 Carrusel de armas -->
  <div class="character-slider">
    <!-- ⬅️ Botón anterior -->
    <button class="slider-btn prev-btn" id="prev-btn">&lt;</button>

    <div class="slider-viewport">
      <div class="avatars-container" id="weapons-container">
        <?php if (empty($weapons)): ?>
          <div class="text-center text-light">
            <h3>⚠️ No hay armas disponibles para esta categoría.</h3>
          </div>
        <?php else: ?>
          <?php foreach ($weapons as $weapon): ?>
            <div class="character-card <?= ($weapon['id_weapons'] == $currentWeapon) ? 'selected' : '' ?>">

              <div class="weapon-img-container">
                <img src="../../img/armas/<?= htmlspecialchars($weapon['image_url']) ?>" 
                     alt="<?= htmlspecialchars($weapon['name']) ?>" 
                     class="weapon-img">
              </div>

              <h3><?= htmlspecialchars($weapon['name']) ?></h3>
              <p><strong>Tipo:</strong> <?= htmlspecialchars($weapon['subtype']) ?></p>
              <p><strong>Daño:</strong> <?= htmlspecialchars($weapon['damage']) ?></p>
              <p><strong>Balas:</strong> <?= htmlspecialchars($weapon['bullets']) ?></p>

              <form method="POST">
                <input type="hidden" name="id_weapons" value="<?= $weapon['id_weapons'] ?>">
                <button type="submit" class="select-btn">
                  <?= ($weapon['id_weapons'] == $currentWeapon) ? '✅ Seleccionada' : 'Seleccionar' ?>
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ➡️ Botón siguiente -->
    <button class="slider-btn next-btn" id="next-btn">&gt;</button>
  </div>

<script>
  const sliderContainer = document.getElementById('weapons-container');
  const cards = document.querySelectorAll('.character-card');
  const totalCards = cards.length;
  let currentIndex = 0;

  function getVisibleCards() {
    if (window.innerWidth <= 768) return 1;
    if (window.innerWidth <= 1100) return 2;
    return 3;
  }

  function getStepSize() {
    const cardRect = cards[0]?.getBoundingClientRect();
    return cardRect ? cardRect.width + 25 : 0;
  }

  function updateSlider() {
    const visibleCards = getVisibleCards();
    const stepSize = getStepSize();
    if (currentIndex > totalCards - visibleCards) {
      currentIndex = Math.max(0, totalCards - visibleCards);
    }
    const offset = -currentIndex * stepSize;
    sliderContainer.style.transform = `translateX(${offset}px)`;

    document.getElementById('prev-btn').disabled = (currentIndex === 0);
    document.getElementById('next-btn').disabled = (currentIndex >= totalCards - visibleCards);
  }

  document.getElementById('next-btn').addEventListener('click', () => {
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

  updateSlider();
  window.addEventListener('resize', updateSlider);
</script>

</body>
</html>
