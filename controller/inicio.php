<?php
// Asegúrate de que este archivo está en 'controller/inicio.php'
session_start();
// La ruta a 'conexion.php' debe ser correcta relativa a este archivo.
// Asumiendo que 'conexion.php' está en '../database/conexion.php'
require_once("../database/conexion.php");

$db = new Database;
$con = $db->conectar(); // Asume que $db->conectar() devuelve el objeto PDO

// Mensaje de error (usaremos $_SESSION['login_error'] en su lugar)
// $mensaje = ''; // Ya no se necesita esta variable aquí, usamos la sesión.

if ($_SERVER["REQUEST_METHOD"] === "POST") {
<<<<<<< HEAD
    // Recibir y sanear los campos del formulario
    $usuario    = trim($_POST["usuario"] ?? '');
    $contrasena = trim($_POST["contrasena"] ?? '');

    // 1. Validar campos vacíos
    if (empty($usuario) || empty($contrasena)) {
        $_SESSION['login_error'] = "⚠️ Por favor, complete todos los campos.";
        header("Location: ../index.php"); // Redirige de vuelta al login
        exit;
    }

    try {
        // 2. Buscar el usuario en la base de datos
        // Usamos una consulta preparada para prevenir inyección SQL.
        $sql = $con->prepare("SELECT id_user, username, email, password, id_rol FROM users WHERE username = :usuario");
=======
    $usuario    = trim($_POST["usuario"] ?? '');
    $contrasena = trim($_POST["contrasena"] ?? '');

    if (empty($usuario) || empty($contrasena)) {
        $mensaje = "⚠️ Por favor, complete todos los campos.";
    } else {
        // ✅ El nombre correcto del campo es "username"
        $sql = $con->prepare("SELECT * FROM users WHERE username = :usuario");
>>>>>>> 74f6e0a259a86fd4b1e0ab0bee4503f43cab840d
        $sql->bindParam(':usuario', $usuario);
        $sql->execute();
        $fila = $sql->fetch(PDO::FETCH_ASSOC);

<<<<<<< HEAD
        // 3. Validar contraseña y existencia del usuario
        if ($fila && password_verify($contrasena, $fila['password'])) {
            // El usuario existe y la contraseña es correcta

            // Guardar datos en sesión
=======
        // ✅ El campo de la contraseña es "password"
        if ($fila && isset($fila['password']) && password_verify($contrasena, $fila['password'])) {
>>>>>>> 74f6e0a259a86fd4b1e0ab0bee4503f43cab840d
            $_SESSION['id_user']   = $fila['id_user'];
            $_SESSION['usuario']   = $fila['username'];
            $_SESSION['email']     = $fila['email'];
            $_SESSION['id_rol']    = $fila['id_rol'];

            // ✅ Redirección según el rol
            switch ($_SESSION['id_rol']) {
<<<<<<< HEAD
                case 1:
                    // ¡RUTA CORREGIDA! DEBES ESPECIFICAR LA RUTA DEL ADMINISTRADOR
=======
                case 1: // Admin
>>>>>>> 74f6e0a259a86fd4b1e0ab0bee4503f43cab840d
                    header("Location: ../model/admin/dashboard.php");
                    exit;
                case 2: // Jugador
                    header("Location: ../model/user/lobby.php");
                    exit;
                default:
<<<<<<< HEAD
                    // Rol no reconocido
                    $_SESSION['login_error'] = "⚠️ Rol de usuario no reconocido. Contacte al administrador.";
                    header("Location: ../index.php");
                    exit;
=======
                    $mensaje = "⚠️ Rol no reconocido. Contacta al administrador.";
>>>>>>> 74f6e0a259a86fd4b1e0ab0bee4503f43cab840d
            }
        } else {
<<<<<<< HEAD
            // Usuario no encontrado o contraseña incorrecta (incluye el error de hash)
            $_SESSION['login_error'] = "❌ Usuario o contraseña incorrectos.";
            header("Location: ../index.php"); // Redirige de vuelta al login
            exit;
=======
            $mensaje = "❌ Usuario o contraseña incorrectos.";
>>>>>>> 74f6e0a259a86fd4b1e0ab0bee4503f43cab840d
        }

    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        $_SESSION['login_error'] = "⛔ Error de conexión o consulta a la base de datos: " . $e->getMessage();
        header("Location: ../index.php");
        exit;
    }

} else {
    // Si alguien intenta acceder a inicio.php directamente sin POST
    header("Location: ../index.php");
    exit;
}
?>
