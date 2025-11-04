<?php
session_start();
require_once("../../database/conexion.php");

$db = new Database;
$con = $db->conectar();

// 🔐 Verificación de sesión
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ✅ Consulta del usuario y su rol
$sql = $con->prepare("
    SELECT u.*, r.name AS rol_nombre 
    FROM users u 
    INNER JOIN roles r ON u.id_rol = r.id_rol 
    WHERE u.id_user = :id_user
");
$sql->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$sql->execute();
$fila = $sql->fetch(PDO::FETCH_ASSOC);

// 🚪 Cerrar sesión
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
    <style>
        body {
            background: radial-gradient(circle at top, #0d1117, #000);
            color: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
        }
        .navbar {
            background: linear-gradient(90deg, #00eaff, #007bff);
        }
        .card {
            background: rgba(20, 20, 20, 0.9);
            border: 1px solid #00eaff;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 234, 255, 0.3);
        }
        .btn-outline-info {
            border-color: #00eaff;
            color: #00eaff;
        }
        .btn-outline-info:hover {
            background: #00eaff;
            color: #000;
        }
        footer {
            margin-top: 60px;
            color: #6c757d;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-dark" href="#">
            <i class="bi bi-shield-lock-fill text-dark"></i> HALO ADMIN
        </a>
        <div class="d-flex align-items-center">
            <form method="POST" class="mb-0">
                <button type="submit" name="btncerrar" class="btn btn-dark border border-light">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</nav>

<!-- CONTENIDO -->
<div class="container py-5">

    <?php if ($fila): ?>
        <div class="card shadow border-0 p-4 text-center">
            <div class="card-body">
                <h2 class="mb-3 text-info">
                    <i class="bi bi-person-circle"></i> Bienvenido, 
                    <span class="text-light"><?= htmlspecialchars($fila['username']) ?></span>
                </h2>
                <p><strong>Correo:</strong> <?= htmlspecialchars($fila['email']) ?></p>
                <p><strong>Rol:</strong> <?= htmlspecialchars($fila['rol_nombre']) ?></p>
                <hr class="border-light">
                
                <h4 class="text-info mb-3"><i class="bi bi-gear-wide-connected"></i> Panel de Gestión</h4>

                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="ver_usuarios.php" class="btn btn-outline-info">
                        <i class="bi bi-people"></i> Usuarios
                    </a>
                    <a href="weapons.php" class="btn btn-outline-info">
                        <i class="bi bi-controller"></i> Armas
                    </a>
                    <a href="worlds.php" class="btn btn-outline-info">
                        <i class="bi bi-globe"></i> Mapas
                    </a>
                    <a href="avatars.php" class="btn btn-outline-info">
                        <i class="bi bi-person-bounding-box"></i> Avatares
                    </a>
                    <a href="salas.php" class="btn btn-outline-info">
                        <i class="bi bi-door-open"></i> Crear Sala
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
<footer class="text-center mt-5">
    <small>© <?= date('Y') ?> HALO System — Panel de Administración</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
