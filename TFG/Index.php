<?php
session_start();
require_once "config.php"; 
require_once "Funciones/logs.php";

// Si no hay usuarios crear el primero
$check = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc();
if ($check["total"] == 0) {
    header("Location: crear_usuario.php?primer_usuario=1");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    if ($usuario !== "" && $password !== "") {

        $stmt = $conn->prepare("SELECT id, password, imagen, admin, verificado FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            
            $stmt->bind_result($idBD, $hashBD, $imagenBD, $adminBD, $verificadoBD);
            $stmt->fetch();

            // Bloquear si no está verificado
            if ($verificadoBD == 0) {
                $error = "Debes verificar tu correo antes de iniciar sesión.";
            } 
            // Si está verificado, comprobar contraseña
            elseif (password_verify($password, $hashBD)) {

                $_SESSION["usuario"] = $usuario;
                $_SESSION["imagen"] = $imagenBD;
                $_SESSION["admin"] = $adminBD;

                registrarLog($conn, $usuario, "Inició sesión");

                header("Location: panel.php");
                exit;

            } else {
                $error = "Contraseña incorrecta";
            }

        } else {
            $error = "El usuario no existe";
        }

        $stmt->close();

    } else {
        $error = "Rellena todos los campos";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login TFG</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="login-container">
    <h2>Iniciar Sesión</h2>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <label>Usuario</label>
            <input type="text" name="usuario" required>
        </div>

        <div class="input-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn">Entrar</button>
        <a href="php/recuperar.php" class="link">¿Olvidaste tu contraseña?</a>
    </form>
</div>

</body>
</html>
