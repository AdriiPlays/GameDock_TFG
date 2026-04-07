<?php
require_once "../../../Funciones/Sesion.php";

$servidor = "plex_" . ($_GET["nombre"] ?? "");
$ruta = $_GET["ruta"] ?? "/data";

if (!$servidor) {
    die("No se especificó el contenedor.");
}

$tituloPagina = "Gestor de archivos - " . htmlspecialchars($servidor);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $tituloPagina ?></title>

<link rel="stylesheet" href="/TFG/css/Temas/<?= ucfirst($temaUsuario) ?>.css">
<link rel="stylesheet" href="/TFG/css/Temas/<?= ucfirst($temaUsuario) ?>/filemanager-<?= strtolower($temaUsuario) ?>.css">

</head>
<body>

<?php include __DIR__ . "/../../../php/menu.php"; ?>

<div class="main-content">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

<?php $nombreBase = preg_replace('/^plex_/', '', $servidor); ?>
<button class="btn-volver" onclick="location.href='/TFG/contenedores/plex/editar.php?nombre=<?= $nombreBase ?>'">
    ⬅️ Volver al contenedor
</button>

<h3>Ruta actual: <?= htmlspecialchars($ruta) ?></h3>

<!-- SUBIR ARCHIVOS -->
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
    fetch("api/listar.php?servidor=<?= $servidor ?>&ruta=<?= urlencode($ruta) ?>")
        .then(r => r.json())
        .then(res => {
            if (res.estado !== "exito") {
                document.getElementById("contenedor").innerHTML = "Error al cargar archivos";
                return;
            }

            let html = "";

            // Botón volver
            if ("<?= $ruta ?>" !== "/data") {
                let rutaActual = "<?= $ruta ?>";
                let partes = rutaActual.split("/");
                partes.pop();
                let rutaPadre = partes.join("/");
                if (rutaPadre === "") rutaPadre = "/data";

                html += `<div class="archivo carpeta" onclick="abrir('${rutaPadre}', true)">⬅️ Volver</div>`;
            }

            res.archivos.forEach(a => {
                html += `
                <div class="archivo ${a.es_carpeta ? 'carpeta' : ''}">
                    <span onclick="abrir('${a.ruta}', ${a.es_carpeta})">
                        ${a.es_carpeta ? "📁" : "📄"} ${a.nombre}
                    </span>

                    <button onclick="borrar('${a.ruta}')" style="float:right; margin-left:5px;">🗑️</button>

                    ${!a.es_carpeta ? `<button onclick="descargar('${a.ruta}')" style="float:right;">⬇️</button>` : ""}
                </div>`;
            });

            document.getElementById("contenedor").innerHTML = html;
        });
}

function abrir(ruta, esCarpeta) {
    if (!ruta.startsWith("/data"))
        ruta = "/data" + ruta;

    if (esCarpeta) {
        location.href = "index.php?nombre=<?= $servidor ?>&ruta=" + encodeURIComponent(ruta);
    }
}

function descargar(ruta) {
    if (!ruta.startsWith("/data"))
        ruta = "/data" + ruta;

    window.location.href = "api/descargar.php?servidor=<?= $servidor ?>&ruta=" + encodeURIComponent(ruta);
}

function borrar(ruta) {
    if (!confirm("¿Seguro que quieres borrar este archivo o carpeta?")) return;

    if (!ruta.startsWith("/data"))
        ruta = "/data" + ruta;

    let formData = new FormData();
    formData.append("servidor", "<?= $servidor ?>");
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
    formData.append("servidor", "<?= $servidor ?>");
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

document.getElementById("formSubir").addEventListener("submit", async function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    formData.append("servidor", "<?= $servidor ?>");
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
