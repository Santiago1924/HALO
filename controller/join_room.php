<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: ../index.php");
  exit();
}

// 📡 Conexión a la base de datos
include(__DIR__ . '/db.php');

if (isset($_POST['id_games']) && isset($_POST['id_user'])) {
    $id_games = intval($_POST['id_games']);
    $id_user = intval($_POST['id_user']);

    // ⚙️ Inserta el jugador en la sala
    $stmt = $conn->prepare("
      INSERT INTO room_players (id_games, join_date, is_alive, current_hp)
      VALUES (?, NOW(), 1, 100)
    ");
    $stmt->bind_param("i", $id_games);

    if ($stmt->execute()) {
        header("Location: ../views/rooms.php?joined=success");
        exit();
    } else {
        header("Location: ../views/rooms.php?error=join_failed");
        exit();
    }
} else {
    header("Location: ../views/rooms.php?error=invalid_data");
    exit();
}
