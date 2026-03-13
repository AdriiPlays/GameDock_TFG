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
<link rel="stylesheet" href="css/update.css">
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
    <div id="progress-container" class="progress-container" style="display:none;">
    <div id="progress-bar" class="progress-bar"></div>
    <p id="progress-status" class="progress-status">Preparando…</p>
    <span id="progress-text" class="progress-text">0%</span>
</div>


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

    const bar = document.getElementById("progress-bar");
    const text = document.getElementById("progress-text");
    const container = document.getElementById("progress-container");
    const status = document.getElementById("progress-status");
    const sound = document.getElementById("update-sound");

    // Mostrar barra
    container.style.display = "block";
    bar.style.width = "0%";
    text.innerText = "0%";
    status.innerText = "Descargando…";

    let progreso = 0;

    // Cambios de texto según el progreso
    function actualizarTexto(p) {
        if (p < 30) status.innerText = "Descargando…";
        else if (p < 70) status.innerText = "Instalando…";
        else if (p < 100) status.innerText = "Finalizando…";
    }

    // Simulación de progreso realista
    const intervalo = setInterval(() => {
        progreso += Math.floor(Math.random() * 10) + 5;

        if (progreso >= 100) {
            progreso = 100;
            clearInterval(intervalo);
        }

        bar.style.width = progreso + "%";
        text.innerText = progreso + "%";
        actualizarTexto(progreso);

    }, 300);

    // Llamada real a la API
    fetch("api/do_update.php")
        .then(r => r.json())
        .then(res => {

            // Forzar barra al 100%
            bar.style.width = "100%";
            text.innerText = "100%";
            status.innerText = "Completado ✔";

            // Reproducir sonido
            sound.play();

            setTimeout(() => {
                alert(res.mensaje);
                location.reload();
            }, 800);
        });
}
</script>
<audio id="update-sound" src="sounds/update_complete.mp3" preload="auto"></audio>
</body>
</html>
