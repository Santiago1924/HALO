
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

      <!-- Lado izquierdo (video o imagen) -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center text-light p-5 position-relative">
        <video autoplay muted loop class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; z-index: -1;">
          <source src="video/intro.mp4" type="video/mp4">
        </video>
      </div>

      <!-- Lado derecho (formulario login) -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-light">
        <div class="w-75">
          <h2 class="fw-bold text-dark mb-4">Iniciar sesión</h2>
          <p class="text-muted">¿No tienes cuenta? <a href="registrarse.php" class="text-decoration-none">Regístrate aquí</a></p>

          <?php if (!empty($mensaje)): ?>
            <div class="alert alert-danger text-center"><?= $mensaje ?></div>
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

            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
          </form>
        </div>
      </div>

    </div>
  </div>

</body>
</html>
<script>
    // Activar el sonido al hacer clic
    document.getElementById('play-sound').addEventListener('click', () => {
      const audio = document.getElementById('bg-audio');
      audio.play().then(() => {
        document.getElementById('play-sound').style.display = 'none';
      }).catch(err => console.log("Autoplay bloqueado:", err));
    });
  </script>
