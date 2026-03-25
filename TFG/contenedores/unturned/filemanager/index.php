<?php
require_once "../../../Funciones/Sesion.php";

$contenedor = $_GET["nombre"] ?? null;
$ruta = $_GET["ruta"] ?? null;

if (!$contenedor) {
    die("No se especificó el servidor.");
}


$serverFolder = "DockerServer";


if (!$ruta) {
    $ruta = "/home/steam/unturned/Servers/$serverFolder";
}

$tituloPagina = "Gestor de archivos - " . htmlspecialchars($contenedor);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $tituloPagina ?></title>

     <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
    <link rel="stylesheet" href="/TFG/css/minecraft.css">
    <link rel="stylesheet" href="/TFG/css/mc.css">

</head>
<body>

<?php include __DIR__ . "/../../../php/menu.php"; ?>

<div class="main-content">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

<button class="btn-volver" onclick="location.href='/TFG/contenedores/unturned/editar.php?nombre=<?= $contenedor ?>'">
    ⬅️ Volver al servidor
</button>

<h3>Ruta actual: <?= htmlspecialchars($ruta) ?></h3>

<form id="formSubir" enctype="multipart/form-data" method="POST" style="margin-bottom:20px;">
    <input type="file" name="archivo" required>
    <button type="submit">⬆️ Subir archivo</button>
    <button type="button" onclick="crearCarpeta()">📁 Crear carpeta</button>
</form>

<div id="contenedor">Cargando archivos...</div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script src="/TFG/JS/panel.js"></script>

<script>
function cargarArchivos() {
    fetch("api/listar.php?servidor=<?= $contenedor ?>&ruta=<?= urlencode($ruta) ?>")
        .then(r => r.json())
        .then(res => {
            if (res.estado !== "exito") {
                document.getElementById("contenedor").innerHTML = "Error al cargar archivos";
                return;
            }

            let html = "";

            if ("<?= $ruta ?>" !== "/home/steam/unturned/Servers/DockerServer") {
                let rutaActual = "<?= $ruta ?>";
                let partes = rutaActual.split("/");
                partes.pop();
                let rutaPadre = partes.join("/");
                if (rutaPadre === "") rutaPadre = "/home/steam/unturned/Servers/DockerServer";

                html += `<div class="archivo carpeta" onclick="abrir('${rutaPadre}', true)">⬅️ Volver</div>`;
            }

            res.archivos.forEach(a => {
                html += `
<div class="archivo ${a.es_carpeta ? 'carpeta' : ''}">
    <span onclick="abrir('${a.ruta}', ${a.es_carpeta})">
        ${a.es_carpeta ? "📁" : "📄"} ${a.nombre}
    </span>

    <button onclick="borrar('${a.ruta}')" style="float:right; margin-left:5px;">🗑️</button>

    ${!a.es_carpeta ? `<button onclick="editar('${a.ruta}')" style="float:right; margin-right:5px;">✏️</button>` : ""}
    ${!a.es_carpeta ? `<button onclick="descargar('${a.ruta}')" style="float:right;">⬇️</button>` : ""}
</div>`;

            });

            document.getElementById("contenedor").innerHTML = html;
        });
}

function abrir(ruta, esCarpeta) {
    if (esCarpeta) {
        location.href = "index.php?nombre=<?= $contenedor ?>&ruta=" + encodeURIComponent(ruta);
    } else {
        editar(ruta);
    }
}

function descargar(ruta) {
    window.location.href = "api/descargar.php?servidor=<?= $contenedor ?>&ruta=" + encodeURIComponent(ruta);
}

function borrar(ruta) {
    if (!confirm("¿Seguro que quieres borrar este archivo o carpeta?")) return;

    let formData = new FormData();
    formData.append("servidor", "<?= $contenedor ?>");
    formData.append("ruta", ruta);

    fetch("api/borrar.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        alert(res.estado === "exito" ? "Elemento borrado" : "Error: " + res.mensaje);
        cargarArchivos();
    });
}

function crearCarpeta() {
    let nombre = prompt("Nombre de la nueva carpeta:");
    if (!nombre) return;

    let formData = new FormData();
    formData.append("servidor", "<?= $contenedor ?>");
    formData.append("ruta", "<?= $ruta ?>");
    formData.append("nombre", nombre);

    fetch("api/crear_carpeta.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        alert(res.estado === "exito" ? "Carpeta creada" : "Error: " + res.mensaje);
        cargarArchivos();
    });
}

function editar(ruta) {
    fetch("api/leer.php?servidor=<?= $contenedor ?>&ruta=" + encodeURIComponent(ruta))
        .then(r => r.json())
        .then(res => {
            if (res.estado !== "exito") return alert("Error al abrir archivo");

            let contenido = res.contenido;

            let html = `
                <div style="padding:20px;">
                    <h2>Editando: ${ruta}</h2>
                    <textarea id="editor" style="width:100%; height:400px;">${contenido}</textarea>
                    <br><br>
                    <button onclick="guardar('${ruta}')">💾 Guardar</button>
                    <button onclick="location.reload()">Cancelar</button>
                </div>
            `;

            document.body.innerHTML = html;
        });
}

function guardar(ruta) {
    let contenido = document.getElementById("editor").value;

    let formData = new FormData();
    formData.append("servidor", "<?= $contenedor ?>");
    formData.append("ruta", ruta);
    formData.append("contenido", contenido);

    fetch("api/guardar.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        alert(res.estado === "exito" ? "Archivo guardado" : "Error: " + res.mensaje);
        location.reload();
    });
}

document.getElementById("formSubir").addEventListener("submit", async function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    formData.append("servidor", "<?= $contenedor ?>");
    formData.append("ruta", "<?= $ruta ?>");

    let res = await fetch("api/subir.php", {
        method: "POST",
        body: formData
    });

    let data = await res.json();

    alert(data.estado === "exito" ? "Archivo subido" : "Error: " + data.mensaje);
    cargarArchivos();
});

cargarArchivos();
</script>

</body>
</html>
