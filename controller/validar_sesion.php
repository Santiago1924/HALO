<?php
// ✅ Activar errores (opcional mientras debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🚫 Evitar caché para que el navegador no muestre páginas guardadas
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 🔐 Validar si el usuario está logeado
if (empty($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

// ✅ Variables globales
$id_user = $_SESSION['id_user'] ?? null;
$username = $_SESSION['usuario'] ?? "Invitado";
?>
