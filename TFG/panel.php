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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?></title>
   <link rel="icon" type="image/png" href="img/iconogrande.png">

  <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">

</head>
<body>

<?php include "php/menu.php"; ?>

<div class="main-content" id="main">

    <header class="header">
        <button id="menu-btn" class="menu-btn">☰</button>
        <h1><?= $tituloPagina ?></h1>
    </header>

    <main class="contenido">
        <div class="contenedores-wrapper">

            <!-- BOTÓN CREAR CONTENEDOR -->
            <button id="btnCrearContenedor" class="crear-contenedor" title="Crear nuevo servidor">
                <span class="mas">+</span>
            </button>

            <!-- LISTA DE CONTENEDORES -->
            <?php while ($c = $contenedores->fetch_assoc()): ?>
                <?php
                // Estado real del contenedor
                $out = [];
                $ret = 0;
                exec('docker inspect --format="{{json .State}}" "' . escapeshellarg($c['nombre']) . '" 2>&1', $out, $ret);

                if ($ret !== 0 || empty($out)) {
                    $estado = "offline";
                } else {
                    $state = json_decode($out[0], true);
                    $estado = (!empty($state["Running"]) && $state["Running"] === true) ? "online" : "offline";
                }

                $isoClass = 'iso-' . strtolower(preg_replace('/[^a-z0-9]/i', '', $c['iso']));
                $enlace = strtolower(preg_replace('/[^a-z0-9]/i', '', $c['iso'])) . '/editar.php?nombre=' . urlencode($c['nombre']);
                ?>

                <div class="card-contenedor <?= $isoClass ?>"
                     data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                     data-iso="<?= htmlspecialchars($c['iso']) ?>"
                     onclick="location.href='contenedores/<?= htmlspecialchars($enlace) ?>'">

                    <div class="card-header">
                        <h3><?= htmlspecialchars($c["nombre"]) ?></h3>
                        <span class="estado <?= $estado ?>" title="Estado: <?= ucfirst($estado) ?>">
                            <?= $estado === "online" ? "Online" : "Offline" ?>
                        </span>
                    </div>

                  <div class="card-info">
    
    <p class="card-version">Versión: <?= htmlspecialchars($c["version"]) ?></p>
    <p class="card-port">Puerto: <?= htmlspecialchars($c["puerto"]) ?></p>
</div>

                    <button class="btn-delete"
                            onclick="event.stopPropagation(); eliminarContenedor('<?= htmlspecialchars($c['nombre']) ?>', '<?= htmlspecialchars($c['iso']) ?>')">
                        Eliminar
                    </button>
                </div>
            <?php endwhile; ?>

        </div>
    </main>

    <footer class="footer">
        GameDock — Todos los derechos reservados © <?= date("Y") ?>
    </footer>

</div>

<!-- MODAL CREAR CONTENEDOR -->
<div id="modalCrear" class="modal">
    <div class="modal-content">
        <h2>Crear nuevo servidor</h2>

        <label for="tipoServidor">Selecciona el tipo de servidor</label>
        <select id="tipoServidor" class="input-edit">
            <option value="">-- Selecciona una opción --</option>
            <option value="minecraft">Minecraft</option>
            <option value="unturned">Unturned</option>
            <option value="python">Python</option>
            <option value="alpine">Alpine</option>
        </select>

        <button id="btnIrCrear" class="btn-save">Continuar</button>
        <button id="btnCerrar" class="btn-cancel">Cancelar</button>
    </div>
</div>

<script src="JS/panel.js"></script>

<script>
// Gestión del modal
const modal = document.getElementById("modalCrear");
const btnCrear = document.getElementById("btnCrearContenedor");
const btnCerrar = document.getElementById("btnCerrar");
const btnIrCrear = document.getElementById("btnIrCrear");
const tipoServidor = document.getElementById("tipoServidor");

btnCrear.addEventListener("click", () => {
    modal.classList.add("show");
    tipoServidor.focus();
});

btnCerrar.addEventListener("click", () => {
    modal.classList.remove("show");
});

btnIrCrear.addEventListener("click", () => {
    const tipo = tipoServidor.value;
    if (!tipo) {
        alert("Por favor selecciona un tipo de servidor");
        return;
    }
    window.location.href = "contenedores/" + tipo + "/crear.php";
});

// Cerrar modal al presionar ESC
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal.classList.contains("show")) {
        modal.classList.remove("show");
    }
});

// Función para eliminar contenedor
function eliminarContenedor(nombre, iso) {
    mostrarConfirmacion(
        `¿Seguro que quieres eliminar el servidor "${nombre}"?\n\nEsta acción no se puede deshacer.`,
        () => {
            mostrarLoader();

            fetch("contenedores/" + iso.toLowerCase() + "/api/delete.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ nombre })
            })
            .then(r => r.json())
            .then(res => {
                ocultarLoader();

                if (res.status === "success") {
                    const card = document.querySelector(`[data-nombre='${nombre}']`);
                    if (card) {
                        card.style.animation = "fadeOut 0.3s ease";
                        setTimeout(() => card.remove(), 300);
                    }

                mostrarAlertaCerrar("✅ Servidor eliminado correctamente");



                } else {
                    mostrarAlertaError("❌ Error: " + (res.message || "No se pudo eliminar"));
                }
            })
            .catch(err => {
                ocultarLoader();
                mostrarAlertaError("❌ No se pudo conectar con la API: " + err.message);
            });
        }
    );
}

</script>

<?php include __DIR__ . "/Funciones/alerta.php"; ?>
<?php include __DIR__ . "/Funciones/carga.php"; ?>

</body>
</html>
