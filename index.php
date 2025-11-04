



<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

$mensaje = '';
if (isset($_SESSION['login_error'])) {
  $mensaje = $_SESSION['login_error'];
  unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login | Halo Style</title>
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
</head>
<body class="vh-100 bg-dark">

  <div class="container-fluid h-100">
    <div class="row h-100">

      <!-- 🎥 Lado del video -->
      <div class="col-md-6 d-none d-md-flex flex-column justify-content-center align-items-center text-light p-5 position-relative">
        <video autoplay muted loop class="position-absolute top-0 start-0 w-100 h-100 bg-audio" style="object-fit: cover; z-index: -1;">
          <source src="video/intro.mp4" type="video/mp4">
        </video>
      </div>

      <!-- 🧑‍💻 Lado del login -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-light">
        <div class="w-75" style="max-width: 400px;">
          <h2 class="fw-bold text-dark mb-3 text-center">Iniciar sesión</h2>
          <p class="text-muted text-center">¿No tienes cuenta? <a href="registrarse.php" class="text-decoration-none">Regístrate aquí</a></p>

          <?php if (!empty($mensaje)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($mensaje) ?></div>
          <?php endif; ?>

          <form method="POST" action="controller/inicio.php" autocomplete="off">
            <div class="mb-3">
              <label for="usuario" class="form-label">Usuario</label>
              <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>

            <div class="mb-3">
              <label for="contrasena" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="contrasena" name="contrasena" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-2">Ingresar</button>

            <a href="recuperar.php" class="d-block text-center mt-2 text-decoration-none">¿Olvidaste tu contraseña?</a>
          </form>
        </div>
      </div>

    </div>
  </div>

  <script src="controller/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
window.history.pushState(null, "", window.location.href);
window.onpopstate = function () {
    window.location.href = window.location.href;
};
</script>

</body>
</html>
