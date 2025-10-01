<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login | Halo Style</title>
  <!-- Bootstrap local -->
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
</head>
<body class="vh-100 bg-dark">

  <div class="container-fluid h-100">
    <div class="row h-100">

      <!-- Izquierda con video -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center text-light p-5 position-relative">
        <video autoplay muted loop class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; z-index: -1;">
          <source src="video/intro.mp4" type="video/mp4">
          Tu navegador no soporta videos HTML5.
        </video>
      </div>

      <!-- Derecha con login -->
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-light">
        <div class="w-75">
          <h2 class="fw-bold text-dark mb-4">Inicio de Sesión</h2>
          <p class="text-muted">¿No tienes cuenta? <a href="#" class="text-decoration-none">Regístrate aquí</a></p>

          <form method="POST" action="controller/inicio.php" autocomplete="off">
            <div class="mb-3">
              <label for="usuario" class="form-label">Usuario</label>
              <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa tu usuario">
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="password" name="clave" placeholder="Ingresa tu contraseña">
            </div>

            <button type="submit" class="btn btn-success w-100 mb-3">Entrar</button>

            <div class="mt-3">
              <a href="registrarse.php" class="text-decoration-none">Registrarse</a>
            </div>

            <div class="mt-3">
              <a href="#" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>

  <!-- Audio de fondo -->
  <audio id="bg-music" autoplay loop>
    <source src="audio/intro.mp3" type="audio/mpeg">
    Tu navegador no soporta audio HTML5.
  </audio>

  <!-- Script para habilitar autoplay tras interacción -->
  <script>
    document.addEventListener("click", () => {
      const audio = document.getElementById("bg-music");
      audio.play().catch(err => console.log("Autoplay bloqueado:", err));
    });
  </script>

  <!-- Bootstrap JS local -->
  <script src="controller/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
