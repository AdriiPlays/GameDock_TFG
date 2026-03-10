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

$imagenPerfil = !empty($_SESSION["imagen"])
    ? "/TFG/uploads/" . $_SESSION["imagen"]
    : "/TFG/uploads/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servidor Minecraft</title>
<link rel="stylesheet" href="../../css/panel.css">

    <style>
        .form-box {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            max-width: 700px;
            margin: 30px auto;
            box-shadow: 0 0 10px #0002;
        }
        .input-edit {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .btn-save { background: #22c55e; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 6px; }
        .btn-stop { background: #ef4444; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 6px; }
        .btn-start { background: #3b82f6; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 6px; }
        .btn-delete { background: #b91c1c; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 6px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div id="sidebar" class="sidebar">
    <nav class="sidebar-menu">
        <div id="editUserBox" class="menu-item user-item">
            <img src="<?= $imagenPerfil ?>" class="avatar-small" alt="Foto">
            <span><?= htmlspecialchars($_SESSION["usuario"]) ?></span>
        </div>

        <a href="/TFG/panel.php" class="menu-item">📦 Instancias</a>
<a href="/TFG/panel_logs.php" class="menu-item">📜 Logs</a>
<a href="/TFG/crear_usuario.php" class="menu-item">👤 Añadir usuarios</a>
<a href="/TFG/logout.php" class="menu-item logout">🚪 Cerrar sesión</a>

    </nav>
</div>

<!-- MAIN -->
<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1>Editar Servidor Minecraft: <?= htmlspecialchars($nombre) ?></h1>
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

<button class="btn-start" onclick="location.href='server_properties/editor.php?nombre=<?= $nombre ?>'">
    Editar server.properties
</button>


    </div>

</main>

</div>

<!-- JS SIDEBAR -->
<script>
const menuBtn = document.getElementById("menu-btn");
const sidebar = document.getElementById("sidebar");

menuBtn.onclick = () => sidebar.classList.toggle("sidebar-open");

document.addEventListener("click", (e) => {
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove("sidebar-open");
    }
});
</script>

<!-- JS ACCIONES -->
<script>
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
            // Redirigir al panel
            location.href = "/TFG/panel.php";
        } else {
            // Para start, stop, restart
            location.reload();
        }
    })
    .catch(err => {
        console.error(err);
        alert("No se pudo conectar con la API");
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
</script>

</body>
</html>
