<?php
require_once "../config.php";

if (!isset($_GET["token"])) {
    $errorFatal = "Token inválido.";
} else {
    $token = $_GET["token"];

    $stmt = $conn->prepare("SELECT id, token_expira FROM usuarios WHERE token_reset = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows !== 1) {
        $errorFatal = "Token inválido o expirado.";
    } else {
        $stmt->bind_result($id, $expira);
        $stmt->fetch();
        $stmt->close();

        if (strtotime($expira) < time()) {
            $errorFatal = "El enlace ha expirado.";
        }
    }
}

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($errorFatal)) {
    $pass1 = $_POST["password"];
    $pass2 = $_POST["password2"];

    if ($pass1 !== $pass2) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE usuarios SET password = ?, token_reset = NULL, token_expira = NULL WHERE id = ?");
        $update->bind_param("si", $hash, $id);
        $update->execute();


        echo "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Contraseña cambiada</title>
            <link rel='stylesheet' href='../css/style.css'>
        </head>
        <body>

        <div class='login-container'>
            <h2>✔ Contraseña cambiada</h2>
            <p style='text-align:center; margin-bottom:20px; color:#1e293b;'>
                Tu contraseña se ha actualizado correctamente.
            </p>
            <a href='../index.php' class='btn' style='text-align:center; display:block;'>
                Iniciar sesión
            </a>
        </div>

        </body>
        </html>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Restablecer contraseña</title>
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

<?php if (isset($errorFatal)): ?>

    <div class="login-container">
        <h2>Error</h2>
        <div class="error"><?= $errorFatal ?></div>
        <a href="../index.php" class="btn" style="text-align:center; display:block;">Volver al inicio</a>
    </div>

<?php else: ?>

<div class="login-container">
    <h2>Restablecer contraseña</h2>

    <?php if ($mensaje): ?>
        <div class="error"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Nueva contraseña</label>
            <input type="password" name="password" required>
        </div>

        <div class="input-group">
            <label>Repetir contraseña</label>
            <input type="password" name="password2" required>
        </div>

        <button class="btn">Cambiar contraseña</button>
    </form>
</div>

<?php endif; ?>

</body>
</html>
