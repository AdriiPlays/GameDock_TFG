<?php
require_once "config.php";
session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$contenedores = $conn->query("SELECT * FROM contenedores ORDER BY fecha_creado DESC");

$imagenPerfil = "uploads/default.png";
if (!empty($_SESSION["imagen"])) {
    $imagenPerfil = "uploads/" . $_SESSION["imagen"];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Usuario</title>
    <link rel="stylesheet" href="css/panel.css">
</head>
<body>

<!-- SIDEBAR -->
<div id="sidebar" class="sidebar">
    <nav class="sidebar-menu">
        <div id="editUserBox" class="menu-item user-item">
            <img src="<?= $imagenPerfil ?>" class="avatar-small" alt="Foto">
            <span><?= htmlspecialchars($_SESSION["usuario"]) ?></span>
        </div>

        <a href="panel_logs.php" class="menu-item">📜 Logs</a>
        <a href="panel.php" class="menu-item">📦 Instancias</a>
        <a href="crear_usuario.php" class="menu-item">👤 Añadir usuarios</a>
        <a href="logout.php" class="menu-item logout">🚪 Cerrar sesión</a>
    </nav>
</div>

<!-- MAIN -->
<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1>Panel de Control</h1>
</header>

<main class="contenido">

    <div class="contenedores-wrapper">

        <!-- BOTÓN CREAR -->
        <div id="btnCrearContenedor" class="contenedor crear-contenedor">
            <span class="mas">+</span>
        </div>

        <!-- LISTA DE CONTENEDORES -->
        <div id="listaContenedores" class="contenedores-lista">

            <?php while ($c = $contenedores->fetch_assoc()): ?>

                <?php
                // Estado real del contenedor
                $out = [];
                $ret = 0;
                exec('docker inspect --format="{{json .State}}" "' . $c['nombre'] . '" 2>&1', $out, $ret);

                if ($ret !== 0 || empty($out)) {
                    $estado = "offline";
                } else {
                    $state = json_decode($out[0], true);
                    $estado = (!empty($state["Running"]) && $state["Running"] === true) ? "online" : "offline";
                }
                ?>

                <div class="card-contenedor iso-<?= strtolower($c['iso']) ?>"
                     onclick="location.href='contenedores/<?= strtolower($c['iso']) ?>/editar.php?nombre=<?= $c['nombre'] ?>'">

                    <div class="card-header">
                        <h3><?= $c["nombre"] ?></h3>
                        <span class="estado <?= $estado ?>">
                            <?= $estado === "online" ? "🟢 Online" : "🔴 Offline" ?>
                        </span>
                    </div>

                    <p class="card-version">ISO: <?= $c["iso"] ?></p>
                    <p class="card-version">Versión: <?= $c["version"] ?></p>

                    <button class="btn-delete"
                            onclick="event.stopPropagation(); eliminarContenedor('<?= $c['nombre'] ?>', '<?= $c['iso'] ?>')">
                        🗑 Eliminar
                    </button>
                </div>

            <?php endwhile; ?>

        </div>
    </div>

    <!-- MODAL CREAR -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <h2>Crear nuevo servidor</h2>

            <label>Selecciona el tipo de servidor</label>
            <select id="tipoServidor" class="input-edit">
                <option value="minecraft">Minecraft</option>
                <option value="mariadb">MariaDB</option>
                <option value="debian">Debian</option>
                <option value="alpine">Alpine</option>
            </select>

            <button id="btnIrCrear" class="btn-save">Continuar</button>
            <button id="btnCerrar" class="btn-cancel">Cancelar</button>
        </div>
    </div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<!-- JS SIDEBAR -->
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
</script>

<!-- JS PERFIL -->
<script>
document.getElementById("editUserBox").addEventListener("click", function(e) {
    const tag = e.target.tagName.toLowerCase();
    if (!["input", "button", "label"].includes(tag)) {
        window.location.href = "usuario.php";
    }
});
</script>

<!-- JS MODAL CREAR -->
<script>
const modal = document.getElementById("modalCrear");
document.getElementById("btnCrearContenedor").onclick = () => modal.classList.add("show");
document.getElementById("btnCerrar").onclick = () => modal.classList.remove("show");

document.getElementById("btnIrCrear").onclick = () => {
    const tipo = document.getElementById("tipoServidor").value;
    window.location.href = "contenedores/" + tipo + "/crear.php";
};
</script>

<!-- JS ELIMINAR -->
<script>
function eliminarContenedor(nombre, iso) {
    if (!confirm("¿Seguro que quieres eliminar el contenedor " + nombre + "?")) return;

    fetch("contenedores/" + iso + "/api/delete.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            alert("Contenedor eliminado correctamente");
            location.reload();
        } else {
            alert("Error: " + res.message);
        }
    });
}
</script>

</body>
</html>
