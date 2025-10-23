<?php
session_start();

// ✅ Eliminar todas las variables de sesión usadas en la recuperación
unset($_SESSION['code']);
unset($_SESSION['id_user']);

// ✅ Destruir toda la sesión
session_destroy();
session_write_close();

// ✅ Redirigir al inicio (login)
header("Location: index.php");
exit();
?>
