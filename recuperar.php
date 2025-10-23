<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

session_start();
require_once("database/conexion.php");

$db = new Database;
$con = $db->conectar();

if (isset($_POST['validar'])) {
    $elEmail = trim($_POST['input_correo']);
    $elUsuario = trim($_POST['input_usuario']);

    if (empty($elEmail) || empty($elUsuario)) {
        echo "<script>alert('Por favor ingrese su correo y usuario.')</script>";
        exit();
    }

    // ✅ Verificar que usuario y correo coincidan
    $stmt = $con->prepare("SELECT * FROM users WHERE email = :email AND username = :username");
    $stmt->bindParam(':email', $elEmail, PDO::PARAM_STR);
    $stmt->bindParam(':username', $elUsuario, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $codigo = rand(1000, 9999); // Código de 4 dígitos

        $_SESSION['id_user'] = $usuario['id_user'];
        $_SESSION['code'] = $codigo;
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['username'] = $usuario['username'];

        // ✅ Enviar correo
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'halo117infinity@gmail.com';
            $mail->Password   = 'kypg byvy lwht gkzr'; // contraseña de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // ✅ Configuración profesional del correo
            $mail->setFrom('halo117infinity@gmail.com', 'Soporte Halo Style');
            $mail->addReplyTo('halo117infinity@gmail.com', 'Soporte Halo Style');
            $mail->addAddress($usuario['email'], $usuario['username']);

            $mail->isHTML(true);
            $mail->Subject = 'Código de recuperación - Halo Style';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Hola {$usuario['username']},</h2>
                    <p>Recibimos una solicitud para restablecer tu contraseña en <strong>Halo Style</strong>.</p>
                    <p>Tu código de verificación es:</p>
                    <h1 style='color: #007bff; font-size: 36px;'>{$codigo}</h1>
                    <p>Este código expira en 10 minutos.</p>
                    <p>Si tú no solicitaste este cambio, puedes ignorar este correo.</p>
                    <br>
                    <hr>
                    <p style='font-size: 12px; color: #888;'>Estás recibiendo este correo porque solicitaste un cambio de contraseña en Halo Style.</p>
                </div>
            ";
            $mail->AltBody = "Tu código para restablecer la contraseña es: {$codigo}";

            $mail->send();

            header("Location: verify_code.php");
            exit();
        } catch (Exception $e) {
            echo "<script>alert('Error al enviar el correo: {$mail->ErrorInfo}')</script>";
        }
    } else {
        echo "<script>alert('El correo y el usuario no coinciden o no existen.')</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Recuperar Contraseña | Halo Style</title>
  <link rel="stylesheet" href="controller/bootstrap/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow-lg border-0 rounded-4 p-4" style="max-width: 400px; width: 100%;">
    <div class="text-center mb-3">
      <img src="controller/image/logo.png" class="rounded-circle mb-3" alt="Logo" style="width: 100px; height: 100px; object-fit: cover;">
      <h1 class="h5 fw-bold text-primary">Recuperar Contraseña</h1>
    </div>

    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="input_usuario" class="form-label">Usuario</label>
        <input type="text" name="input_usuario" id="input_usuario" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="input_correo" class="form-label">Correo electrónico</label>
        <input type="email" name="input_correo" id="input_correo" class="form-control" required>
      </div>

      <div class="d-grid">
        <button type="submit" name="validar" class="btn btn-primary">Enviar código</button>
      </div>

      <div class="text-center mt-2">
        <a href="index.php">Volver al inicio</a>
      </div>
    </form>
  </div>

  <script src="controller/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
