<?php
session_start();
require_once("../../database/conexion.php");

$db = new Database;
$con = $db->conectar();

// Si no hay sesión, redirige al inicio
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ✅ Consulta del usuario con su rol (tabla correcta: roles)
$sql = $con->prepare("
    SELECT u.*, r.name AS rol_nombre 
    FROM users u 
    INNER JOIN roles r ON u.id_rol = r.id_rol 
    WHERE u.id_user = :id_user
");
$sql->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$sql->execute();
$fila = $sql->fetch(PDO::FETCH_ASSOC);

// ✅ Cerrar sesión si se presiona el botón
if (isset($_POST['btncerrar'])) {
    session_destroy();
    header("Location: ../../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-info shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">HALO ADMIN</a>
        <div class="d-flex align-items-center">
            <form method="POST" class="mb-0">
                <button type="submit" name="btncerrar" class="btn btn-outline-danger me-2">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- CONTENIDO -->
<div class="container py-5">

    <?php if ($fila): ?>
        <div class="card bg-secondary shadow border-0">
            <div class="card-body text-center">
                <h2 class="mb-3 text-info"><i class="bi bi-person-circle"></i>Bienvenido, 
                    <span class="text-info"><?= htmlspecialchars($fila['username']) ?></span>
                </h2>
                <p><strong>Correo:</strong> <?= htmlspecialchars($fila['email']) ?></p>
                <p><strong>Rol:</strong> <?= htmlspecialchars($fila['rol_nombre']) ?></p>
                <hr class="border-light">
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="ver_usuarios.php" class="btn btn-outline-info">
                        <i class="bi bi-people"></i> Gestionar Usuarios
                    </a>
                    <a href="weapons.php" class="btn btn-outline-info">
                        <i class="bi bi-controller"></i> Gestionar Armas
                    </a>
                    <a href="worlds.php" class="btn btn-outline-info">
                        <i class="bi bi-globe"></i> Gestionar Mapas
                    </a>
                    <a href="avatars.php" class="btn btn-outline-info">
                        <i class="bi bi-globe"></i> Gestionar Avatars
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center mt-5">
            ⚠️ No se encontró la información del usuario.
        </div>
    <?php endif; ?>

</div>

<!-- FOOTER -->
<footer class="text-center text-secondary mt-5">
    <small>© <?= date('Y') ?> HALO System — Panel de Administración</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
