<?php
session_start();
require_once("../database/conexion.php");

$db = new Database;
$con = $db->conectar();

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario    = trim($_POST["usuario"] ?? '');
    $contrasena = trim($_POST["contrasena"] ?? '');

    if (empty($usuario) || empty($contrasena)) {
        $mensaje = "⚠️ Por favor, complete todos los campos.";
    } else {
        // ✅ El nombre correcto del campo es "username"
        $sql = $con->prepare("SELECT * FROM users WHERE username = :usuario");
        $sql->bindParam(':usuario', $usuario);
        $sql->execute();
        $fila = $sql->fetch(PDO::FETCH_ASSOC);

        // ✅ El campo de la contraseña es "password"
        if ($fila && isset($fila['password']) && password_verify($contrasena, $fila['password'])) {
            $_SESSION['id_user']   = $fila['id_user'];
            $_SESSION['usuario']   = $fila['username'];
            $_SESSION['email']     = $fila['email'];
            $_SESSION['id_rol']    = $fila['id_rol'];

            // ✅ Redirección según el rol
            switch ($_SESSION['id_rol']) {
                case 1: // Admin
                    header("Location: ../model/admin/dashboard.php");
                    exit;
                case 2: // Jugador
                    header("Location: ../model/user/lobby.php");
                    exit;
                default:
                    $mensaje = "⚠️ Rol no reconocido. Contacta al administrador.";
            }
        } else {
            $mensaje = "❌ Usuario o contraseña incorrectos.";
        }
    }
}
?>
