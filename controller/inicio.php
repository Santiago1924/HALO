<?php
session_start();
require_once("../database/conexion.php");

$db = new Database();
$con = $db->conectar();

if (isset($_POST["inicio"])) {

    $usuario   = trim($_POST["usuario"]);
    $contrasena = trim($_POST["contrasena"]); 

    // Validar campos vacíos
    if (empty($usuario) || empty($contrasena)) {
        echo "<script>alert('Por favor complete todos los campos');</script>";
        echo "<script>window.location='../index.php';</script>";
        exit();
    }

    // Buscar usuario
    $sql = $con->prepare("SELECT * FROM users WHERE username = :usuario");
    $sql->bindParam(':usuario', $usuario);
    $sql->execute();
    $fila = $sql->fetch(PDO::FETCH_ASSOC);

    // Verificar contraseña
    if ($fila && password_verify($contrasena, $fila['password'])) {

        // Guardar variables de sesión
        $_SESSION['id_user']   = $fila['id_user'];
        $_SESSION['username']  = $fila['username'];
        $_SESSION['email']     = $fila['email'];
        $_SESSION['id_rol']    = $fila['id_rol'];
        $_SESSION['points']    = $fila['points'];
        $_SESSION['level_id']  = $fila['level_id'];

        // Actualizar fecha de último login
        $update = $con->prepare("UPDATE users SET last_login = NOW() WHERE id_user = :id");
        $update->bindParam(':id', $fila['id_user']);
        $update->execute();

        // Redirigir según el rol
        if ($_SESSION['id_rol'] == 1) {
            header("Location: ../model/admin/lobby.php");
            exit();
        } elseif ($_SESSION['id_rol'] == 2) {
            header("Location: ../model/user/lobby.php");
            exit();
        } else {
            echo "<script>alert('Rol no reconocido.');</script>";
            echo "<script>window.location='../index.php';</script>";
            exit();
        }

    } else {
        echo "<script>alert('Usuario o contraseña incorrectos');</script>";
        echo "<script>window.location='../index.php';</script>";
        exit();
    }
}
?>
