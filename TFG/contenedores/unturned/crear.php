<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../login.php");
    exit;
}

$imagenPerfil = !empty($_SESSION["imagen"])
    ? "../../uploads/" . $_SESSION["imagen"]
    : "../../uploads/default.png";

/* ============================
   LISTA DE VERSIONES UNTURNED
   ============================ */

$listaVersiones = [
    "3.24.0.0", "3.23.11.0", "3.23.10.0", "3.23.9.0", "3.23.8.0",
    "3.23.7.0", "3.23.6.0", "3.23.5.0", "3.23.4.0", "3.23.3.0",
    "3.23.2.0", "3.23.1.0", "3.23.0.0",
    "3.22.0.0", "3.21.0.0", "3.20.0.0"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Servidor Unturned</title>
    <link rel="stylesheet" href="/TFG/css/panel.css">
    <link rel="stylesheet" href="/TFG/css/minecraft.css"> 
</head>
<style>

#loadingOverlay {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    color: white;
    font-size: 20px;
    font-weight: bold;
}

#loadingOverlay.active {
    display: flex;
}

.spinner {
    border: 6px solid #f3f3f3;
    border-top: 6px solid #3498db;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

</style>
<body>

<!-- SIDEBAR -->
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

<!-- MAIN -->
<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1>Crear Servidor Unturned</h1>
</header>

<main class="contenido">

    <div class="form-box">
        <h2>Configuración del servidor</h2>

        <label>Nombre del servidor</label>
        <input type="text" id="nombre" class="input-edit" placeholder="Ej: unturned01">

        <label>Versión</label>
        <select id="version" class="input-edit">
            <?php foreach ($listaVersiones as $v): ?>
                <option value="<?= $v ?>"><?= $v ?></option>
            <?php endforeach; ?>
        </select>

        <label>Tipo</label>
        <select id="tipo" class="input-edit">
            <option value="VANILLA">Vanilla</option>
            <option value="ROCKETMOD">RocketMod</option>
            <option value="OPENMOD">OpenMod</option>
        </select>

        <label>Puerto</label>
        <input type="number" id="puerto" class="input-edit" placeholder="27015">

        <button id="btnCrear" class="btn-save">Crear servidor</button>
        <button onclick="location.href='../../panel.php'" class="btn-cancel">Cancelar</button>
    </div>

</main>

</div>

<!-- JS MENU LATERAL -->
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



<!-- JS CREAR SERVIDOR -->
<script>
document.getElementById("btnCrear").addEventListener("click", () => {

    const nombre = document.getElementById("nombre").value.trim();
    const version = document.getElementById("version").value.trim();
    const tipo = document.getElementById("tipo").value;
    const puerto = document.getElementById("puerto").value.trim();

    if (!nombre || !version || !puerto) {
        alert("Rellena todos los campos.");
        return;
    }



    fetch("api/create.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nombre, version, tipo, puerto })
    })
    .then(r => r.json())
    .then(res => {

     

        if (res.status === "success") {
            alert("Servidor de Unturned creado correctamente");
            location.href = "../../panel.php";
        } else {
            alert("Error: " + res.message + "\n\n" + (res.docker_output || ""));
        }
    })
    .catch(err => {
        console.error(err);



        alert("No se pudo conectar con la API");
    });
});



</script>





</body>
</html>
