<?php
require_once __DIR__ . "/Funciones/Sesion.php";

$tituloPagina = "Panel de Control";

// Obtener contenedores
$contenedores = $conn->query("SELECT * FROM contenedores ORDER BY fecha_creado DESC");
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">

</head>
<body>

<?php include "php/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
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
                // Estado del contenedor
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
     data-nombre="<?= $c['nombre'] ?>"
     onclick="location.href='<?= strtolower($c['iso']) ?>/<?= $c['nombre'] ?>'">



                    <div class="card-header">
                        <h3><?= $c["nombre"] ?></h3>
                        <span class="estado <?= $estado ?>">
                            <?= $estado === "online" ? "🟢 Online" : "🔴 Offline" ?>
                        </span>
                    </div>

                    <p class="card-version">ISO: <?= $c["iso"] ?></p>
                    <p class="card-version">Versión: <?= $c["version"] ?></p>
                    <p class="card-version">Puerto: <?= $c["puerto"] ?></p>

                    <button class="btn-delete"
                            onclick="event.stopPropagation(); eliminarContenedor('<?= $c['nombre'] ?>', '<?= $c['iso'] ?>')">
                        🗑 Eliminar
                    </button>
                </div>

            <?php endwhile; ?>

        </div>
    </div>

    <!-- MODAL -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <h2>Crear nuevo servidor</h2>

            <label>Selecciona el tipo de servidor</label>
            <select id="tipoServidor" class="input-edit">
                <option value="minecraft">Minecraft</option>
                <option value="unturned">Unturned</option>
                <option value="Python">Python</option>
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

<script src="JS/panel.js"></script>

<script>
// crear contenedor + eliminar
const modal = document.getElementById("modalCrear");
document.getElementById("btnCrearContenedor").onclick = () => modal.classList.add("show");
document.getElementById("btnCerrar").onclick = () => modal.classList.remove("show");

document.getElementById("btnIrCrear").onclick = () => {
    const tipo = document.getElementById("tipoServidor").value;
    window.location.href = "contenedores/" + tipo + "/crear.php";
};

function eliminarContenedor(nombre, iso) {

    mostrarConfirmacion(
        "¿Seguro que quieres eliminar el contenedor " + nombre + "?",
        () => {

            mostrarLoader();

            fetch("contenedores/" + iso + "/api/delete.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ nombre })
            })
            .then(r => r.json())
            .then(res => {

                ocultarLoader();

                if (res.status === "success") {

                    
                    const card = document.querySelector(`[data-nombre='${nombre}']`);
                    if (card) card.remove();

                    mostrarAlertaOK("Contenedor eliminado correctamente");

                } 
            })
            .catch(err => {
                ocultarLoader();
                mostrarAlertaError("No se pudo conectar con la API");
            });
        }
    );
}


</script>
<?php include __DIR__ . "/Funciones/alerta.php"; ?>
<?php include __DIR__ . "/Funciones/carga.php"; ?>

</body>
</html>
