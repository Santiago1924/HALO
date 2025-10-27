<?php
session_start();
require_once("../database/conexion.php");

$db = new Database;
$con = $db->conectar();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir y sanear los campos del formulario
    $usuario    = trim($_POST["usuario"] ?? '');
    $contrasena = trim($_POST["contrasena"] ?? '');

    // 1. Validar campos vacíos
    if (empty($usuario) || empty($contrasena)) {
        $_SESSION['login_error'] = "⚠️ Por favor, complete todos los campos.";
        header("Location: ../index.php");
        exit;
    }

    try {
        // 2. Buscar el usuario en la base de datos
        $sql = $con->prepare("SELECT id_user, username, email, password, id_rol FROM users WHERE username = :usuario");
        $sql->bindParam(':usuario', $usuario);
        $sql->execute();
        $fila = $sql->fetch(PDO::FETCH_ASSOC);

        // 3. Validar contraseña y existencia del usuario
        if ($fila && password_verify($contrasena, $fila['password'])) {
            // Guardar datos en sesión
            $_SESSION['id_user'] = $fila['id_user'];
            $_SESSION['usuario'] = $fila['username'];
            $_SESSION['email']   = $fila['email'];
            $_SESSION['id_rol']  = $fila['id_rol'];

            // 4. Redirección según el rol
            switch ($_SESSION['id_rol']) {
                case 1: // Admin
                    header("Location: ../model/admin/dashboard.php");
                    exit;
                case 2: // Jugador
                    header("Location: ../model/user/lobby.php");
                    exit;
                default:
                    $_SESSION['login_error'] = "⚠️ Rol de usuario no reconocido. Contacte al administrador.";
                    header("Location: ../index.php");
                    exit;
            }
        } else {
            $_SESSION['login_error'] = "❌ Usuario o contraseña incorrectos.";
            header("Location: ../index.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['login_error'] = "⛔ Error de conexión o consulta a la base de datos: " . $e->getMessage();
        header("Location: ../index.php");
        exit;
    }

} else {
    header("Location: ../index.php");
    exit;
}
?>
