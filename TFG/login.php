<?php
session_start();
require_once "config.php"; 
require_once "Funciones/logs.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $password = trim($_POST["password"]);

    if ($usuario !== "" && $password !== "") {

  
        $stmt = $conn->prepare("SELECT id, password, imagen, admin FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $hashBD, $imagenBD, $adminBD);
            $stmt->fetch();

           
            if (password_verify($password, $hashBD)) {


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
        <a href="crear_usuario.php" class="btn btn-secundario">Crear Usuario</a>
    </form>
</div>

</body>
</html>
