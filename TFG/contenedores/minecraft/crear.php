<?php
require_once "../../Funciones/Sesion.php";

$imagenPerfil = !empty($_SESSION["imagen"])
    ? "../../uploads/" . $_SESSION["imagen"]
    : "../../uploads/default.png";

$listaVersiones = [
    "1.21.4", "1.21.3", "1.21.2", "1.21.1", "1.21",
    "1.20.6", "1.20.5", "1.20.4", "1.20.3", "1.20.2", "1.20.1", "1.20",
    "1.19.4", "1.19.3", "1.19.2", "1.19.1", "1.19",
    "1.18.2", "1.18.1", "1.18",
    "1.17.1", "1.17",
    "1.16.5", "1.16.4", "1.16.3", "1.16.2", "1.16.1", "1.16",
    "1.15.2", "1.15.1", "1.15",
    "1.14.4", "1.14.3", "1.14.2", "1.14.1", "1.14",
    "1.13.2", "1.13.1", "1.13",
    "1.12.2", "1.12.1", "1.12",
    "1.11.2", "1.11.1", "1.11",
    "1.10.2", "1.10.1", "1.10",
    "1.9.4", "1.9.3", "1.9.2", "1.9.1", "1.9",
    "1.8.9", "1.8.8", "1.8.7", "1.8.6", "1.8.5", "1.8.4", "1.8.3", "1.8.2", "1.8.1", "1.8",
    "1.7.10", "1.7.9", "1.7.8", "1.7.7", "1.7.6", "1.7.5", "1.7.4", "1.7.2",
    "1.6.4", "1.6.2", "1.6.1",
    "1.5.2", "1.5.1",
    "1.4.7", "1.4.6",
    "1.3.2",
    "1.2.5",
    "1.1",
    "1.0"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Crear Servidor Minecraft</title>
    <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
   <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>/crear-<?= $temaUsuario ?>.css">
    <link rel="icon" type="image/png" href="/TFG/img/iconogrande.png">
</head>
<body>

<div id="sidebar" class="sidebar">
    <nav class="sidebar-menu">
        <div id="editUserBox" class="menu-item user-item">
            <img src="<?= $imagenPerfil ?>" class="avatar-small" alt="Foto de perfil">
            <span><?= htmlspecialchars($_SESSION["usuario"]) ?></span>
        </div>

        <a href="../../panel.php" class="menu-item">Instancias</a>
        <a href="../../panel_logs.php" class="menu-item">Logs</a>
        <a href="../../crear_usuario.php" class="menu-item">Añadir usuarios</a>
        <a href="../../logout.php" class="menu-item logout">Cerrar sesión</a>
    </nav>
</div>

<div class="main-content" id="main">

    <header class="header">
        <button id="menu-btn" class="menu-btn">☰</button>
        <h1>Crear Servidor Minecraft</h1>
    </header>

    <main class="contenido">

        <div class="form-box">
            <h2>Configuración</h2>

            <label for="nombre">Nombre del servidor</label>
            <input type="text" id="nombre" class="input-edit" placeholder="Ej: survival01" autocomplete="off">

            <label for="version">Versión</label>
            <select id="version" class="input-edit">
                <?php foreach ($listaVersiones as $v): ?>
                    <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="tipo">Tipo de servidor</label>
            <select id="tipo" class="input-edit">
                <option value="VANILLA">Vanilla</option>
                <option value="FORGE">Forge</option>
                <option value="FABRIC">Fabric</option>
                <option value="PAPER">Paper</option>
                <option value="PURPUR">Purpur</option>
            </select>

            <label for="puerto">Puerto</label>
            <input type="number" id="puerto" class="input-edit" placeholder="25565" autocomplete="off">

            <button id="btnCrear" class="btn-save">Crear servidor</button>
            <button onclick="location.href='../../panel.php'" class="btn-cancel">Cancelar</button>
        </div>

    </main>

</div>

<script src="/TFG/JS/panel.js"></script>

<script>
// Menú sidebar
const menuBtn = document.getElementById("menu-btn");
const sidebar = document.getElementById("sidebar");

menuBtn.onclick = () => sidebar.classList.toggle("sidebar-open");

document.addEventListener("click", (e) => {
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove("sidebar-open");
    }
});

// Crear servidor
document.getElementById("btnCrear").addEventListener("click", () => {
    const nombre = document.getElementById("nombre").value.trim();
    const version = document.getElementById("version").value.trim();
    const tipo = document.getElementById("tipo").value;
    const puerto = document.getElementById("puerto").value.trim();

    if (!nombre || !version || !puerto) {
        mostrarAlertaError("Por favor rellena todos los campos");
        return;
    }

    if (isNaN(puerto) || parseInt(puerto) < 1024 || parseInt(puerto) > 65535) {
        mostrarAlertaError("El puerto debe estar entre 1024 y 65535");
        return;
    }

    mostrarLoader();

    fetch("api/create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre, version, tipo, puerto })
    })
    .then(r => r.json())
    .then(res => {
        ocultarLoader();

        if (res.status === "success") {
            mostrarAlertaOK("Servidor de Minecraft creado correctamente", () => {
                location.href = "../../panel.php";
            });
        } else {
            mostrarAlertaError("Error: " + res.message + "\n\n" + (res.docker_output || ""));
        }
    })
    .catch(err => {
        ocultarLoader();
        console.error(err);
        mostrarAlertaError("No se pudo conectar con la API");
    });
});
</script>

<?php include __DIR__ . "/../../Funciones/alerta.php"; ?>
<?php include __DIR__ . "/../../Funciones/carga.php"; ?>

</body>
</html>
