<?php
require_once "../../Funciones/Sesion.php";

if (!isset($_GET["nombre"])) {
    die("No se especificó el servidor.");
}

$nombre = $_GET["nombre"];

// Obtener datos del contenedor general
$stmt = $conn->prepare("SELECT * FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$cont = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cont) {
    die("Servidor no encontrado.");
}

// Obtener datos específicos de Minecraft
$stmt2 = $conn->prepare("SELECT * FROM minecraft WHERE id = ?");
$stmt2->bind_param("i", $cont["id"]);
$stmt2->execute();
$mc = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

if (!$mc) {
    die("Datos de Minecraft no encontrados.");
}

// Estado real del contenedor
$out = [];
$ret = 0;
exec('docker inspect --format="{{json .State}}" ' . escapeshellarg($nombre) . ' 2>&1', $out, $ret);

if ($ret !== 0 || empty($out)) {
    $estado = "offline";
} else {
    $state = json_decode($out[0], true);
    $estado = (!empty($state["Running"]) && $state["Running"] === true) ? "online" : "offline";
}

$tituloPagina = "Editar Servidor: " . htmlspecialchars($nombre);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
</head>
<body>

<?php include __DIR__ . "/../../php/menu.php"; ?>

<div class="main-content" id="main">

    <header class="header">
        <button id="menu-btn" class="menu-btn">☰</button>
        <h1>Gestión de Servidor: <?= htmlspecialchars($nombre) ?></h1>
    </header>

    <main class="contenido">

        <!-- NAV DE TABS -->
        <div class="tabs">
            <button class="tab-btn active" onclick="openTab('editar')">Información</button>
            <button class="tab-btn" onclick="openTab('estado')">Estado</button>
            <button class="tab-btn" onclick="openTab('control')">Control</button>
            <button class="tab-btn" onclick="openTab('avanzado')">Avanzado</button>
            <button class="tab-btn" onclick="openTab('ftp')">Archivos</button>
        </div>

        <!-- SECCIÓN INFORMACIÓN -->
        <div id="editar" class="tab-content active">
            <h2>Configuración del Servidor</h2>

            <label>Nombre del Servidor</label>
            <input type="text" id="nuevoNombre" class="input-edit" value="<?= htmlspecialchars($cont['nombre']) ?>" placeholder="Nombre del servidor">

            <label>Versión</label>
            <input type="text" id="nuevaVersion" class="input-edit" value="<?= htmlspecialchars($mc['version']) ?>" readonly>

            <label>Tipo de Servidor</label>
            <input type="text" id="nuevoTipo" class="input-edit" value="<?= htmlspecialchars($mc['tipo']) ?>" readonly>

            <label>Puerto</label>
            <input type="number" id="nuevoPuerto" class="input-edit" value="<?= htmlspecialchars($mc['puerto']) ?>" placeholder="25565">

            <button class="btn-save" onclick="guardarCambios()">Guardar Cambios</button>
        </div>

        <!-- SECCIÓN ESTADO -->
        <div id="estado" class="tab-content">
            <h2>Estado del Servidor</h2>

            <div class="estado-box">
                <!-- USO DE RAM -->
                <div class="estado-item">
                    <h3>Uso de RAM</h3>
                    <p id="ramUso">Cargando...</p>
                    <div class="barra">
                        <div id="ramBar" class="barra-fill" style="width: 0%"></div>
                    </div>
                </div>

                <!-- ASIGNAR RAM -->
                <div class="estado-item">
                    <h3>Asignar Memoria RAM</h3>
                    <input type="range" id="ramSlider" min="512" max="8192" step="256" value="<?= htmlspecialchars($mc['ram'] ?? 2048) ?>">
                    <p style="font-size: 18px; font-weight: 700; margin-top: 15px;">
                        <span id="ramValor"><?= htmlspecialchars($mc['ram'] ?? 2048) ?></span> MB
                    </p>
                    <button class="btn-save" onclick="guardarRAM()">Guardar Memoria</button>
                </div>
            </div>
        </div>

        <!-- SECCIÓN CONTROL -->
        <div id="control" class="tab-content">
            <h2>Control del Servidor</h2>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php if ($estado === "online"): ?>
                    <button class="btn-stop" onclick="accion('stop')">Detener Servidor</button>
                    <button class="btn-start" onclick="accion('restart')">Reiniciar Servidor</button>
                <?php else: ?>
                    <button class="btn-start" onclick="accion('start')">Iniciar Servidor</button>
                <?php endif; ?>

                <button class="btn-delete" onclick="accion('delete')" style="margin-left: auto;">Eliminar Servidor</button>
            </div>

            <div style="margin-top: 30px; padding: 20px; background: #161b22; border-radius: 8px; border: 1px solid #21262d;">
                <h3 style="color: #ffffff; margin-top: 0;">Estado Actual</h3>
                <p style="font-size: 16px; margin: 10px 0;">
                    Estado: 
                    <span style="font-weight: 700; <?= $estado === 'online' ? 'color: #56d364;' : 'color: #ff7b72;' ?>">
                        <?= $estado === 'online' ? '🟢 Online' : '🔴 Offline' ?>
                    </span>
                </p>
            </div>
        </div>

        <!-- SECCIÓN AVANZADO -->
        <div id="avanzado" class="tab-content">
            <h2>Configuración Avanzada</h2>

            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                <button class="btn-start" onclick="abrirConsola()">Abrir Consola</button>
                <button class="btn-stop" onclick="cerrarConsola()">Cerrar Consola</button>
            </div>

            <div id="consolaBox" class="consola"></div>

            <div id="cmdBox" class="cmd-box">
                <input id="cmdInput" type="text" placeholder="Escribe un comando...">
                <button onclick="enviarComando()">Enviar</button>
            </div>

            <button class="btn-start" onclick="location.href='/TFG/contenedores/minecraft/server_properties/editor.php?nombre=<?= htmlspecialchars($nombre) ?>'">
                Editar server.properties
            </button>
        </div>

        <!-- SECCIÓN ARCHIVOS -->
        <div id="ftp" class="tab-content">
            <h2>Gestor de Archivos</h2>

            <p style="color: #8b949e; margin-bottom: 20px;">
                Accede al gestor de archivos para administrar los datos del servidor
            </p>

            <button class="btn-start" onclick="location.href='/TFG/contenedores/minecraft/filemanager/index.php?nombre=<?= htmlspecialchars($nombre) ?>'">
                Abrir Gestor de Archivos
            </button>
        </div>

    </main>

    <footer class="footer">
        GameDock — Todos los derechos reservados © <?= date("Y") ?>
    </footer>

</div>

<script src="/TFG/JS/panel.js"></script>

<script>
const NOMBRE_SERVIDOR = "<?= htmlspecialchars($nombre) ?>";
const ID_CONTENEDOR = <?= $cont['id'] ?>;
const PUERTO_ACTUAL = <?= $mc['puerto'] ?>;

// Función para cambiar tabs
function openTab(tabName) {
    // Ocultar todos los tabs
    const tabs = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("active");
    }

    // Desactivar todos los botones
    const btns = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < btns.length; i++) {
        btns[i].classList.remove("active");
    }

    // Mostrar el tab seleccionado
    const selectedTab = document.getElementById(tabName);
    if (selectedTab) {
        selectedTab.classList.add("active");
    }
    
    // Activar el botón correspondiente
    if (event && event.target) {
        event.target.classList.add("active");
    }
}

// Inicializar slider cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function() {
    const ramSlider = document.getElementById("ramSlider");
    if (ramSlider) {
        ramSlider.addEventListener("input", function() {
            const ramValor = document.getElementById("ramValor");
            if (ramValor) {
                ramValor.textContent = this.value;
            }
        });
    }
});
</script>

<script src="/TFG/contenedores/minecraft/js.js"></script>
<?php include __DIR__ . "/../../Funciones/carga.php"; ?>
<?php include __DIR__ . "/../../Funciones/alerta.php"; ?>

</body>
</html>
