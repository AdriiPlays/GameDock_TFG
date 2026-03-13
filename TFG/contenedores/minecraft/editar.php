<?php
require_once __DIR__ . "/../../config.php";
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: ../../../login.php");
    exit;
}

if (!isset($_GET["nombre"])) {
    die("No se especificó el servidor.");
}

$nombre = $_GET["nombre"];

// Obtener datos del contenedor general
$stmt = $conn->prepare("SELECT * FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$cont = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cont) {
    die("Servidor no encontrado.");
}

// Obtener datos específicos de Minecraft
$stmt2 = $conn->prepare("SELECT * FROM minecraft WHERE id = ?");
$stmt2->bind_param("i", $cont["id"]);
$stmt2->execute();
$mc = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

if (!$mc) {
    die("Datos de Minecraft no encontrados.");
}

// Estado real del contenedor
$out = [];
$ret = 0;
exec('docker inspect --format="{{json .State}}" "' . $nombre . '" 2>&1', $out, $ret);

if ($ret !== 0 || empty($out)) {
    $estado = "offline";
} else {
    $state = json_decode($out[0], true);
    $estado = (!empty($state["Running"]) && $state["Running"] === true) ? "online" : "offline";
}

$tituloPagina = "Editar Servidor Minecraft: " . htmlspecialchars($nombre);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>

    <link rel="stylesheet" href="/TFG/css/panel.css">
    <link rel="stylesheet" href="/TFG/css/minecraft.css">

  
</head>
<body>

<?php include __DIR__ . "/../../php/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

    <div class="form-box">

        <h2>Configuración</h2>

        <label>Nombre</label>
        <input type="text" id="nuevoNombre" class="input-edit" value="<?= $cont['nombre'] ?>">

        <label>Versión</label>
        <input type="text" id="nuevaVersion" class="input-edit" value="<?= $mc['version'] ?>">

        <label>Tipo</label>
        <select id="nuevoTipo" class="input-edit">
            <option value="VANILLA" <?= $mc['tipo']=="VANILLA"?"selected":"" ?>>Vanilla</option>
            <option value="FORGE" <?= $mc['tipo']=="FORGE"?"selected":"" ?>>Forge</option>
            <option value="FABRIC" <?= $mc['tipo']=="FABRIC"?"selected":"" ?>>Fabric</option>
            <option value="PAPER" <?= $mc['tipo']=="PAPER"?"selected":"" ?>>Paper</option>
            <option value="PURPUR" <?= $mc['tipo']=="PURPUR"?"selected":"" ?>>Purpur</option>
        </select>

        <label>Puerto</label>
        <input type="number" id="nuevoPuerto" class="input-edit" value="<?= $mc['puerto'] ?>">

        <button class="btn-save" onclick="guardarCambios()">Guardar cambios</button>

        <hr>

        <h2>Control del servidor</h2>

        <?php if ($estado === "online"): ?>
            <button class="btn-stop" onclick="accion('stop')">Detener</button>
            <button class="btn-start" onclick="accion('restart')">Reiniciar</button>
        <?php else: ?>
            <button class="btn-start" onclick="accion('start')">Iniciar</button>
        <?php endif; ?>

        <button class="btn-delete" onclick="accion('delete')">Eliminar servidor</button>

        <hr>

        <h2>Configuración avanzada</h2>

        <button class="btn-start" onclick="abrirConsola()">Abrir consola</button>

        <div id="consolaBox" style="
            display:none;
            background:#000;
            color:#0f0;
            padding:10px;
            height:400px;
            overflow-y:auto;
            font-family: monospace;
            border-radius:8px;
            margin-top:20px;
        "></div>

        <div style="margin-top:10px; display:none;" id="cmdBox">
            <input id="cmdInput" type="text" placeholder="Escribe un comando..." 
                style="width:80%; padding:8px; font-family:monospace;">
            <button onclick="enviarComando()" 
                style="padding:8px 15px; background:#3b82f6; color:white; border:none; border-radius:5px;">
                Enviar
            </button>
        </div>

        <button class="btn-start" onclick="location.href='server_properties/editor.php?nombre=<?= $nombre ?>'">
            Editar server.properties
        </button>

        <hr>

        <h2>FTP</h2>
        <button class="btn-start" onclick="location.href='/TFG/contenedores/minecraft/filemanager/index.php?nombre=<?= $nombre ?>'">
            📁 Abrir gestor de archivos
        </button>

    </div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script src="/TFG/JS/panel.js"></script>

<script>
// ACCIONES DEL SERVIDOR
function accion(tipo) {
    fetch("/TFG/contenedores/minecraft/api/actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ tipo, nombre: "<?= $nombre ?>" })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);

        if (tipo === "delete" && res.status === "success") {
            location.href = "/TFG/panel.php";
        } else {
            location.reload();
        }
    });
}

function guardarCambios() {
    fetch("api/edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id: <?= $cont['id'] ?>,
            nombreActual: "<?= $nombre ?>",
            nuevoNombre: document.getElementById("nuevoNombre").value,
            nuevaVersion: document.getElementById("nuevaVersion").value,
            nuevoTipo: document.getElementById("nuevoTipo").value,
            nuevoPuerto: document.getElementById("nuevoPuerto").value
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.status === "success") {
            location.href = "/TFG/panel.php";
        }
    });
}

// CONSOLA
let consolaInterval = null;

function abrirConsola() {
    const consola = document.getElementById("consolaBox");
    const cmdBox = document.getElementById("cmdBox");

    consola.style.display = "block";
    cmdBox.style.display = "block";

    consola.innerHTML = "Cargando logs...\n";

    if (consolaInterval) clearInterval(consolaInterval);

    consolaInterval = setInterval(() => {
        fetch("/TFG/contenedores/minecraft/api/console.php?nombre=<?= $nombre ?>")
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    consola.innerText = res.logs;
                    consola.scrollTop = consola.scrollHeight;
                }
            });
    }, 1000);
}

function enviarComando() {
    const cmd = document.getElementById("cmdInput").value.trim();
    if (!cmd) return;

    fetch("/TFG/contenedores/minecraft/api/command.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nombre: "<?= $nombre ?>",
            cmd: cmd
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.status === "success" ? "Comando ejecutado" : "Error: " + res.message);
    });

    document.getElementById("cmdInput").value = "";
}
</script>

</body>
</html>
