<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$tituloPagina = "Sistema de Actualizaciones";

$versionLocal = trim(file_get_contents("version.txt"));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $tituloPagina ?></title>
<link rel="stylesheet" href="css/panel.css">
</head>
<body>

<?php include "php/menu.php"; ?>

<div class="main-content">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

    <h2>Versión instalada: <?= $versionLocal ?></h2>

    <button id="btnCheck" class="btn-save">🔍 Buscar actualizaciones</button>

    <div id="resultado" style="margin-top:20px; font-size:18px;"></div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script src="JS/panel.js"></script>

<script>
document.getElementById("btnCheck").onclick = () => {
    fetch("api/check_update.php")
        .then(r => r.json())
        .then(res => {
            const box = document.getElementById("resultado");

            if (res.estado === "actualizado") {
                box.innerHTML = "✔️ Tu panel está actualizado";
            } else if (res.estado === "disponible") {
                box.innerHTML = `
                    <p>🚀 Nueva versión disponible: <b>${res.version}</b></p>
                    <button onclick="actualizar()" class="btn-save">Actualizar ahora</button>
                `;
            } else {
                box.innerHTML = "❌ Error: " + res.mensaje;
            }
        });
};

function actualizar() {
    if (!confirm("¿Seguro que quieres actualizar el panel?")) return;

    fetch("api/do_update.php")
        .then(r => r.json())
        .then(res => {
            alert(res.mensaje);
            location.reload();
        });
}
</script>

</body>
</html>
