<?php
require_once "Funciones/Sesion.php";


$tituloPagina = "Actualizar Panel";
$versionLocal = trim(file_get_contents("version.txt"));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $tituloPagina ?></title>
  <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
<link rel="stylesheet" href="css/update.css">
   <link rel="icon" type="image/png" href="img/iconogrande.png">
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

    <div class="update-actions">
        <button id="btnCheck" class="btn-save">🔍 Buscar actualizaciones</button>
    </div>

    <div id="resultado" class="resultado"></div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<!-- MODAL DE ACTUALIZACIÓN -->
<div id="updateModal" class="update-modal">
    <div class="update-box">
        <h2 id="modal-title">Preparando…</h2>

        <div class="progress-container">
            <div id="progress-bar" class="progress-bar"></div>
            <span id="progress-text" class="progress-text">0%</span>
        </div>

        <p id="progress-status" class="progress-status">Iniciando…</p>
    </div>
</div>

<audio id="update-sound" src="sounds/update_complete.mp3" preload="auto"></audio>

<script src="JS/panel.js"></script>

<script>
document.getElementById("btnCheck").onclick = () => {
    fetch("api/check_update.php")
        .then(r => r.json())
        .then(res => {
            const box = document.getElementById("resultado");

            if (res.estado === "actualizado") {
                box.innerHTML = "<p class='ok'>✔️ Tu panel está actualizado</p>";
            } else if (res.estado === "disponible") {
                box.innerHTML = `
                    <p class='new-version'>🚀 Nueva versión disponible: <b>${res.version}</b></p>
                    <button onclick="actualizar()" class="btn-update">Actualizar ahora</button>
                `;
            } else {
                box.innerHTML = "<p class='error'>❌ Error: " + res.mensaje + "</p>";
            }
        });
};

function actualizar() {
    if (!confirm("¿Seguro que quieres actualizar el panel?")) return;

    const modal = document.getElementById("updateModal");
    const bar = document.getElementById("progress-bar");
    const text = document.getElementById("progress-text");
    const status = document.getElementById("progress-status");
    const title = document.getElementById("modal-title");
    const sound = document.getElementById("update-sound");

    modal.style.display = "flex";

    bar.style.width = "0%";
    text.innerText = "0%";
    status.innerText = "Preparando…";
    title.innerText = "Preparando…";

    let progreso = 0;

    function actualizarTexto(p) {
        if (p < 20) {
            title.innerText = "Preparando…";
            status.innerText = "Preparando archivos…";
        }
        else if (p < 50) {
            title.innerText = "Descargando…";
            status.innerText = "Descargando actualización…";
        }
        else if (p < 80) {
            title.innerText = "Instalando…";
            status.innerText = "Instalando componentes…";
        }
        else if (p < 100) {
            title.innerText = "Finalizando…";
            status.innerText = "Aplicando cambios…";
        }
    }

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

    fetch("api/do_update.php")
        .then(r => r.json())
        .then(res => {

            bar.style.width = "100%";
            text.innerText = "100%";
            title.innerText = "Completado ✔";
            status.innerText = "Actualización finalizada";

            sound.play();

            setTimeout(() => {
                modal.style.display = "none";
                alert(res.mensaje);
                location.reload();
            }, 1200);
        });
}
</script>

</body>
</html>
