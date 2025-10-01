<?php
session_start();
require_once("../database/conexion.php");

$db = new Database;
$con = $db->conectar();

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir los campos del formulario
    $usuario    = trim($_POST["usuario"] ?? '');
    $contrasena = trim($_POST["contrasena"] ?? '');

    // Validar campos vacíos
    if (empty($usuario) || empty($contrasena)) {
        $mensaje = "⚠️ Por favor, complete todos los campos.";
    } else {
        // Buscar el usuario en la base de datos
        $sql = $con->prepare("SELECT * FROM users WHERE username = :usuario");
        $sql->bindParam(':usuario', $usuario);
        $sql->execute();
        $fila = $sql->fetch(PDO::FETCH_ASSOC);

        // Validar contraseña y existencia del usuario
        if ($fila && password_verify($contrasena, $fila['password'])) {

            // Guardar datos en sesión
            $_SESSION['id_user']   = $fila['id_user'];
            $_SESSION['usuario']   = $fila['username'];
            $_SESSION['email']     = $fila['email'];
            $_SESSION['id_rol']    = $fila['id_rol'];

            // Redirigir según el rol
            switch ($_SESSION['id_rol']) {
                case 1:
                    header("Location: ");
                    exit;
                case 2:
                    header("Location: ../model/user/lobby.php");
                    exit;
                default:
                    $mensaje = " Rol no reconocido. Contacta al administrador.";
            }

        } else {
            $mensaje = " Usuario o contraseña incorrectos.";
        }
    }
}
?>