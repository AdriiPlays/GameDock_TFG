<?php
require_once "../config.php";
require_once "../Funciones/recuperar.php";

$mensaje = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = trim($_POST["correo"]);

    if ($correo === "") {
        $mensaje = "Introduce tu correo.";
        $tipo = "error";
    } else {
        $resultado = enviarRecuperacion($conn, $correo);

        if ($resultado === true) {
            $mensaje = "Te hemos enviado un correo con instrucciones.";
            $tipo = "ok";
        } else {
            $mensaje = $resultado;
            $tipo = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña</title>
<link rel="stylesheet" href="../css/style.css">

<style>

.ok {
    background: #d1fae5;
    color: #065f46;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    text-align: center;
    font-size: 14px;
}
</style>

</head>
<body>

<div class="login-container">
    <h2>Recuperar contraseña</h2>

    <?php if ($mensaje): ?>
        <div class="<?= $tipo ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Correo electrónico</label>
            <input type="email" name="correo" required>
        </div>

        <button class="btn">Enviar enlace</button>

        <div style="text-align:center; margin-top:15px;">
            <a href="../index.php" class="link" style="color:#1e3a8a; text-decoration:none; font-weight:bold;">
                Volver al login
            </a>
        </div>
    </form>
</div>

</body>
</html>
