<?php
require_once "../Funciones/Sesion.php";

$stmt = $conn->prepare("SELECT correo, imagen FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($correoActual, $imagenActual);
$stmt->fetch();
$stmt->close();

$imagenPerfil = $imagenActual 
    ? "../uploads/" . $imagenActual 
    : "../uploads/default.png";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=5.0, user-scalable=yes">
    <title>Editar Usuario</title>

    <link rel="stylesheet" href="../css/temas/<?= $temaUsuario ?>.css">
    <link rel="icon" type="image/png" href="../img/iconogrande.png">
</head>
<body>

<?php include __DIR__ . "/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <button id="menu-btn" class="menu-btn">☰</button>
    <h1>Editar Usuario</h1>
</header>

<main class="contenido">

<form action="../Funciones/cambioUsuario.php" method="POST" enctype="multipart/form-data">
    <div class="edit-user-container">

        <div class="edit-user-left">
            <img src="<?= $imagenPerfil ?>" class="edit-avatar" alt="Foto de perfil">

            <label class="btn-change-img">
                Cambiar imagen
                <input type="file" name="nueva_imagen" accept="image/*" hidden>
            </label>
        </div>

        <div class="edit-user-right">
            <h1 class="edit-username"><?= htmlspecialchars($usuario) ?></h1>

            <div class="edit-block">
                <h3>Cambiar correo</h3>
                <input type="email" name="nuevo_correo" class="input-edit" placeholder="Nuevo correo" value="<?= htmlspecialchars($correoActual) ?>">
            </div>

            <div class="edit-block">
                <h2>Cambiar contraseña</h2>

                <label>Contraseña actual</label>
                <input type="password" name="pass_actual" class="input-edit" placeholder="Ingresa tu contraseña actual">

                <label>Nueva contraseña</label>
                <input type="password" name="pass_nueva" class="input-edit" placeholder="Ingresa tu nueva contraseña">

                <label>Confirmar contraseña</label>
                <input type="password" name="pass_confirmar" class="input-edit" placeholder="Confirma tu nueva contraseña">
            </div>

            <button type="submit" class="btn-save">Guardar cambios</button>
        </div>

    </div>
</form>

<!-- SELECTOR DE TEMAS -->
<div class="tema-selector">
    <h3>Seleccionar tema</h3>

    <div class="tema-opciones">

        <div class="tema-card <?= $temaUsuario === 'light' ? 'tema-activo' : '' ?>" 
             data-tema="light" 
             style="--color:#f3f4f6; color:#000;">
            🌞 Claro
        </div>

        <div class="tema-card <?= $temaUsuario === 'dark' ? 'tema-activo' : '' ?>" 
             data-tema="dark" 
             style="--color:#1e293b;">
            🌙 Oscuro
        </div>

    </div>
</div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script src="../JS/panel.js"></script>

<script>
// Menú lateral
const menuBtn = document.getElementById("menu-btn");
const sidebar = document.getElementById("sidebar");

menuBtn.onclick = () => sidebar.classList.toggle("sidebar-open");

document.addEventListener("click", (e) => {
    if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
        sidebar.classList.remove("sidebar-open");
    }
});

// Cambio de tema
document.querySelectorAll(".tema-card").forEach(card => {
    card.addEventListener("click", () => {
        const tema = card.dataset.tema;

        fetch("../api/tema.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ tema })
        })
        .then(r => r.json())
        .then(() => location.reload())
        .catch(err => console.error("Error cambiando tema:", err));
    });
});

// Previsualización de imagen
const btnChangeImg = document.querySelector(".btn-change-img");
const inputFile = btnChangeImg.querySelector("input[type='file']");
const editAvatar = document.querySelector(".edit-avatar");

inputFile.addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
            editAvatar.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
