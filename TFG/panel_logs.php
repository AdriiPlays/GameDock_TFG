<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: Index.php");
    exit;
}

$tituloPagina = "Logs del Sistema";

$usuario = $_SESSION["usuario"];

$stmt = $conn->prepare("SELECT imagen FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($imagenActual);
$stmt->fetch();
$stmt->close();

$imagenPerfil = $imagenActual ? "uploads/" . $imagenActual : "uploads/default.png";

$porPagina = 6;
$pagina = isset($_GET["pagina"]) ? (int)$_GET["pagina"] : 1;
$inicio = ($pagina - 1) * $porPagina;

$buscar = $_GET["buscar"] ?? "";
$filtroFecha = $_GET["fecha"] ?? "";
$filtroUsuario = $_GET["usuario_filtro"] ?? "";

$where = "WHERE 1=1";

if ($buscar !== "") {
    $b = "%$buscar%";
    $where .= " AND (usuario LIKE '$b' OR accion LIKE '$b')";
}

if ($filtroFecha !== "") {
    $where .= " AND fecha LIKE '$filtroFecha%'";
}

if ($filtroUsuario !== "") {
    $where .= " AND usuario = '$filtroUsuario'";
}

$total = $conn->query("SELECT COUNT(*) AS total FROM logs $where")->fetch_assoc()["total"];
$totalPaginas = ceil($total / $porPagina);

$query = "SELECT * FROM logs $where ORDER BY fecha DESC LIMIT $inicio, $porPagina";
$logs = $conn->query($query);

$usuarios = $conn->query("SELECT DISTINCT usuario FROM logs");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" href="css/panel.css">
    <link rel="stylesheet" href="css/logs.css">
</head>
<body>

<?php include "php/menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

<div class="logs-wrapper">
    <div class="logs-title">📜 Historial de Actividad</div>

    <form method="GET" class="filtros">
        <input type="text" name="buscar" placeholder="Buscar..." value="<?= $buscar ?>">
        <input type="date" name="fecha" value="<?= $filtroFecha ?>">

        <select name="usuario_filtro">
            <option value="">Todos los usuarios</option>
            <?php while ($u = $usuarios->fetch_assoc()): ?>
                <option value="<?= $u['usuario'] ?>" <?= $filtroUsuario == $u['usuario'] ? "selected" : "" ?>>
                    <?= $u['usuario'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button class="btn-pag">Filtrar</button>
    </form>

    <table class="tabla-logs">
        <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Acción</th>
        </tr>

        <?php while ($row = $logs->fetch_assoc()): ?>
        <?php
            $accion = strtolower($row["accion"]);
            $clase = "log-info";
            if (str_contains($accion, "creó") || str_contains($accion, "crear")) $clase = "log-create";
            if (str_contains($accion, "eliminó") || str_contains($accion, "borrar")) $clase = "log-delete";
            if (str_contains($accion, "editó") || str_contains($accion, "editar")) $clase = "log-edit";
            if (str_contains($accion, "inició sesión")) $clase = "log-login";
            if (str_contains($accion, "cerró sesión")) $clase = "log-logout";
        ?>
        <tr>
            <td><?= $row["fecha"] ?></td>
            <td><?= htmlspecialchars($row["usuario"]) ?></td>
            <td class="<?= $clase ?>"><?= htmlspecialchars($row["accion"]) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="paginacion">
        <?php if ($pagina > 1): ?>
            <a href="?pagina=<?= $pagina - 1 ?>&buscar=<?= $buscar ?>&fecha=<?= $filtroFecha ?>&usuario_filtro=<?= $filtroUsuario ?>" class="btn-pag">Anterior</a>
        <?php endif; ?>

        <span>Página <?= $pagina ?> de <?= $totalPaginas ?></span>

        <?php if ($pagina < $totalPaginas): ?>
            <a href="?pagina=<?= $pagina + 1 ?>&buscar=<?= $buscar ?>&fecha=<?= $filtroFecha ?>&usuario_filtro=<?= $filtroUsuario ?>" class="btn-pag">Siguiente</a>
        <?php endif; ?>
    </div>

</div>

</main>

<footer class="footer">
    GameDock — Todos los derechos reservados © <?= date("Y") ?>
</footer>

</div>

<script src="JS/panel.js"></script>

</body>
</html>
