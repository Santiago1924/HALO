<?php
require_once('database/conexion.php');
session_start();

// 🚨 Verifica si se validó el código antes de entrar aquí
if (!isset($_SESSION['codigo_validado']) || $_SESSION['codigo_validado'] !== true) {
    header("Location: recuperar.php");
    exit();
}

$db = new Database();
$con = $db->conectar();

if (isset($_POST['enviar'])) {
    $contrasena = trim($_POST['new_contrasena']);
    $contrasena_Verify = trim($_POST['confirmar_con']);

    // 🛡️ Validaciones
    if (strlen($contrasena) < 6) {
        echo "<script>alert('La contraseña debe tener al menos 6 caracteres.');</script>";
    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $contrasena)) {
        echo "<script>alert('La contraseña solo puede contener letras y números.');</script>";
    } elseif ($contrasena !== $contrasena_Verify) {
        echo "<script>alert('Las contraseñas no coinciden.');</script>";
    } else {
        // 🔐 Encriptar y actualizar
        $encripted = password_hash($contrasena, PASSWORD_BCRYPT, ["cost" => 12]);
        $sql1 = $con->prepare("UPDATE users SET password = :password WHERE id_user = :id_user");
        $sql1->bindParam(':password', $encripted, PDO::PARAM_STR);
        $sql1->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
        $sql1->execute();

        // 🧹 Limpiar sesión y redirigir
        session_destroy();
        header("Location: index.php?password=success");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cambiar Contraseña</title>
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow p-4 w-100" style="max-width: 400px;">
    <h4 class="text-center mb-4 text-primary">Cambiar Contraseña</h4>
    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="new_contrasena" class="form-label">Nueva contraseña</label>
        <input type="password" name="new_contrasena" id="new_contrasena" class="form-control" placeholder="Escribe tu nueva contraseña" required>
      </div>

      <div class="mb-3">
        <label for="confirmar_con" class="form-label">Confirmar contraseña</label>
        <input type="password" name="confirmar_con" id="confirmar_con" class="form-control" placeholder="Confirma la contraseña" required>
      </div>

      <div class="d-grid">
        <button type="submit" name="enviar" class="btn btn-success">Cambiar contraseña</button>
      </div>
    </form>
  </div>

  <script src="controller/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
