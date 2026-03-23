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

// Obtener datos específicos de unturned
$stmt2 = $conn->prepare("SELECT * FROM unturned WHERE id = ?");
$stmt2->bind_param("i", $cont["id"]);
$stmt2->execute();
$mc = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

if (!$mc) {
    die("Datos de unturned no encontrados.");
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

$tituloPagina = "Editar Servidor unturned: " . htmlspecialchars($nombre);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>

    <link rel="stylesheet" href="/TFG/css/panel.css">
    <link rel="stylesheet" href="/TFG/css/minecraft.css">
    <link rel="stylesheet" href="/TFG/css/mc.css">
</head>
<body>

<?php include __DIR__ . "/../../php/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

    <!-- NAV DE SECCIONES -->
    <div class="tabs">
        <button class="tab-btn active" onclick="openTab('editar')">⚙️ Información</button>
        <button class="tab-btn" onclick="openTab('control')">🖥️ Control</button>
        <button class="tab-btn" onclick="openTab('avanzado')">🧪 Avanzado</button>
        <button class="tab-btn" onclick="openTab('estado')">📊 Estado</button>
        <button class="tab-btn" onclick="openTab('ftp')">📁 FTP</button>
    </div>

    <!-- SECCIÓN EDITAR -->
    <div id="editar" class="tab-content active">

        <h2>Configuración del servidor</h2>

        <label>Nombre</label>
        <input type="text" id="nuevoNombre" class="input-edit" value="<?= $cont['nombre'] ?>">

        <label>Versión</label>
        <input type="text" id="nuevaVersion" class="input-edit" value="<?= $mc['version'] ?>" readonly>

        <label>Tipo</label>
        <input type="text" id="nuevoTipo" class="input-edit" value="<?= $mc['tipo'] ?>" readonly>

        <label>Puerto</label>
        <input type="number" id="nuevoPuerto" class="input-edit" value="<?= $mc['puerto'] ?>">

        <button class="btn-save" onclick="guardarCambios()">Guardar cambios</button>
    </div>

    <!-- SECCIÓN ESTADO -->
<div id="estado" class="tab-content">

    <h2>Estado del servidor</h2>

    <div class="estado-box">

        <!-- USO DE RAM -->
        <div class="estado-item">
            <h3>Uso de RAM</h3>
            <p id="ramUso">Cargando...</p>

            <div class="barra">
                <div id="ramBar" class="barra-fill" style="width: 0%"></div>
            </div>
        </div>

        <!-- ASIGNAR RAM -->
        <div class="estado-item">
            <h3>Asignar RAM</h3>

            <input type="range" id="ramSlider" min="512" max="8192" step="256" value="<?= $mc['ram'] ?? 2048 ?>">
            <p><span id="ramValor"><?= $mc['ram'] ?? 2048 ?></span> MB</p>

            <button class="btn-save" onclick="guardarRAM()">Guardar RAM</button>
        </div>

    </div>

</div>


    <!-- SECCIÓN CONTROL -->
    <div id="control" class="tab-content">

        <h2>Control del servidor</h2>

        <?php if ($estado === "online"): ?>
            <button class="btn-stop" onclick="accion('stop')">Detener</button>
            <button class="btn-start" onclick="accion('restart')">Reiniciar</button>
        <?php else: ?>
            <button class="btn-start" onclick="accion('start')">Iniciar</button>
        <?php endif; ?>

    <button class="btn-update" onclick="accion('update')">Actualizar servidor</button>

        <button class="btn-delete" onclick="accion('delete')">Eliminar servidor</button>
    </div>

    <!-- SECCIÓN AVANZADO -->
    <div id="avanzado" class="tab-content">

        <h2>Configuración avanzada</h2>

        <button class="btn-start" onclick="abrirConsola()">Abrir consola</button>
        <button class="btn-stop" onclick="cerrarConsola()">Cerrar consola</button>


        <div id="consolaBox" class="consola"></div>

        <div id="cmdBox" class="cmd-box">
            <input id="cmdInput" type="text" placeholder="Escribe un comando...">
            <button onclick="enviarComando()">Enviar</button>
        </div>

    
    </div>

    <!-- SECCIÓN FTP -->
    <div id="ftp" class="tab-content">

        <h2>Gestor de archivos</h2>

        <button class="btn-start" onclick="location.href='/TFG/contenedores/unturned/filemanager/index.php?nombre=<?= $nombre ?>'">
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
// CAMBIO DE TABS
function openTab(tab) {
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

    document.querySelector(`[onclick="openTab('${tab}')"]`).classList.add("active");
    document.getElementById(tab).classList.add("active");
}

// ACCIONES DEL SERVIDOR
function accion(tipo) {
    fetch("/TFG/contenedores/unturned/api/actions.php", {
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

// GUARDAR CAMBIOS
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
            nuevoPuerto: document.getElementById("nuevoPuerto").value,
            puertoActual: <?= $mc['puerto'] ?>
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
    cmdBox.style.display = "flex";

    consola.innerHTML = "Cargando logs...\n";

    if (consolaInterval) clearInterval(consolaInterval);

    consolaInterval = setInterval(() => {
        fetch("/TFG/contenedores/unturned/api/console.php?nombre=<?= $nombre ?>")
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    consola.innerText = res.logs;
                    consola.scrollTop = consola.scrollHeight;
                }
            });
    }, 1000);
}

function cerrarConsola() {
    const consola = document.getElementById("consolaBox");
    const cmdBox = document.getElementById("cmdBox");

    consola.style.display = "none";
    cmdBox.style.display = "none";

    if (consolaInterval) {
        clearInterval(consolaInterval);
        consolaInterval = null;
    }
}


function enviarComando() {
    const cmd = document.getElementById("cmdInput").value.trim();
    if (!cmd) return;

    fetch("/TFG/contenedores/unturned/api/command.php", {
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

// ===============================
// RAM EN TIEMPO REAL
// ===============================
function actualizarRAM() {
    fetch(`/TFG/contenedores/unturned/api/stats.php?nombre=<?= $nombre ?>`)
        .then(r => r.json())
        .then(data => {
            if (data.status !== "success") return;

            let used = data.used.replace("MiB", "").replace("GiB", "");
            let total = data.total.replace("MiB", "").replace("GiB", "");

            // Convertir a MB
            if (data.used.includes("GiB")) used = used * 1024;
            if (data.total.includes("GiB")) total = total * 1024;

            let porcentaje = (used / total) * 100;

            document.getElementById("ramUso").innerText =
                `${Math.round(used)} MB / ${Math.round(total)} MB`;

            document.getElementById("ramBar").style.width = porcentaje + "%";
        });
}

setInterval(actualizarRAM, 2000);
actualizarRAM();

// ===============================
// SLIDER RAM
// ===============================
document.getElementById("ramSlider").addEventListener("input", e => {
    document.getElementById("ramValor").innerText = e.target.value;
});

// ===============================
// GUARDAR RAM
// ===============================
function guardarRAM() {
    let ram = document.getElementById("ramSlider").value;

    fetch("api/edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id: <?= $cont['id'] ?>,
            nombreActual: "<?= $nombre ?>",
            nuevoNombre: document.getElementById("nuevoNombre").value,
            nuevaVersion: document.getElementById("nuevaVersion").value,
            nuevoTipo: document.getElementById("nuevoTipo").value,
            nuevoPuerto: document.getElementById("nuevoPuerto").value,
            puertoActual: <?= $mc['puerto'] ?>,
            nuevaRAM: ram 
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.status === "success") {
            location.reload();
        }
    });
}

</script>

</body>
</html>
