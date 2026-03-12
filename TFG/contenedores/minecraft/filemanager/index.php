<?php
session_start();

$servidor = $_GET["nombre"] ?? null;
$ruta = $_GET["ruta"] ?? "/data";

if (!$servidor) {
    die("No se especificó el servidor.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestor de archivos - <?= htmlspecialchars($servidor) ?></title>
<style>
body {
    font-family: Arial;
    background: #f3f3f3;
    padding: 20px;
}
#contenedor {
    background: white;
    padding: 20px;
    border-radius: 10px;
}
.archivo {
    padding: 8px;
    border-bottom: 1px solid #ddd;
    cursor: pointer;
}
.archivo:hover {
    background: #eee;
}
.carpeta {
    font-weight: bold;
}
</style>
</head>
<body>

<h1>📁 Gestor de archivos: <?= htmlspecialchars($servidor) ?></h1>
<h3>Ruta actual: <?= htmlspecialchars($ruta) ?></h3>

<!-- SUBIR ARCHIVO -->
<form id="formSubir" enctype="multipart/form-data" method="POST" style="margin-bottom:20px;">
    <input type="file" name="archivo" required>
    <button type="submit">⬆️ Subir archivo</button>
    <button onclick="crearCarpeta()">📁 Crear carpeta</button>

</form>

<div id="contenedor">Cargando archivos...</div>

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

            // Botón para volver atrás
           if ("<?= $ruta ?>" !== "/data") {
    let rutaActual = "<?= $ruta ?>";
    let partes = rutaActual.split("/");
    partes.pop(); // quitar carpeta actual
    let rutaPadre = partes.join("/");
    if (rutaPadre === "") rutaPadre = "/data";

    html += `<div class="archivo carpeta" onclick="abrir('${rutaPadre}', true)">⬅️ Volver</div>`;
}


            res.archivos.forEach(a => {
                html += `<div class="archivo ${a.es_carpeta ? 'carpeta' : ''}">
    <span onclick="abrir('${a.ruta}', ${a.es_carpeta})">
        ${a.es_carpeta ? "📁" : "📄"} ${a.nombre}
    </span>
${!a.es_carpeta ? `<button onclick="editar('${a.ruta}')" style="float:right; margin-right:5px;">✏️</button>` : ""}

    <button onclick="borrar('${a.ruta}')" style="float:right; margin-left:5px;">🗑️</button>

    ${!a.es_carpeta ? `<button onclick="descargar('${a.ruta}')" style="float:right;">⬇️</button>` : ""}
    
</div>`;


            });

            document.getElementById("contenedor").innerHTML = html;
        });
}

function abrir(ruta, esCarpeta) {
    // Normalizar ruta
    if (!ruta.startsWith("/")) ruta = "/" + ruta;
    if (!ruta.startsWith("/data")) ruta = "/data" + ruta;

    if (esCarpeta) {
        // Navegar a la carpeta
        location.href = "index.php?nombre=<?= $servidor ?>&ruta=" + encodeURIComponent(ruta);
    } else {
        // Abrir editor
        editar(ruta);
    }
}


function descargar(ruta) {
    // Asegurar ruta absoluta
    if (!ruta.startsWith("/")) ruta = "/" + ruta;
    if (!ruta.startsWith("/data")) ruta = "/data" + ruta;

    window.location.href = "api/descargar.php?servidor=<?= $servidor ?>&ruta=" + encodeURIComponent(ruta);
}

function borrar(ruta) {
    if (!confirm("¿Seguro que quieres borrar este archivo o carpeta?")) {
        return;
    }

    // Normalizar ruta
    if (!ruta.startsWith("/")) ruta = "/" + ruta;
    if (!ruta.startsWith("/data")) ruta = "/data" + ruta;

    let formData = new FormData();
    formData.append("servidor", "<?= $servidor ?>");
    formData.append("ruta", ruta);

    fetch("api/borrar.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.estado === "exito") {
            alert("Elemento borrado correctamente");
            cargarArchivos();
        } else {
            alert("Error: " + res.mensaje);
        }
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
        if (res.estado === "exito") {
            alert("Carpeta creada correctamente");
            cargarArchivos();
        } else {
            alert("Error: " + res.mensaje);
        }
    });
}

function editar(ruta) {
    if (!ruta.startsWith("/")) ruta = "/" + ruta;
    if (!ruta.startsWith("/data")) ruta = "/data" + ruta;

    fetch("api/leer.php?servidor=<?= $servidor ?>&ruta=" + encodeURIComponent(ruta))
        .then(r => r.json())
        .then(res => {
            if (res.estado !== "exito") {
                alert("Error al abrir el archivo");
                return;
            }

            // Crear ventana de edición
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
    formData.append("servidor", "<?= $servidor ?>");
    formData.append("ruta", ruta);
    formData.append("contenido", contenido);

    fetch("api/guardar.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.estado === "exito") {
            alert("Archivo guardado correctamente");
            location.reload();
        } else {
            alert("Error: " + res.mensaje);
        }
    });
}




// SUBIR ARCHIVO
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

    if (data.estado === "exito") {
        alert("Archivo subido correctamente");
        cargarArchivos();
    } else {
        alert("Error: " + data.mensaje);
    }
});

cargarArchivos();

</script>

</body>
</html>
