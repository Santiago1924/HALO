<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Iniciar sesión solo si existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 💣 Desactivar caché para impedir regresar con botón atrás
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// 🧹 Limpiar variables de sesión
$_SESSION = [];

// 🍪 Borrar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 🧨 Destruir sesión
session_destroy();

// ✅ Redirigir al login
header("Location: ../index.php");
exit;
