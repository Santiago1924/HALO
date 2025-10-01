<?php
// registrarse.php (procesa el registro)
require_once("database/conexion.php");

$db = new Database;
$con = $db->conectar();

$registro_exitoso = false; // 👈 Variable para controlar si se registró

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['clave'], PASSWORD_BCRYPT);

    $rol = 2;       // Rol por defecto
    $avatar = 3;    // Avatar por defecto 
    $status = 1;    // Activo
    $level = 1;     // Nivel inicial
    $points = 0;    // Puntos iniciales

    try {
        $sql = "INSERT INTO users 
                (username, email, password, id_rol, id_avatar, points, level_id, last_login, id_status) 
                VALUES 
                (:username, :email, :password, :rol, :avatar, :points, :level, NOW(), :status)";
        
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':points', $points);
        $stmt->bindParam(':level', $level);
        $stmt->bindParam(':status', $status);

        if ($stmt->execute()) {
            $registro_exitoso = true; //  Marca que se registró correctamente
        } else {
            $error = "❌ Error al registrar usuario.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro | Halo Style</title>
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
  <script>
    // Si el registro fue exitoso, redirigir al login luego de 3 segundos
    function redirigirLogin() {
      setTimeout(function(){
        window.location.href = "index.php";
      }, 4000); // 4000 = 4 segundos
    }
  </script>
</head>
<body class="vh-100 bg-dark" <?php if($registro_exitoso) echo 'onload="redirigirLogin()"'; ?>>

  <div class="container-fluid h-100">
    <div class="row h-100">

      <!-- Video lado izquierdo -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center text-light p-5 position-relative">
        <video autoplay muted loop class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; z-index: -1;">
          <source src="video/intro.mp4" type="video/mp4">
        </video>
      </div>

      <!-- Formulario de registro -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-light">
        <div class="w-75">
          <h2 class="fw-bold text-dark mb-4">Crear cuenta</h2>
          <p class="text-muted">¿Ya tienes cuenta? <a href="index.php" class="text-decoration-none">Inicia sesión aquí</a></p>

          <?php if (isset($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
          <?php endif; ?>

          <?php if ($registro_exitoso): ?>
            <div class="alert alert-success text-center">
              ✅ ¡Registro exitoso! Serás redirigido al inicio de sesión en 3 segundos...
            </div>
          <?php else: ?>
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
          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>

</body>
</html>
