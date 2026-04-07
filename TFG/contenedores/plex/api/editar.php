<?php
require_once "../../Funciones/Sesion.php";

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

// Estado real del contenedor Plex
$out = [];
$ret = 0;
exec('docker inspect --format="{{json .State}}" ' . escapeshellarg("plex_" . $nombre) . ' 2>&1', $out, $ret);

if ($ret !== 0 || empty($out)) {
    $estado = "offline";
} else {
    $state = json_decode($out[0], true);
    $estado = (!empty($state["Running"]) && $state["Running"] === true) ? "online" : "offline";
}

$tituloPagina = "Editar Plex: " . htmlspecialchars($nombre);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $tituloPagina ?></title>

<link rel="stylesheet" href="/TFG/css/Temas/<?= ucfirst($temaUsuario) ?>.css">
<link rel="stylesheet" href="/TFG/css/Temas/<?= ucfirst($temaUsuario) ?>/editar-<?= strtolower($temaUsuario) ?>.css">

</head>
<body>

<?php include __DIR__ . "/../../php/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

    <!-- NAV DE TABS -->
    <div class="tabs">
        <button class="tab-btn active" onclick="openTab(event, 'info')">Información</button>
        <button class="tab-btn" onclick="openTab(event, 'estado')">Estado</button>
        <button class="tab-btn" onclick="openTab(event, 'control')">Control</button>
        <button class="tab-btn" onclick="openTab(event, 'ftp')">Archivos</button>
    </div>

    <!-- TAB INFORMACIÓN -->
    <div id="info" class="tab-content active">
        <h2>Información del Servidor Plex</h2>

        <label>Nombre del Servidor</label>
        <input type="text" id="nuevoNombre" class="input-edit" value="<?= htmlspecialchars($cont['nombre']) ?>">

        <button class="btn-save" onclick="guardarCambios()">Guardar Cambios</button>
    </div>

    <!-- TAB ESTADO -->
    <div id="estado" class="tab-content">
        <h2>Estado del Servidor</h2>

        <div style="padding: 20px; border-radius: 12px; border: 2px solid #21262d;">
            <h3 style="margin-top: 0;">Estado Actual</h3>
            <p style="font-size: 16px; margin: 10px 0;">
                Estado:
                <span style="font-weight: 700; <?= $estado === 'online' ? 'color: #56d364;' : 'color: #ff7b72;' ?>">
                    <?= $estado === 'online' ? 'Online' : 'Offline' ?>
                </span>
            </p>
        </div>
    </div>

    <!-- TAB CONTROL -->
    <div id="control" class="tab-content">
        <h2>Control del Servidor</h2>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 30px;">
            <?php if ($estado === "online"): ?>
                <button class="btn-stop" onclick="accion('stop')">Detener Servidor</button>
                <button class="btn-start" onclick="accion('restart')">Reiniciar Servidor</button>
            <?php else: ?>
                <button class="btn-start" onclick="accion('start')">Iniciar Servidor</button>
            <?php endif; ?>

            <button class="btn-delete" onclick="accion('delete')" style="margin-left: auto;">Eliminar Servidor</button>
        </div>
    </div>

    <!-- TAB ARCHIVOS -->
    <div id="ftp" class="tab-content">
        <h2>Gestor de Archivos</h2>

        <p style="margin-bottom: 20px; opacity: 0.8;">
            Accede al gestor de archivos para administrar la configuración y la biblioteca multimedia.
        </p>

        <button class="btn-start" onclick="location.href='/TFG/contenedores/plex/filemanager/index.php?nombre=<?= htmlspecialchars($nombre) ?>'">
            📁 Abrir Gestor de Archivos
        </button>
    </div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script src="/TFG/JS/panel.js"></script>

<script>
const NOMBRE_SERVIDOR = "<?= htmlspecialchars($nombre) ?>";

// Cambiar tabs
function openTab(event, tabName) {
    const tabs = document.getElementsByClassName("tab-content");
    for (let t of tabs) t.classList.remove("active");

    const btns = document.getElementsByClassName("tab-btn");
    for (let b of btns) b.classList.remove("active");

    document.getElementById(tabName).classList.add("active");
    event.target.classList.add("active");
}

// Guardar cambios (solo nombre)
function guardarCambios() {
    const nuevoNombre = document.getElementById("nuevoNombre").value.trim();

    if (!nuevoNombre) {
        mostrarAlertaError("El nombre no puede estar vacío");
        return;
    }

    mostrarLoader();

    fetch("api/edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nombreActual: NOMBRE_SERVIDOR,
            nuevoNombre
        })
    })
    .then(r => r.json())
    .then(res => {
        ocultarLoader();

        if (res.status === "success") {
            mostrarAlertaOK(res.message, () => location.href = "../../panel.php");
        } else {
            mostrarAlertaError(res.message);
        }
    });
}

// Acciones start/stop/restart/delete
function accion(tipo) {
    mostrarLoader();

    fetch("/TFG/contenedores/plex/api/actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ tipo, nombre: NOMBRE_SERVIDOR })
    })
    .then(r => r.json())
    .then(res => {
        ocultarLoader();

        if (res.status === "success") {
            mostrarAlertaOK(res.message, () => location.reload());
        } else {
            mostrarAlertaError(res.message);
        }
    })
    .catch(() => {
        ocultarLoader();
        mostrarAlertaError("No se pudo conectar con la API");
    });
}
</script>

<?php include __DIR__ . "/../../Funciones/carga.php"; ?>
<?php include __DIR__ . "/../../Funciones/alerta.php"; ?>

</body>
</html>
