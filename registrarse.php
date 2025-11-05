<?php
// registrarse.php (procesa el registro)
require_once("database/conexion.php");

$db = new Database;
$con = $db->conectar();

$registro_exitoso = false; 
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    // Nota: Es mejor validar y sanitizar la entrada antes de hashear.
    $password = password_hash($_POST['clave'], PASSWORD_BCRYPT);

    $rol = 2;
    $avatar = 1;
    $status = 1;
    $level = 1;
    $points = 0;

    try {

        // ✅ ✅ Validar si el usuario o email ya existen
        $check = $con->prepare("SELECT id_user FROM users WHERE username = :username OR email = :email");
        $check->bindParam(':username', $username);
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->rowCount() > 0) {
            $error = "⚠️ El usuario o correo ya está registrado. Intenta con otro.";
        } else {

            // ✅ Si no existen, insertar
            $sql = "INSERT INTO users 
                (username, email, password, id_rol, id_avatar, points, level_id, last_login, id_status) 
                VALUES 
                (:username, :email, :password, :rol, :avatar, :points, :level, NOW(), :status)";
            
            $stmt = $con->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':avatar', $avatar);
            $stmt->bindParam(':points', $points);
            $stmt->bindParam(':level', $level);
            $stmt->bindParam(':status', $status);

            if ($stmt->execute()) {
                $registro_exitoso = true;
            } else {
                $error = "❌ Error al registrar usuario.";
            }
        }

    } catch (PDOException $e) {
        // ✅ Manejo específico para duplicados MySQL
        if ($e->errorInfo[1] == 1062) {
            $error = "⚠️ El usuario o correo ya existe, intenta otro.";
        } else {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | Halo Style</title>
    <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
    <script>
        // Si el registro fue exitoso, redirigir al login luego de 4 segundos
        function redirigirLogin() {
            setTimeout(function(){
                window.location.href = "index.php";
            }, 4000); // 4000 = 4 segundos
        }
    </script>
</head>
<body class="vh-100 bg-dark" <?php if($registro_exitoso) echo 'onload="redirigirLogin()"'; ?>>

    <div class="container-fluid h-100">
        <div class="row h-100">

            <div class="col-md-6 d-none d-md-flex flex-column justify-content-center align-items-center text-light p-5 position-relative">
                <img src="img/mapas/login_fondo.png" alt="Fondo de Registro" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover; z-index: -1;">
            </div>

            <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-light">
                <div class="col-12 col-lg-8 mx-auto p-4">
                    <h2 class="fw-bold text-dark mb-4">Crear cuenta</h2>
                    <p class="text-muted">¿Ya tienes cuenta? <a href="index.php" class="text-decoration-none">Inicia sesión aquí</a></p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger text-center"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if ($registro_exitoso): ?>
                        <div class="alert alert-success text-center">
                            ✅ **¡Registro exitoso!** Serás redirigido al inicio de sesión en 4 segundos...
                        </div>
                    <?php else: ?>
                        <form method="POST" action="registrarse.php" autocomplete="off">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="clave" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="clave" name="clave" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

</body>
</html>