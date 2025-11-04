<?php
require_once("../../database/conexion.php");
session_start();

if (!isset($_SESSION['usuario'])) {
  header("Location: ../../index.php");
  exit();
}

$db = new Database;
$con = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_avatar'])) {
  $id_avatar = intval($_POST['id_avatar']);
  $username = $_SESSION['usuario'];

  $update = $con->prepare("UPDATE users SET id_avatar = ? WHERE username = ?");
  $update->execute([$id_avatar, $username]);

  echo "<script>alert('Avatar cambiado con éxito');</script>";
}

$query = $con->prepare("SELECT * FROM avatars");
$query->execute();
$avatars = $query->fetchAll(PDO::FETCH_ASSOC);

$username = $_SESSION['usuario'];
$stmt = $con->prepare("SELECT id_avatar FROM users WHERE username = ?");
$stmt->execute([$username]);
$currentAvatar = $stmt->fetchColumn();

if (!$currentAvatar) {
  $currentAvatar = 117;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Seleccionar Personaje</title>
  <link rel="stylesheet" href="../../css/personajes.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="background">

  <div class="volver-container top-left">
    <a href="lobby.php" class="volver-btn">Volver</a>
  </div>

  <h2 class="titulo">Selecciona tu personaje</h2>

  <div class="character-slider">
    <button class="slider-btn prev-btn" id="prev-btn">&lt;</button>

    <div class="slider-viewport">
      <div class="avatars-container" id="avatars-container">
        <?php foreach ($avatars as $avatar): ?>
          <div class="character-card <?= ($avatar['id_avatar'] == $currentAvatar) ? 'selected' : '' ?>">

            <div class="weapon-img-container">
              <img class="weapon-img" src="../../<?= htmlspecialchars($avatar['image_url']) ?>" alt="<?= htmlspecialchars($avatar['name']) ?>">
            </div>

            <h3><?= htmlspecialchars($avatar['name']) ?></h3>
            <p><?= htmlspecialchars($avatar['description']) ?></p>

            <form method="POST">
              <input type="hidden" name="id_avatar" value="<?= $avatar['id_avatar'] ?>">
              <button type="submit" class="select-btn">Seleccionar</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button class="slider-btn next-btn" id="next-btn">&gt;</button>
  </div>

  <script>
    const sliderContainer = document.getElementById('avatars-container');
    const cards = document.querySelectorAll('.character-card');
    const totalCards = cards.length;
    let currentIndex = 0;

    function getVisibleCards() {
      if (window.innerWidth <= 768) return 1;
      if (window.innerWidth <= 1100) return 2;
      return 3;
    }

    function getStepSize() {
      const cardRect = cards[0].getBoundingClientRect();
      return cardRect.width + 25;
    }

    function updateSlider() {
      const VISIBLE_CARDS = getVisibleCards();
      const STEP_SIZE = getStepSize();
      if (currentIndex > totalCards - VISIBLE_CARDS) {
        currentIndex = Math.max(0, totalCards - VISIBLE_CARDS);
      }
      const offset = -currentIndex * STEP_SIZE;
      sliderContainer.style.transform = `translateX(${offset}px)`;
      document.getElementById('prev-btn').disabled = (currentIndex === 0);
      document.getElementById('next-btn').disabled = (currentIndex >= totalCards - VISIBLE_CARDS);
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