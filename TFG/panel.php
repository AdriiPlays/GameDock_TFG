<?php
require_once "config.php";
$contenedores = $conn->query("SELECT * FROM contenedores ORDER BY fecha_creado DESC");

session_start();

if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit;
}

$imagenPerfil = "uploads/default.png";

if (isset($_SESSION["imagen"]) && $_SESSION["imagen"] !== "") {
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

<div class="main-content" id="main">

    <header class="header">
        <div id="menu-btn" class="menu-btn">☰</div>
        <h1>Panel de Control</h1>
    </header>

    <main class="contenido">
        <div class="contenedores-wrapper">

            <div id="btnCrearContenedor" class="contenedor crear-contenedor">
                <span class="mas">+</span>
            </div>

            <div id="listaContenedores" class="contenedores-lista">

               <?php while ($c = $contenedores->fetch_assoc()): ?>

<?php
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


                <div class="card-contenedor" onclick="location.href='editar_contenedor.php?nombre=<?= $c['nombre'] ?>'">
                    <div class="card-header">
                        <h3><?= $c["nombre"] ?></h3>
                        <span class="estado <?= $estado ?>">
                            <?= $estado === "online" ? "🟢 Online" : "🔴 Offline" ?>
                        </span>
                    </div>

                    <p class="card-version">ISO: <?= $c["iso"] ?></p>
                    <p class="card-version">Versión: <?= $c["version"] ?></p>

                    <button class="btn-delete" onclick="eliminarContenedor('<?= $c['nombre'] ?>', '<?= $c['iso'] ?>')">
                        🗑 Eliminar
                    </button>
                </div>

                <?php endwhile; ?>

            </div>

        </div>

        <div id="modalCrear" class="modal">
            <div class="modal-content">
                <h2>Crear Contenedor</h2>

                <label>Nombre del contenedor</label>
                <input type="text" id="nombreContenedor">

                <label>ISO</label>
                <select id="isoContenedor">
                    <option value="minecraft">minecraft</option>
                    <option value="mariadb">MariaDB</option>
                    <option value="debian">Debian</option>
                    <option value="alpine">Alpine</option>
                </select>

                <label>Versión</label>
                <input type="text" id="versionContenedor" placeholder="latest">

                <button id="btnCrear" class="btn-save">Crear</button>
                <button id="btnCerrar" class="btn-cancel">Cancelar</button>
            </div>
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
</script>

<script>
document.getElementById("editUserBox").addEventListener("click", function(e) {
    const tag = e.target.tagName.toLowerCase();
    if (tag !== "input" && tag !== "button" && tag !== "label") {
        window.location.href = "usuario.php";
    }
});
</script>

<script>
const modal = document.getElementById("modalCrear");
const btnCrearContenedor = document.getElementById("btnCrearContenedor");
const btnCerrar = document.getElementById("btnCerrar");

btnCrearContenedor.addEventListener("click", () => {
    modal.classList.add("show");
});

btnCerrar.addEventListener("click", () => {
    modal.classList.remove("show");
});
</script>

<script>
document.getElementById("btnCrear").addEventListener("click", () => {
    const nombre = document.getElementById("nombreContenedor").value;
    const iso = document.getElementById("isoContenedor").value;
    let version = document.getElementById("versionContenedor").value;
if (!version) version = "LATEST";


    let url = "";
    let payload = { nombre, version };

    switch (iso) {
        case "minecraft":
            url = "Api/minecraft/create.php";
            break;

        case "mariadb":
            url = "Api/mariadb/create.php";
            payload.password = prompt("Introduce la contraseña root de MariaDB:");
            break;

        default:
            alert("ISO no soportada todavía.");
            return;
    }

    fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
  .then(res => {
    console.log("RESPUESTA API:", res);

    if (res.status === "success") {
        alert("Contenedor creado correctamente");
        location.reload();
    } else {
        alert(
            "Error: " + res.message +
            "\n\nDocker dice:\n" + JSON.stringify(res.docker_output) +
            "\n\nComando ejecutado:\n" + res.cmd
        );
    }
})

    .catch(err => {
        console.error(err);
        alert("No se pudo conectar con la API");
    });
});
</script>

<script>
function eliminarContenedor(nombre, iso) {
    if (!confirm("¿Seguro que quieres eliminar el contenedor " + nombre + "?")) {
        return;
    }

    fetch("Api/" + iso + "/delete.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre: nombre })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === "success") {
            alert("Contenedor eliminado correctamente");
            location.reload();
        } else {
            alert("Error: " + res.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("No se pudo conectar con la API");
    });
}
</script>

</body>
</html>
