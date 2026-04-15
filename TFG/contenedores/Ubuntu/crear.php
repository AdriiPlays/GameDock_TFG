<?php
require_once "../../Funciones/Sesion.php";

$imagenPerfil = !empty($_SESSION["imagen"])
    ? "../../uploads/" . $_SESSION["imagen"]
    : "../../uploads/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Crear Servidor Ubuntu SSH</title>

    <link rel="stylesheet" href="/TFG/css/Temas/<?= ucfirst($temaUsuario) ?>.css">
    <link rel="stylesheet" href="/TFG/css/Temas/<?= ucfirst($temaUsuario) ?>/crear-<?= strtolower($temaUsuario) ?>.css">
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
        <h1>Crear Servidor Ubuntu SSH</h1>
    </header>

    <main class="contenido">

        <div class="form-box">
            <h2>Configuración</h2>

            <label for="nombre">Nombre del servidor</label>
            <input type="text" id="nombre" class="input-edit" placeholder="Ej: ubuntu01" autocomplete="off">

            <label for="password">Contraseña SSH</label>
            <input type="password" id="password" class="input-edit" placeholder="Ej: admin123" autocomplete="off">

            <label for="puerto">Puerto SSH</label>
            <input type="number" id="puerto" class="input-edit" placeholder="Ej: 2222" autocomplete="off">

            <button id="btnCrear" class="btn-save">Crear servidor</button>
            <button onclick="location.href='../../panel.php'" class="btn-cancel">Cancelar</button>
        </div>

    </main>

</div>

<script src="/TFG/JS/panel.js"></script>

<script>
// Sidebar
const menuBtn = document.getElementById("menu-btn");
const sidebar = document.getElementById("sidebar");

menuBtn.onclick = () => sidebar.classList.toggle("sidebar-open");

document.addEventListener("click", (e) => {
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove("sidebar-open");
    }
});

// Crear servidor Ubuntu SSH
document.getElementById("btnCrear").addEventListener("click", () => {
    const nombre   = document.getElementById("nombre").value.trim();
    const password = document.getElementById("password").value.trim();
    const puerto   = document.getElementById("puerto").value.trim();

    if (!nombre || !password || !puerto) {
        mostrarAlertaError("Por favor rellena todos los campos");
        return;
    }

    mostrarLoader();

    fetch("api/create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre, password, puerto })
    })
    .then(r => r.json())
    .then(res => {
        ocultarLoader();

        if (res.status === "success") {
            mostrarAlertaOK("Servidor Ubuntu SSH creado correctamente", () => {
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
