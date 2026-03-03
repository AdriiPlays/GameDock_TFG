<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$usuario = $_SESSION["usuario"];

if (!isset($_GET["nombre"])) {
    die("Contenedor no especificado");
}

$nombre = $_GET["nombre"];

// Datos BD
$stmt = $conn->prepare("SELECT * FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$cont = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cont) {
    die("Contenedor no encontrado");
}

// Estado
$out = [];
$ret = 0;
exec('docker inspect --format="{{json .State}}" "' . $nombre . '" 2>&1', $out, $ret);

if ($ret !== 0 || empty($out)) {
    $estado = "offline";
    $state = [];
} else {
    $state = json_decode($out[0], true);
    $estado = (!empty($state["Running"]) && $state["Running"] === true) ? "online" : "offline";
}

// Puertos reales
$outPorts = [];
exec('docker inspect --format="{{json .NetworkSettings.Ports}}" "' . $nombre . '" 2>&1', $outPorts, $retPorts);
$ports = [];
if ($retPorts === 0 && !empty($outPorts)) {
    $ports = json_decode($outPorts[0], true) ?: [];
}

// Stats (CPU/RAM)
$outStats = [];
exec('docker stats "' . $nombre . '" --no-stream --format "{{json .}}" 2>&1', $outStats, $retStats);
$stats = [];
if ($retStats === 0 && !empty($outStats)) {
    $stats = json_decode($outStats[0], true) ?: [];
}

$imagenPerfil = isset($_SESSION["imagen"]) && $_SESSION["imagen"] !== "" 
    ? "uploads/" . $_SESSION["imagen"] 
    : "uploads/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Contenedor</title>
    <link rel="stylesheet" href="css/panel.css">
    <style>
        .btn-start { background: #22c55e; color: white; padding: 10px 16px; border: none; cursor: pointer; border-radius: 4px; }
        .btn-stop  { background: #ef4444; color: white; padding: 10px 16px; border: none; cursor: pointer; border-radius: 4px; }
        .btn-edit  { background: #facc15; color: black; padding: 10px 16px; border: none; cursor: pointer; border-radius: 4px; }
        .btn-delete { background: #b91c1c; color: white; padding: 10px 16px; border: none; cursor: pointer; border-radius: 4px; }
        .btn-restart { background: #3b82f6; color: white; padding: 10px 16px; border: none; cursor: pointer; border-radius: 4px; }
        .edit-container { display: flex; flex-wrap: wrap; gap: 30px; margin-top: 20px; }
        .edit-block { margin-bottom: 20px; min-width: 220px; }
        .input-edit { width: 100%; padding: 8px; margin-top: 5px; }
        .stats-box, .ports-box { margin-top: 25px; padding: 15px; border-radius: 6px; background: #111827; color: #e5e7eb; }
        .stats-box h3, .ports-box h3 { margin-top: 0; }
        .badge-online { color: #22c55e; font-weight: bold; }
        .badge-offline { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>

<div id="sidebar" class="sidebar">
    <nav class="sidebar-menu">
        <div class="menu-item user-item">
            <img src="<?= $imagenPerfil ?>" class="avatar-small" alt="Foto">
            <span><?= htmlspecialchars($usuario) ?></span>
        </div>
        <a href="panel_logs.php" class="menu-item">📜 Logs</a>
        <a href="panel.php" class="menu-item">📦 Instancias</a>
        <a href="crear_usuario.php" class="menu-item">👤 Añadir usuarios</a>
        <a href="logout.php" class="menu-item logout">🚪 Cerrar sesión</a>
    </nav>
</div>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1>
        Contenedor: <?= htmlspecialchars($nombre) ?>
        <?php if ($estado === "online"): ?>
            <span class="badge-online">🟢 Online</span>
        <?php else: ?>
            <span class="badge-offline">🔴 Offline</span>
        <?php endif; ?>
    </h1>
</header>

<main class="contenido">

<div class="edit-container">

    <div class="edit-block">
        <h3>Nombre del contenedor</h3>
        <input type="text" id="nuevoNombre" class="input-edit" value="<?= htmlspecialchars($cont['nombre']) ?>">
    </div>

    <div class="edit-block">
        <h3>Versión</h3>
        <input type="text" id="nuevaVersion" class="input-edit" value="<?= htmlspecialchars($cont['version']) ?>">
    </div>

    <div class="edit-block">
        <h3>Puerto (host)</h3>
        <input type="text" id="nuevoPuerto" class="input-edit" placeholder="Ej: 3307">
    </div>

</div>

<div style="margin-top: 20px; display:flex; gap:10px; flex-wrap:wrap;">
    <?php if ($estado === "online"): ?>
        <button class="btn-stop" onclick="accion('stop')">Apagar</button>
        <button class="btn-restart" onclick="accion('restart')">Reiniciar</button>
    <?php else: ?>
        <button class="btn-start" onclick="accion('start')">Iniciar</button>
    <?php endif; ?>

    <button class="btn-edit" onclick="guardarCambios()">Guardar cambios</button>
    <button class="btn-delete" onclick="accion('delete')">Eliminar</button>
</div>

<div class="stats-box">
    <h3>Uso de recursos</h3>
    <?php if (!empty($stats)): ?>
        <p><strong>CPU:</strong> <?= htmlspecialchars($stats["CPUPerc"] ?? "N/A") ?></p>
        <p><strong>Memoria:</strong> <?= htmlspecialchars($stats["MemUsage"] ?? "N/A") ?></p>
        <p><strong>Mem %:</strong> <?= htmlspecialchars($stats["MemPerc"] ?? "N/A") ?></p>
    <?php else: ?>
        <p>No hay estadísticas disponibles (el contenedor puede estar apagado).</p>
    <?php endif; ?>
</div>

<div class="ports-box">
    <h3>Puertos reales</h3>
    <?php if (!empty($ports)): ?>
        <ul>
            <?php foreach ($ports as $containerPort => $bindings): ?>
                <?php if (is_array($bindings)): ?>
                    <?php foreach ($bindings as $bind): ?>
                        <li>
                            <strong><?= htmlspecialchars($bind["HostIp"]) ?>:<?= htmlspecialchars($bind["HostPort"]) ?></strong>
                            → <?= htmlspecialchars($containerPort) ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><?= htmlspecialchars($containerPort) ?> (no mapeado al host)</li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Este contenedor no tiene puertos mapeados o no se pudieron leer.</p>
    <?php endif; ?>
</div>

</main>

<footer class="footer">
    Docker — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script>
const menuBtn = document.getElementById("menu-btn");
const sidebar = document.getElementById("sidebar");

menuBtn.onclick = () => {
    sidebar.classList.toggle("sidebar-open");
};

document.addEventListener("click", (e) => {
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove("sidebar-open");
    }
});

function accion(tipo) {
    if (tipo === "delete" && !confirm("¿Seguro que quieres eliminar este contenedor?")) return;

    fetch("Funciones/acciones_contenedor.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            accion: tipo,
            nombre: "<?= $nombre ?>"
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (tipo === "delete") location.href = "panel.php";
        else location.reload();
    })
    .catch(err => {
        console.error(err);
        alert("Error al ejecutar la acción");
    });
}

function guardarCambios() {
    fetch("Funciones/editar_contenedor.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nombreActual: "<?= $nombre ?>",
            nuevoNombre: document.getElementById("nuevoNombre").value,
            nuevaVersion: document.getElementById("nuevaVersion").value,
            nuevoPuerto: document.getElementById("nuevoPuerto").value
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.status === "success") location.href = "panel.php";
    })
    .catch(err => {
        console.error(err);
        alert("Error al guardar cambios");
    });
}
</script>

</body>
</html>
