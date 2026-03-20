<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION["usuario"];


$stmt = $conn->prepare("SELECT correo, imagen FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($correoActual, $imagenActual);
$stmt->fetch();
$stmt->close();


$imagenPerfil = $imagenActual ? "uploads/" . $imagenActual : "uploads/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/panel.css">
</head>
<body>


<?php include "php/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1>Editar Usuario</h1>
</header>


<main class="contenido">

<form action="Funciones/cambioUsuario.php" method="POST" enctype="multipart/form-data">
    <div class="edit-user-container">

       
        <div class="edit-user-left">
            <img src="<?= $imagenPerfil ?>" class="edit-avatar" alt="Foto de perfil">

            <label class="btn-change-img">
                Cambiar imagen
                <input type="file" name="nueva_imagen" accept="image/*" hidden>
            </label>
        </div>

       
        <div class="edit-user-right">
            <h1 class="edit-username"><?= htmlspecialchars($usuario) ?></h1>

            <div class="edit-block">
                <h3>Cambiar correo</h3>
                <input type="email" name="nuevo_correo" class="input-edit" placeholder="Nuevo correo" value="<?= $correoActual ?>">
            </div>

            <div class="edit-block">
                <h2>Cambiar contraseña</h2>

                <label>Contraseña actual</label>
                <input type="password" name="pass_actual" class="input-edit">

                <label>Nueva contraseña</label>
                <input type="password" name="pass_nueva" class="input-edit">

                <label>Confirmar contraseña</label>
                <input type="password" name="pass_confirmar" class="input-edit">
            </div>

            <button type="submit" class="btn-save">Guardar</button>
        </div>

    </div>
</form>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>
<script src="JS/panel.js"></script>
<script>
const menuBtn = document.getElementById("menu-btn");
const sidebar = document.getElementById("sidebar");

menuBtn.onclick = () => {
    sidebar.classList.toggle("sidebar-open");
};

document.addEventListener("click", (e) => {
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove("sidebar-open");
    }
});
</script>

</body>
</html>
