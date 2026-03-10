<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: ../../login.php");
    exit;
}

$imagenPerfil = !empty($_SESSION["imagen"])
    ? "../../uploads/" . $_SESSION["imagen"]
    : "../../uploads/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Servidor Minecraft</title>
    <link rel="stylesheet" href="../../css/panel.css">
    <style>
        .form-box {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            max-width: 600px;
            margin: 30px auto;
            box-shadow: 0 0 10px #0002;
        }
        .form-box h2 {
            margin-bottom: 20px;
        }
        .input-edit {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .btn-save {
            background: #22c55e;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
        }
        .btn-cancel {
            background: #b91c1c;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            margin-left: 10px;
        }
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
    <h1>Crear Servidor Minecraft</h1>
</header>

<main class="contenido">

    <div class="form-box">
        <h2>Configuración del servidor</h2>

        <label>Nombre del servidor</label>
        <input type="text" id="nombre" class="input-edit" placeholder="Ej: survival01">

        <label>Versión</label>
        <input type="text" id="version" class="input-edit" placeholder="Ej: 1.20.4">

        <label>Tipo</label>
        <select id="tipo" class="input-edit">
            <option value="VANILLA">Vanilla</option>
            <option value="FORGE">Forge</option>
            <option value="FABRIC">Fabric</option>
            <option value="PAPER">Paper</option>
            <option value="PURPUR">Purpur</option>
        </select>

        <label>Puerto</label>
        <input type="number" id="puerto" class="input-edit" placeholder="25565">

        <button id="btnCrear" class="btn-save">Crear servidor</button>
        <button onclick="location.href='../../panel.php'" class="btn-cancel">Cancelar</button>
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
            alert("Servidor creado correctamente");
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
