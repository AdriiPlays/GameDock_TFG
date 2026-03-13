<?php
session_start();
require_once "config.php";
require_once "Funciones/verificacion.php";

$check = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc();
$hayUsuarios = $check["total"] > 0;

// Seguridad
if ($hayUsuarios) {
    if (!isset($_SESSION["usuario"])) {
        header("Location: index.php");
        exit;
    }

    if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1) {
        header("Location: panel.php");
        exit;
    }
}

$mensaje = "";
$tipo = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = trim($_POST["usuario"]);
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);
    $password2 = trim($_POST["password2"]);

    // Primer usuario = admin
    $admin = $hayUsuarios ? (isset($_POST["admin"]) ? 1 : 0) : 1;

    if ($usuario === "" || $correo === "" || $password === "") {
        $mensaje = "Todos los campos son obligatorios";
        $tipo = "error";

    } elseif ($password !== $password2) {
        $mensaje = "Las contraseñas no coinciden";
        $tipo = "error";

    } else {

        // Comprobar duplicados
        $checkUser = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $checkUser->bind_param("s", $usuario);
        $checkUser->execute();
        $checkUser->store_result();

        $checkEmail = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $checkEmail->bind_param("s", $correo);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkUser->num_rows > 0) {
            $mensaje = "El usuario ya existe";
            $tipo = "error";

        } elseif ($checkEmail->num_rows > 0) {
            $mensaje = "El correo ya está registrado";
            $tipo = "error";

        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario con verificado = 0
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, correo, admin, verificado) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("sssi", $usuario, $hash, $correo, $admin);

            if ($stmt->execute()) {

                // Enviar email de verificación
                $resultado = enviarVerificacion($conn, $usuario, $correo);

                if ($resultado !== true) {
                    $mensaje = $resultado;
                    $tipo = "error";
                } else {
                    header("Location: index.php?verificacion=pendiente");
                    exit;
                }

            } else {
                $mensaje = "Error al crear el usuario: " . $stmt->error;
                $tipo = "error";
            }

            $stmt->close();
        }

        $checkUser->close();
        $checkEmail->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="css/crear_usuario.css">
</head>
<body>

<div class="container">
    <h2><?= $hayUsuarios ? "Crear Usuario" : "Crear Usuario" ?></h2>

    <?php if ($mensaje): ?>
        <div class="alert <?= $tipo ?>"><?= $mensaje ?></div>
    <?php endif; ?>

    <?php if (!$hayUsuarios): ?>
        <div class="alert info">Bienvenido a GameDock.</div>
    <?php endif; ?>

    <form method="POST">

        <div class="input-group">
            <label>Usuario</label>
            <input type="text" name="usuario" required autocomplete="username">
        </div>

        <div class="input-group">
            <label>Correo</label>
            <input type="email" name="correo" required autocomplete="email">
        </div>

        <div class="input-group">
            <label>Contraseña</label>
            <input type="password" name="password" required autocomplete="new-password">
        </div>

        <div class="input-group">
            <label>Repetir contraseña</label>
            <input type="password" name="password2" required autocomplete="new-password">
        </div>

        <?php if ($hayUsuarios): ?>
        <div class="input-group toggle-admin">
            <label for="admin">Administrador</label>

            <label class="switch">
                <input type="checkbox" name="admin" id="admin" value="1">
                <span class="slider"></span>
            </label>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn">Crear Usuario</button>
    </form>
</div>

</body>
</html>
