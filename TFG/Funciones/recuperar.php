<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../vendor/autoload.php";

function enviarRecuperacion($conn, $correo) {

    // Comprobar si existe el correo
    $stmt = $conn->prepare("SELECT id, usuario, ultimo_reset, intentos_reset FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        return "No existe ninguna cuenta con ese correo.";
    }

    $stmt->bind_result($id, $usuario, $ultimoReset, $intentos);
    $stmt->fetch();
    $stmt->close();

    // Límite de peticiones
    $ahora = time();

    if ($ultimoReset !== null) {
        $ultimo = strtotime($ultimoReset);

        // Si han pasado menos de 60 minutos
        if ($ahora - $ultimo < 3600) {

            if ($intentos >= 2) {
                return "Has solicitado demasiados enlaces. Inténtalo de nuevo en 1 hora.";
            }

            // Aumentar intentos
            $intentos++;
        } else {
            // Ha pasado más de 1 hora | reiniciar contador
            $intentos = 1;
        }
    } else {
        // Primera vez
        $intentos = 1;
    }

    // Guardar nuevo intento y hora
    $nuevoReset = date("Y-m-d H:i:s", $ahora);

    $updateIntentos = $conn->prepare("UPDATE usuarios SET ultimo_reset = ?, intentos_reset = ? WHERE id = ?");
    $updateIntentos->bind_param("sii", $nuevoReset, $intentos, $id);
    $updateIntentos->execute();
    $updateIntentos->close();

    // Generar token y expiración
    $token = bin2hex(random_bytes(32));
    $expira = date("Y-m-d H:i:s", time() + 3600); // 1 hora

    $update = $conn->prepare("UPDATE usuarios SET token_reset = ?, token_expira = ? WHERE id = ?");
    $update->bind_param("ssi", $token, $expira, $id);
    $update->execute();
    $update->close();

    // URL de recuperación
    $url = "http://localhost/TFG/php/reset.php?token=" . $token;

    // Enviar email
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "amuroj02@gmail.com"; 
        $mail->Password = "joyj ygim zpac boha"; 
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("amuroj02@gmail.com", "GameDock");
        $mail->addAddress($correo);

        $mail->isHTML(true);
        $mail->Subject = "Recuperar contraseña - GameDock";
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
            <h2 style='text-align:center; color:#58a6ff;'>Recuperar contraseña</h2>

            <p>Hola <strong>$usuario</strong>, hemos recibido una solicitud para restablecer tu contraseña.</p>

            <div style='text-align:center; margin: 30px 0;'>
                <a href='$url' style='
                    background: #238636;
                    color: white;
                    padding: 12px 20px;
                    text-decoration: none;
                    border-radius: 6px;
                    font-size: 16px;
                    display: inline-block;
                '>Restablecer contraseña</a>
            </div>

            <p style='font-size: 14px; color:#8b949e;'>
                Este enlace expirará en 1 hora.
            </p>
        </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return "Error al enviar correo: " . $mail->ErrorInfo;
    }
}
