<?php
session_start();
require_once("../../database/conexion.php");

$db = new Database;
$con = $db->conectar();

// ✅ Verifica sesión activa
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

// ✅ Consulta del usuario con su rol
$sql = $con->prepare("
    SELECT u.*, r.name AS rol_nombre 
    FROM users u 
    INNER JOIN roles r ON u.id_rol = r.id_rol 
    WHERE u.id_user = :id_user
");
$sql->bindParam(':id_user', $_SESSION['id_user'], PDO::PARAM_INT);
$sql->execute();
$fila = $sql->fetch(PDO::FETCH_ASSOC);

// ✅ Eliminar sala con manejo de errores
if (isset($_GET['eliminar'])) {
    $id_sala = (int) $_GET['eliminar'];

    if ($id_sala > 0) {
        try {
            $stmt = $con->prepare("DELETE FROM rooms WHERE id_room = :id");
            $stmt->bindParam(':id', $id_sala, PDO::PARAM_INT);
            $stmt->execute();

            $msg = "<div class='alert alert-success text-center mt-3 fw-bold'>
                        🗑️ Sala #$id_sala eliminada correctamente.
                    </div>";
        } catch (PDOException $e) {
            // ⚠️ Si hay una restricción de clave foránea, mostramos un mensaje amable
            if ($e->getCode() == "23000") {
                $msg = "<div class='alert alert-warning text-center mt-3 fw-bold'>
                            ⚠️ No puedes eliminar la <b>Sala #$id_sala</b> porque está en uso por partidas activas u otros registros. 
                            <br>Desvincúlala primero antes de eliminarla.
                        </div>";
            } else {
                // Otros errores no esperados
                $msg = "<div class='alert alert-danger text-center mt-3 fw-bold'>
                            ❌ Error al eliminar la Sala #$id_sala.<br>
                            Detalle técnico: " . htmlspecialchars($e->getMessage()) . "
                        </div>";
            }
        }
    }
}

// ✅ Crear una nueva sala
if (isset($_POST['crear_sala'])) {
    $id_world = (int)$_POST['id_world'];

    if ($id_world > 0) {
        $stmt = $con->prepare("INSERT INTO rooms (world_id, created_at) VALUES (:world, NOW())");
        $stmt->bindParam(':world', $id_world, PDO::PARAM_INT);
        $stmt->execute();

        $msg = "<div class='alert alert-success text-center mt-3 fw-bold'>
                    ✅ Sala creada correctamente en el mundo seleccionado.
                </div>";
    } else {
        $msg = "<div class='alert alert-danger text-center mt-3 fw-bold'>
                    ⚠️ Debes seleccionar un mundo antes de crear una sala.
                </div>";
    }
}

// ✅ Obtener mundos disponibles
$worlds = $con->query("SELECT id_world, name FROM worlds ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Obtener salas existentes
$salas = $con->query("
    SELECT r.id_room, w.name AS world_name, r.created_at
    FROM rooms r
    JOIN worlds w ON r.world_id = w.id_world
    ORDER BY r.id_room DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Salas - HALO ADMIN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">

<div class="container mt-5">

    <!-- 🔙 Botón de regreso -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-controller"></i> Gestión de Salas</h2>
        <a href="dashboard.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left-circle"></i> Volver al Panel
        </a>
    </div>

    <!-- 🧾 Mensajes -->
    <?php if (isset($msg)) echo $msg; ?>

    <!-- 🎮 Formulario crear sala -->
    <div class="card bg-secondary border-0 shadow-lg mb-4">
        <div class="card-body">
            <h4 class="text-center mb-3"><i class="bi bi-plus-circle"></i> Crear nueva Sala</h4>

            <form method="POST">
                <div class="row g-3 justify-content-center">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Seleccionar Mundo</label>
                        <select name="id_world" class="form-select text-center" required>
                            <option value="">-- Selecciona un Mundo --</option>
                            <?php foreach ($worlds as $world): ?>
                                <option value="<?= htmlspecialchars($world['id_world']) ?>">
                                    <?= htmlspecialchars($world['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" name="crear_sala" class="btn btn-outline-warning fw-bold px-4">
                        <i class="bi bi-joystick"></i> Crear Sala
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 🧩 Tabla de salas -->
    <div class="card bg-secondary border-0 shadow-lg">
        <div class="card-body">
            <h4 class="text-center mb-3"><i class="bi bi-list-check"></i> Salas Existentes</h4>

            <table class="table table-dark table-striped table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>ID Sala</th>
                        <th>Mundo</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($salas)): ?>
                        <?php foreach ($salas as $sala): ?>
                            <tr>
                                <td><?= htmlspecialchars($sala['id_room']) ?></td>
                                <td><?= htmlspecialchars($sala['world_name']) ?></td>
                                <td><?= htmlspecialchars($sala['created_at']) ?></td>
                                <td>
                                    <a href="?eliminar=<?= $sala['id_room'] ?>" 
                                       class="btn btn-outline-danger btn-sm fw-bold"
                                       onclick="return confirm('¿Seguro que deseas eliminar la Sala #<?= $sala['id_room'] ?>?')">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-warning fw-bold">
                                No hay salas creadas aún.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- 🔻 Footer -->
<footer class="text-center mt-5 text-secondary">
    <small>© <?= date('Y') ?> HALO System - Administración</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
