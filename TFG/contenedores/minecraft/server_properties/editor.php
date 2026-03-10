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
    <title>Editar server.properties</title>

    <!-- RUTA ABSOLUTA PARA QUE SIEMPRE FUNCIONE -->
    <link rel="stylesheet" href="/TFG/css/panel.css">

    <style>
        .editor-box {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #0002;
        }
        .linea {
            display: flex;
            gap: 10px;
            margin-bottom: 8px;
        }
        .linea label {
            width: 250px;
            font-weight: bold;
        }
        .linea input {
            flex: 1;
            padding: 6px;
        }
        .btn-save {
            background: #22c55e;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="main-content">

<header class="header">
    <h1>Editar server.properties — <?= htmlspecialchars($nombre) ?></h1>
</header>

<main class="contenido">

    <div class="editor-box">
        <div id="editor"></div>

        <button class="btn-save" onclick="guardar()">Guardar cambios</button>
    </div>

</main>

</div>

<script>
const nombre = "<?= $nombre ?>";

// Cargar archivo
fetch("/TFG/contenedores/minecraft/server_properties/api/get.php?nombre=" + nombre)
    .then(r => r.json())
    .then(res => {
        if (res.status !== "success") {
            alert(res.message);
            return;
        }

        const editor = document.getElementById("editor");
        editor.innerHTML = "";

        res.lines.forEach((linea, i) => {
            if (!linea.includes("=")) return;

            const [key, value] = linea.split("=");

            editor.innerHTML += `
                <div class="linea">
                    <label>${key}</label>
                    <input type="text" id="line_${i}" value="${value}">
                </div>
            `;
        });
    });

// Guardar archivo
function guardar() {
    const inputs = document.querySelectorAll("#editor input");
    let contenido = "";

    inputs.forEach(input => {
        const key = input.previousElementSibling.innerText;
        const value = input.value;
        contenido += key + "=" + value + "\n";
    });

    fetch("/TFG/contenedores/minecraft/server_properties/api/save.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre, contenido })
    })
    .then(r => r.json())
    .then(res => {
        alert("Cambios guardados.\n\nEs necesario reiniciar el servidor para aplicar los cambios.");

        // Redirigir al panel de listado
        window.location.href = "/TFG/panel.php";
    });
}
</script>

</body>
</html>
