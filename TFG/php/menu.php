<?php
if (!isset($_SESSION)) session_start();

$imagenPerfil = !empty($_SESSION["imagen"])
    ? "/TFG/uploads/" . $_SESSION["imagen"]
    : "/TFG/uploads/default.png";
?>

<div id="sidebar" class="sidebar">
    <nav class="sidebar-menu">

 <a href="/TFG/PHP/usuario.php" id="editUserBox" class="menu-item user-item">
            <img src="<?= $imagenPerfil ?>" class="avatar-small" alt="Foto">
            <span><?= htmlspecialchars($_SESSION["usuario"]) ?></span>
</a>

        <a href="/TFG/panel.php" class="menu-item">📦 Instancias</a>
        <a href="/TFG/PHP/panel_logs.php" class="menu-item">📜 Logs</a>
        <a href="/TFG/php/estado.php" class="menu-item">📊 Estado del Servidor</a>
        <a href="/TFG/crear_usuario.php" class="menu-item">👤 Añadir usuarios</a>
        <a href="/TFG/panel_update.php" class="menu-item">🔄 Actualizaciones</a>
        <a href="/TFG/logout.php" class="menu-item logout">🚪 Cerrar sesión</a>

    </nav>
</div>
