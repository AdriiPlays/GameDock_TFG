<?php
require_once "../../Funciones/Sesion.php";

if (!isset($_GET["nombre"])) {
    die("No se especificó el servidor.");
}

$nombre = $_GET["nombre"];

// Obtener datos del contenedor 
$stmt = $conn->prepare("SELECT * FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$cont = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cont) {
    die("Servidor no encontrado.");
}

// Obtener datos de unturned
$stmt2 = $conn->prepare("SELECT * FROM unturned WHERE id = ?");
$stmt2->bind_param("i", $cont["id"]);
$stmt2->execute();
$mc = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

if (!$mc) {
    die("Datos de unturned no encontrados.");
}

// Estado del contenedor
$out = [];
$ret = 0;
exec('docker inspect --format="{{json .State}}" "' . $nombre . '" 2>&1', $out, $ret);

if ($ret !== 0 || empty($out)) {
    $estado = "offline";
} else {
    $state = json_decode($out[0], true);
    $estado = (!empty($state["Running"]) && $state["Running"] === true) ? "online" : "offline";
}

$tituloPagina = "Editar Servidor unturned: " . htmlspecialchars($nombre);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>


  <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">

</head>
<body>

<?php include __DIR__ . "/../../php/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">


    <div class="tabs">
        <button class="tab-btn active" onclick="openTab('editar')">⚙️ Información</button>
        <button class="tab-btn" onclick="openTab('control')">🖥️ Control</button>
        <button class="tab-btn" onclick="openTab('avanzado')">🧪 Avanzado</button>
        <button class="tab-btn" onclick="openTab('estado')">📊 Estado</button>
        <button class="tab-btn" onclick="openTab('ftp')">📁 FTP</button>
    </div>


    <div id="editar" class="tab-content active">

        <h2>Configuración del servidor</h2>

        <label>Nombre</label>
        <input type="text" id="nuevoNombre" class="input-edit" value="<?= $cont['nombre'] ?>">

        <label>Versión</label>
        <input type="text" id="nuevaVersion" class="input-edit" value="<?= $mc['version'] ?>" readonly>

        <label>Tipo</label>
        <input type="text" id="nuevoTipo" class="input-edit" value="<?= $mc['tipo'] ?>" readonly>

        <label>Puerto</label>
        <input type="number" id="nuevoPuerto" class="input-edit" value="<?= $mc['puerto'] ?>">

        <button class="btn-save" onclick="guardarCambios()">Guardar cambios</button>
    </div>


<div id="estado" class="tab-content">

    <h2>Estado del servidor</h2>

    <div class="estado-box">

      
        <div class="estado-item">
            <h3>Uso de RAM</h3>
            <p id="ramUso">Cargando...</p>

            <div class="barra">
                <div id="ramBar" class="barra-fill" style="width: 0%"></div>
            </div>
        </div>

       
        <div class="estado-item">
            <h3>Asignar RAM</h3>

            <input type="range" id="ramSlider" min="512" max="8192" step="256" value="<?= $mc['ram'] ?? 2048 ?>">
            <p><span id="ramValor"><?= $mc['ram'] ?? 2048 ?></span> MB</p>

            <button class="btn-save" onclick="guardarRAM()">Guardar RAM</button>
        </div>

    </div>

</div>



    <div id="control" class="tab-content">

        <h2>Control del servidor</h2>

        <?php if ($estado === "online"): ?>
            <button class="btn-stop" onclick="accion('stop')">Detener</button>
            <button class="btn-start" onclick="accion('restart')">Reiniciar</button>
        <?php else: ?>
            <button class="btn-start" onclick="accion('start')">Iniciar</button>
        <?php endif; ?>

    <button class="btn-update" onclick="accion('update')">Actualizar servidor</button>

        <button class="btn-delete" onclick="accion('delete')">Eliminar servidor</button>
    </div>

  
    <div id="avanzado" class="tab-content">

        <h2>Configuración avanzada</h2>

        <button class="btn-start" onclick="abrirConsola()">Abrir consola</button>
        <button class="btn-stop" onclick="cerrarConsola()">Cerrar consola</button>


        <div id="consolaBox" class="consola"></div>

        <div id="cmdBox" class="cmd-box">
            <input id="cmdInput" type="text" placeholder="Escribe un comando...">
            <button onclick="enviarComando()">Enviar</button>
        </div>

    
    </div>

  
    <div id="ftp" class="tab-content">

        <h2>Gestor de archivos</h2>

        <button class="btn-start" onclick="location.href='/TFG/contenedores/unturned/filemanager/index.php?nombre=<?= $nombre ?>'">
            📁 Abrir gestor de archivos
        </button>
    </div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script src="/TFG/JS/panel.js"></script>

<script>
const NOMBRE_SERVIDOR = "<?= $nombre ?>";
const ID_CONTENEDOR = <?= $cont['id'] ?>;
const PUERTO_ACTUAL = <?= $mc['puerto'] ?>;
</script>

<script src="/TFG/contenedores/unturned/js.js"></script>

<?php include __DIR__ . "/../../Funciones/alerta.php"; ?>
<?php include __DIR__ . "/../../Funciones/carga.php"; ?>

</body>
</html>
