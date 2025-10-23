<?php
session_start();
require_once("../../database/conexion.php");

$db = new Database();
$con = $db->conectar();

// ✅ Verificar sesión activa
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

// ✅ Consultar datos del usuario logueado
$sql = $con->prepare("
    SELECT u.username, r.name AS rol
    FROM users u
    INNER JOIN roles r ON u.id_rol = r.id_rol
    WHERE u.id_user = :id
");
$sql->bindParam(":id", $_SESSION['id_user']);
$sql->execute();
$fila = $sql->fetch(PDO::FETCH_ASSOC);

// ✅ Registrar nuevo avatar
if (isset($_POST['registrar_avatar'])) {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $imagen = $_FILES['imagen'] ?? null;

    if (empty($nombre) || empty($descripcion) || empty($imagen['name'])) {
        $mensaje = "<div class='alert alert-warning text-center'>⚠️ Todos los campos son obligatorios, incluida la imagen.</div>";
    } else {
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $ext_permitidas)) {
            $mensaje = "<div class='alert alert-danger text-center'>❌ Formato de imagen no permitido (solo JPG, PNG o WEBP).</div>";
        } elseif ($imagen['size'] > 2 * 1024 * 1024) {
            $mensaje = "<div class='alert alert-danger text-center'>⚠️ La imagen excede 2MB.</div>";
        } else {
            $carpeta = "../../uploads/avatars/";
            if (!file_exists($carpeta)) mkdir($carpeta, 0777, true);

            $nombre_archivo = uniqid("avatar_") . "." . $extension;
            $ruta_destino = $carpeta . $nombre_archivo;
            $ruta_relativa = "uploads/avatars/" . $nombre_archivo;

            if (move_uploaded_file($imagen['tmp_name'], $ruta_destino)) {
                $insert = $con->prepare("
                    INSERT INTO avatars (name, image_url, description)
                    VALUES (:nombre, :imagen, :descripcion)
                ");
                $insert->bindParam(":nombre", $nombre);
                $insert->bindParam(":imagen", $ruta_relativa);
                $insert->bindParam(":descripcion", $descripcion);

                if ($insert->execute()) {
                    $mensaje = "<div class='alert alert-success text-center'>✅ Avatar registrado correctamente.</div>";
                } else {
                    $mensaje = "<div class='alert alert-danger text-center'>❌ Error al registrar el avatar.</div>";
                }
            } else {
                $mensaje = "<div class='alert alert-danger text-center'>❌ Error al subir la imagen.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Avatar - HALO ADMIN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center bg-secondary p-3 rounded shadow">
        <h4 class="mb-0">
            Bienvenido, <?= htmlspecialchars($fila['username']) ?> (<?= htmlspecialchars($fila['rol']) ?>)
        </h4>
        <a href="dashboard.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left-circle"></i> Volver al Panel
        </a>
    </div>

    <div class="card bg-secondary mt-4 shadow-lg border-0">
        <div class="card-body">
            <h3 class="text-center mb-4 text-info">🧍 Registrar Nuevo Avatar</h3>
            <?php if (isset($mensaje)) echo $mensaje; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del Avatar</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Spartan 117" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Imagen del Avatar</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*" required>
                        <small class="text-light-50">Formatos: JPG, PNG, WEBP | Máx. 2MB</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Breve descripción del avatar..." required></textarea>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" name="registrar_avatar" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-cloud-upload"></i> Registrar Avatar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="text-center mt-5 text-secondary">
    <small>© <?= date('Y') ?> HALO System - Administración</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
