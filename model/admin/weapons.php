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

// ✅ Registrar nueva arma
if (isset($_POST['registrar_weapon'])) {
    $nombre = trim($_POST['nombre']);
    $subtipo = trim($_POST['subtipo']);
    $balas = trim($_POST['balas']);
    $danio = trim($_POST['danio']);
    $tipo = trim($_POST['tipo']); // id_type
    $imagen = $_FILES['imagen'] ?? null;

    if (empty($nombre) || empty($subtipo) || empty($balas) || empty($danio) || empty($tipo) || empty($imagen['name'])) {
        $mensaje = "<div class='alert alert-warning text-center'>⚠️ Todos los campos son obligatorios, incluida la imagen.</div>";
    } else {
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $ext_permitidas)) {
            $mensaje = "<div class='alert alert-danger text-center'>❌ Formato de imagen no permitido (solo JPG, PNG, WEBP).</div>";
        } elseif ($imagen['size'] > 2 * 1024 * 1024) {
            $mensaje = "<div class='alert alert-danger text-center'>⚠️ La imagen excede 2MB.</div>";
        } else {
            $carpeta = "../../uploads/weapons/";
            if (!file_exists($carpeta)) mkdir($carpeta, 0777, true);

            $nombre_archivo = uniqid("weapon_") . "." . $extension;
            $ruta_destino = $carpeta . $nombre_archivo;
            $ruta_relativa = "uploads/weapons/" . $nombre_archivo;

            if (move_uploaded_file($imagen['tmp_name'], $ruta_destino)) {
                $insert = $con->prepare("
                    INSERT INTO weapons (name, subtype, bullets, damage, image_url, id_type)
                    VALUES (:nombre, :subtipo, :balas, :danio, :imagen, :tipo)
                ");
                $insert->bindParam(":nombre", $nombre);
                $insert->bindParam(":subtipo", $subtipo);
                $insert->bindParam(":balas", $balas);
                $insert->bindParam(":danio", $danio);
                $insert->bindParam(":imagen", $ruta_relativa);
                $insert->bindParam(":tipo", $tipo);

                if ($insert->execute()) {
                    $mensaje = "<div class='alert alert-success text-center'>✅ Arma registrada correctamente.</div>";
                } else {
                    $mensaje = "<div class='alert alert-danger text-center'>❌ Error al registrar el arma.</div>";
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
    <title>Registrar Arma - HALO ADMIN</title>
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
            <h3 class="text-center mb-4 text-info">⚔️ Registrar Nueva Arma</h3>
            <?php if (isset($mensaje)) echo $mensaje; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del arma</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Rifle de asalto" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Subtipo</label>
                        <input type="text" name="subtipo" class="form-control" placeholder="Ej. Ligero, Automático" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Balas</label>
                        <input type="number" name="balas" class="form-control" placeholder="Ej. 30" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Daño</label>
                        <input type="number" name="danio" class="form-control" placeholder="Ej. 50" min="1" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo de Arma</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione un tipo</option>
                            <?php
                            $tipos = $con->query("SELECT id_type, type_name FROM weapon_types")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tipos as $t) {
                                echo "<option value='{$t['id_type']}'>{$t['type_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6 mt-3">
                        <label class="form-label">Imagen del arma</label>
                        <input type="file" name="imagen" class="form-control" accept="image/*" required>
                        <small class="text-light-50">Formatos: JPG, PNG, WEBP | Máx. 2MB</small>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" name="registrar_weapon" class="btn btn-outline-warning fw-bold">
                        <i class="bi bi-cloud-upload"></i>  Registrar Arma
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
