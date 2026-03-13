<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

function enviarVerificacion($conn, $usuario, $correo) {

    // Generar token seguro
    $token = bin2hex(random_bytes(32));

    // Guardarlo en la BD
    $stmt = $conn->prepare("UPDATE usuarios SET token_verificacion = ?, verificado = 0 WHERE usuario = ?");
    $stmt->bind_param("ss", $token, $usuario);
    $stmt->execute();
    $stmt->close();

    //URL de verificación
    $url = "http://localhost/TFG/php/verificar.php?token=" . $token;

    //Enviar email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "amuroj02@gmail.com"; 
        $mail->Password = "joyj ygim zpac boha"; 
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("TU_CORREO@gmail.com", "GameDock");
        $mail->addAddress($correo);

        $mail->isHTML(true);
        $mail->Subject = "Verifica tu cuenta en GameDock";

$mail->isHTML(true);
$mail->Body = "
<div style='
    font-family: Arial, sans-serif;
    background: #0d1117;
    color: #e6edf3;
    padding: 20px;
    border-radius: 10px;
    max-width: 500px;
    margin: auto;
    border: 1px solid #30363d;
'>
    <h2 style='text-align:center; color:#58a6ff;'>Bienvenido a GameDock</h2>

    <p style='font-size: 15px; line-height: 1.6;'>
        ¡Hola! Gracias por registrarte en <strong>GameDock</strong>.  
        Antes de poder iniciar sesión, necesitamos confirmar que este correo te pertenece.
    </p>

    <div style='text-align:center; margin: 30px 0;'>
        <a href='$url' style='
            background: #238636;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            display: inline-block;
        '>✔ Verificar mi cuenta</a>
    </div>

    <p style='font-size: 14px; color:#8b949e;'>
        Si no has creado una cuenta en GameDock, puedes ignorar este mensaje.
    </p>

    <hr style='border: 0; border-top: 1px solid #30363d; margin: 25px 0;'>

    <p style='font-size: 12px; color:#6e7681; text-align:center;'>
        © " . date('Y') . " GameDock — Plataforma de gestión de contenedores
    </p>
</div>
";


        $mail->send();
        return true;

    } catch (Exception $e) {
        return "Error al enviar email: " . $mail->ErrorInfo;
    }
}
