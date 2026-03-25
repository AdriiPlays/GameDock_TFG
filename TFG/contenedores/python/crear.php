<?php
require_once "../../Funciones/Sesion.php";

$imagenPerfil = !empty($_SESSION["imagen"])
    ? "../../uploads/" . $_SESSION["imagen"]
    : "../../uploads/default.png";

$tituloPagina = "Crear Contenedor Python";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
     <title>Crear Servidor Python</title>
  <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
    <link rel="stylesheet" href="/TFG/css/minecraft.css">
</head>

<body>

<div id="sidebar" class="sidebar">
    <nav class="sidebar-menu">
        <div id="editUserBox" class="menu-item user-item">
            <img src="<?= $imagenPerfil ?>" class="avatar-small" alt="Foto">
            <span><?= htmlspecialchars($_SESSION["usuario"]) ?></span>
        </div>

        <a href="../../panel.php" class="menu-item">📦 Instancias</a>
        <a href="../../panel_logs.php" class="menu-item">📜 Logs</a>
        <a href="../../crear_usuario.php" class="menu-item">👤 Añadir usuarios</a>
        <a href="../../logout.php" class="menu-item logout">🚪 Cerrar sesión</a>
    </nav>
</div>


<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

    <div class="form-box">
        <h2>Configuración del contenedor Python</h2>

        <label>Nombre del contenedor</label>
        <input type="text" id="nombre" oninput="this.value = this.value.replace(/[^a-zA-Z0-9_\-]/g, '-')" class="input-edit" placeholder="Ej: python01">

        <label>Puerto</label>
        <input type="number" id="puerto" class="input-edit" placeholder="8000">

        <button id="btnCrear" class="btn-save">Crear contenedor</button>
        <button onclick="location.href='../../panel.php'" class="btn-cancel">Cancelar</button>
    </div>

</main>

</div>


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


<script>
document.getElementById("btnCrear").addEventListener("click", () => {

    const nombre = document.getElementById("nombre").value.trim();
    const puerto = document.getElementById("puerto").value.trim();

    if (!nombre || !puerto) {
        alert("Rellena todos los campos.");
        return;
    }

    // MOSTRAR LOADER
    mostrarLoader();

    fetch("api/create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre, puerto }) // ← CORREGIDO
    })
    .then(r => r.json())
    .then(res => {

        // OCULTAR LOADER
        ocultarLoader();

       if (res.status === "success") {

    mostrarAlertaOK("Servidor de Python creado correctamente", () => {
        location.href = "../../panel.php";
    });

} else {

    mostrarAlertaError("Error: " + res.message + "\n\n" + (res.docker_output || ""));

}

    })
    .catch(err => {

        // OCULTAR LOADER
        ocultarLoader();

        console.error(err);
        mostrarAlertaError("No se pudo conectar con la API");

    });
});
</script>


<?php include __DIR__ . "/../../Funciones/carga.php"; ?>
<?php include __DIR__ . "/../../Funciones/alerta.php"; ?>
</body>
</html>
