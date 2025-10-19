<?php
session_start();
require_once("../../database/conexion.php");

$db = new Database;
$con = $db->conectar();

// Verifica sesión activa
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

// Crear nuevo usuario con rol de administrador
if (isset($_POST['crear_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $estado = $_POST['estado'] ?? 1; // Por defecto activo
    $rol_admin = 1; // ID del rol administrador (según tu base de datos)

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Verificar si el usuario ya existe
        $check = $con->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $check->bindParam(':username', $username);
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->rowCount() > 0) {
            $msg = "<div class='alert alert-warning text-center'>⚠️ El usuario o correo ya está registrado.</div>";
        } else {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $con->prepare("
                INSERT INTO users (username, email, password, id_avatar, points, LEVEL_ID, id_status, id_rol)
                VALUES (:username, :email, :password, 1, 0, 1, :estado, :rol)
            ");
            $insert->bindParam(':username', $username);
            $insert->bindParam(':email', $email);
            $insert->bindParam(':password', $pass_hash);
            $insert->bindParam(':estado', $estado, PDO::PARAM_INT);
            $insert->bindParam(':rol', $rol_admin, PDO::PARAM_INT);
            $insert->execute();

            $msg = "<div class='alert alert-success text-center'>✅ Administrador creado correctamente.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger text-center'>⚠️ Todos los campos son obligatorios.</div>";
    }
}

// Consulta para obtener todos los usuarios con su rol y estado
$sql = $con->prepare("
    SELECT 
        u.id_user,
        u.username,
        u.email,
        u.points,
        r.name AS rol,
        s.name AS estado
    FROM users u
    INNER JOIN roles r ON u.id_rol = r.id_rol
    INNER JOIN status s ON u.id_status = s.id_status
    ORDER BY u.id_user ASC
");
$sql->execute();
$usuarios = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuarios - HALO ADMIN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container mt-5">

    <!-- Botón de regreso -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-clipboard-minus"></i> Lista de Usuarios</h2>
        <a href="dashboard.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left-circle"></i> Volver al Panel
        </a>
    </div>

    <!-- Mensajes del sistema -->
    <?php if (isset($msg)) echo $msg; ?>

    <!-- FORMULARIO DE CREACIÓN DE ADMINISTRADORES -->
    <div class="card bg-secondary mb-5 border-0 shadow-lg">
        <div class="card-body">
            <h4 class="text-center mb-3"><i class="bi bi-clipboard-check"></i> Crear nuevo Administrador</h4>
            <form method="POST" autocomplete="off">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre de usuario</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button type="submit" name="crear_admin" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-person-plus-fill"></i> Crear Administrador
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA DE USUARIOS -->
    <div class="card bg-secondary shadow-lg border-0">
        <div class="card-body">
            <table class="table table-dark table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Puntos</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuarios)): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['id_user']) ?></td>
                                <td><?= htmlspecialchars($usuario['username']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['points']) ?></td>
                                <td><?= htmlspecialchars($usuario['rol']) ?></td>
                                <td><?= htmlspecialchars($usuario['estado']) ?></td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm"
                                            onclick="window.open(
                                                'update.php?id=<?= $usuario['id_user'] ?>',
                                                'ActualizarUsuario',
                                                'width=700,height=700,toolbar=no,scrollbars=yes,resizable=yes'
                                            ); return false;">
                                        <i class="bi bi-pencil-square"></i> Actualizar/Borrar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="text-center mt-5 text-secondary">
    <small>© <?= date('Y') ?> HALO System - Administración</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
