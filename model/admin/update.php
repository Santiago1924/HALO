<?php
session_start();
require_once("../../database/conexion.php");

$db = new Database;
$con = $db->conectar();

// ✅ Obtener ID del usuario desde la URL
$id_user = $_GET['id'] ?? null;
if (!$id_user) {
    die("<div class='alert alert-danger text-center mt-5'>⚠️ ID de usuario no especificado.</div>");
}

// ✅ Obtener los datos actuales del usuario
$sql = $con->prepare("
    SELECT u.*, r.name AS rol_nombre, s.name AS estado_nombre
    FROM users u
    INNER JOIN roles r ON u.id_rol = r.id_rol
    INNER JOIN status s ON u.id_status = s.id_status
    WHERE u.id_user = :id_user
");
$sql->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$sql->execute();
$usua = $sql->fetch(PDO::FETCH_ASSOC);

if (!$usua) {
    die("<div class='alert alert-danger text-center mt-5'>⚠️ Usuario no encontrado.</div>");
}

// ✅ ACTUALIZAR USUARIO
if (isset($_POST['update'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $con->prepare("
            UPDATE users 
            SET username = :username, email = :email, password = :password, id_rol = :rol, id_status = :estado
            WHERE id_user = :id_user
        ");
        $update->bindParam(':password', $password_hash);
    } else {
        $update = $con->prepare("
            UPDATE users 
            SET username = :username, email = :email, id_rol = :rol, id_status = :estado
            WHERE id_user = :id_user
        ");
    }

    $update->bindParam(':username', $username);
    $update->bindParam(':email', $email);
    $update->bindParam(':rol', $rol);
    $update->bindParam(':estado', $estado);
    $update->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $update->execute();

    echo "<div class='alert alert-success text-center'>✅ Usuario actualizado correctamente.</div>";

    // Refrescar datos
    $sql->execute();
    $usua = $sql->fetch(PDO::FETCH_ASSOC);
}

// ✅ ELIMINAR USUARIO
if (isset($_POST['delete'])) {
    $delete = $con->prepare("DELETE FROM users WHERE id_user = :id_user");
    $delete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $delete->execute();
    echo "<div class='alert alert-danger text-center'>🗑️ Usuario eliminado correctamente.</div>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light" onload="centrar();">

<script>
function centrar() {
    const iz = (screen.width - document.body.clientWidth) / 2;
    const de = (screen.height - document.body.clientHeight) / 2;
    window.moveTo(iz, de);
}
</script>

<div class="container mt-4">
    <div class="card bg-secondary shadow border-0">
        <div class="card-body">
            <h3 class="text-center mb-4">✏️ Editar Usuario</h3>

            <form method="POST">
                <!-- Usuario -->
                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="username" class="form-control"
                           value="<?= htmlspecialchars($usua['username'] ?? '') ?>" required>
                </div>

                <!-- Correo -->
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($usua['email'] ?? '') ?>" required>
                </div>

                <!-- Contraseña -->
                <div class="mb-3">
                    <label class="form-label">Contraseña (opcional)</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Dejar vacío para no cambiar">
                </div>

                <!-- Rol -->
                <div class="mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="">Seleccione un rol</option>
                        <?php
                        $roles = $con->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($roles as $r) {
                            $selected = ($r['id_rol'] == $usua['id_rol']) ? 'selected' : '';
                            echo "<option value='{$r['id_rol']}' $selected>{$r['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Estado -->
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="">Seleccione un estado</option>
                        <?php
                        $estados = $con->query("SELECT * FROM status")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($estados as $e) {
                            $selected = ($e['id_status'] == $usua['id_status']) ? 'selected' : '';
                            echo "<option value='{$e['id_status']}' $selected>{$e['name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between">
                    <button type="submit" name="update" class="btn btn-info">Actualizar</button>
                    <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">Eliminar</button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>
