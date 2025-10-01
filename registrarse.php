<?php
// registrar.php (procesa el registro)
require_once("database/conexion.php");
$db = new Database;
$con = $db->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura solo lo que viene del formulario
    $username = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['clave'], PASSWORD_BCRYPT);

    // Valores fijos
    $rol = 2;      // Siempre "Jugador"
    $avatar = 1;   // Avatar por defecto
    $status = 1;   // Activo
    $level = 1;    // Nivel inicial
    $points = 0;

    $sql = "INSERT INTO users (username, email, password, id_rol, id_avatar, points, level_id, last_login, id_status) 
            VALUES (:usuario, :email, :clave, :rol, :avatar, :points, :level, NOW(), :status)";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':usuario', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':clave', $password);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':avatar', $avatar);
    $stmt->bindParam(':points', $points);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        header("Location: index.php?success=1");
        exit;
    } else {
        echo "Error al registrar usuario.";
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro | Halo Style</title>
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
</head>
<body class="vh-100 bg-dark">

  <div class="container-fluid h-100">
    <div class="row h-100">
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center text-light p-5 position-relative">
        <video autoplay muted loop class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; z-index: -1;">
          <source src="video/intro.mp4" type="video/mp4">
        </video>
      </div>

      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-light">
        <div class="w-75">
          <h2 class="fw-bold text-dark mb-4">Crear cuenta</h2>
          <p class="text-muted">¿Ya tienes cuenta? <a href="index.php" class="text-decoration-none">Inicia sesión aquí</a></p>

          <form method="POST" action="registrarse.php" autocomplete="off">
            <div class="mb-3">
              <label for="usuario" class="form-label">Usuario</label>
              <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Correo electrónico</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
              <label for="clave" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="clave" name="clave" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
