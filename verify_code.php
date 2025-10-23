<?php
session_start();
require_once("database/conexion.php");

if (!isset($_SESSION['code']) || !isset($_SESSION['id_user'])) {
    header("Location: recuperar.php");
    exit();
}

if (isset($_POST['verificar'])) {
    $codigoIngresado = trim($_POST['codigo']);

    if ($codigoIngresado == $_SESSION['code']) {
        $_SESSION['codigo_validado'] = true;
        header("Location: cambiar_contrasena.php");
        exit();
    } else {
        $error = "El código ingresado es incorrecto. Inténtalo nuevamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Verificar Código</title>
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow p-4 text-center" style="max-width: 400px; width: 100%;">
    <h4 class="mb-3 text-primary">Verificación de Código</h4>
    <p class="text-muted">Hemos enviado un código a tu correo <strong><?= htmlspecialchars($_SESSION['email']) ?></strong></p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="codigo" class="form-label">Código de verificación</label>
        <input type="text" name="codigo" id="codigo" class="form-control text-center" maxlength="4" pattern="[0-9]{4}" placeholder="Ej: 1234" required>
      </div>

      <div class="d-grid">
        <button type="submit" name="verificar" class="btn btn-primary">Verificar</button>
      </div>

      <div class="mt-3">
        <a href="recuperar.php" class="text-decoration-none">Volver al inicio</a>
      </div>
    </form>
  </div>

  <script src="controller/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
