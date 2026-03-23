<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: /TFG/login.php");
    exit;
}

if (!isset($_GET["nombre"])) {
    die("No se especificó el servidor.");
}

$nombre = $_GET["nombre"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Commands.dat</title>

    <link rel="stylesheet" href="/TFG/css/panel.css">
    <link rel="stylesheet" href="/TFG/css/minecraft.css">
</head>
<body>

<div class="main-content">

<header class="header">
    <h1>Editar Commands.dat — <?= htmlspecialchars($nombre) ?></h1>
</header>

<main class="contenido">

    <button class="btn-volver" onclick="location.href='/TFG/contenedores/unturned/editar.php?nombre=<?= $nombre ?>'">
        ⬅️ Volver al servidor
    </button>

    <div class="editor-box">
        <textarea id="editor" class="editor-textarea"></textarea>

        <button class="btn-save" onclick="guardar()">Guardar cambios</button>
    </div>

</main>

</div>

<script>
const nombre = "<?= $nombre ?>";

// Cargar archivo Commands.dat
fetch("/TFG/contenedores/unturned/commands/api/get.php?nombre=" + nombre)
    .then(r => r.json())
    .then(res => {
        if (res.status !== "success") {
            alert(res.message);
            return;
        }

        document.getElementById("editor").value = res.content;
    });

// Guardar archivo
function guardar() {
    const contenido = document.getElementById("editor").value;

    fetch("/TFG/contenedores/unturned/commands/api/save.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre, contenido })
    })
    .then(r => r.json())
    .then(res => {
        alert("Cambios guardados.\n\nEs necesario reiniciar el servidor para aplicar los cambios.");
        window.location.href = "/TFG/panel.php";
    });
}
</script>

</body>
</html>
