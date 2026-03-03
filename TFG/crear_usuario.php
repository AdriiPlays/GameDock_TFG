<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1) {
    header("Location: panel.php");
    exit;
}


$mensaje = "";
$tipo = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);
    $admin = isset($_POST["admin"]) ? 1 : 0;

    if ($usuario === "" || $correo === "" || $password === "") {
        $mensaje = "Todos los campos son obligatorios";
        $tipo = "error";
    } else {

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

            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, correo, admin) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $usuario, $hash, $correo, $admin);

            if ($stmt->execute()) {
                header("Location: panel.php");
                exit;
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

<style>


.toggle-admin {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 12px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 55px;
    height: 28px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #d9534f;
    transition: 0.4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #5cb85c;
}

input:checked + .slider:before {
    transform: translateX(26px);
}
</style>

</head>
<body>

<div class="container">
    <h2>Crear Usuario</h2>

    <?php if ($mensaje): ?>
        <div class="alert <?= $tipo ?>"><?= $mensaje ?></div>
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

        <div class="input-group toggle-admin">
            <label for="admin">Administrador</label>

            <label class="switch">
                <input type="checkbox" name="admin" id="admin" value="1">
                <span class="slider"></span>
            </label>
        </div>

        <button type="submit" class="btn">Crear Usuario</button>
    </form>
</div>

</body>
</html>
