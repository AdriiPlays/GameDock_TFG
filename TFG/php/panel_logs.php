<?php
require_once __DIR__ . "/../Funciones/Sesion.php";

$tituloPagina = "Logs del Sistema";

$usuario = $_SESSION["usuario"];

// Obtener imagen y rol admin
$stmt = $conn->prepare("SELECT imagen, admin FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($imagenActual, $esAdmin);
$stmt->fetch();
$stmt->close();

$imagenPerfil = $imagenActual 
    ? "../uploads/" . $imagenActual 
    : "../uploads/default.png";

// PAGINACIÓN
$porPagina = 10;
$pagina = isset($_GET["pagina"]) ? max(1, (int)$_GET["pagina"]) : 1;
$inicio = ($pagina - 1) * $porPagina;

// FILTROS
$buscar = $_GET["buscar"] ?? "";
$filtroFecha = $_GET["fecha"] ?? "";
$filtroUsuario = $_GET["usuario_filtro"] ?? "";

$where = "WHERE 1=1";

// Condicion para que solo el admin vea todos los logs
if ($esAdmin != 1) {
    $where .= " AND usuario = '" . $conn->real_escape_string($usuario) . "'";
}

// Filtro de búsqueda
if ($buscar !== "") {
    $b = $conn->real_escape_string("%$buscar%");
    $where .= " AND (usuario LIKE '$b' OR accion LIKE '$b')";
}

// Filtro de fecha
if ($filtroFecha !== "") {
    $f = $conn->real_escape_string($filtroFecha);
    $where .= " AND fecha LIKE '$f%'";
}

// Filtro de usuario (solo admins)
if ($esAdmin == 1 && $filtroUsuario !== "") {
    $fu = $conn->real_escape_string($filtroUsuario);
    $where .= " AND usuario = '$fu'";
}

// Total de registros
$total = $conn->query("SELECT COUNT(*) AS total FROM logs $where")->fetch_assoc()["total"];
$totalPaginas = ceil($total / $porPagina);

// Obtener logs
$query = "SELECT * FROM logs $where ORDER BY fecha DESC LIMIT $inicio, $porPagina";
$logs = $conn->query($query);

// Obtener lista de usuarios (solo admins)
$usuarios = $esAdmin == 1 ? $conn->query("SELECT DISTINCT usuario FROM logs ORDER BY usuario") : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?></title>

    <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
    <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>/logs-<?= $temaUsuario ?>.css">

    <link rel="icon" type="image/png" href="../img/iconogrande.png">
</head>
<body>

<?php include __DIR__ . "/menu.php"; ?>

<div class="main-content" id="main">

    <header class="header">
        <button id="menu-btn" class="menu-btn">☰</button>
        <h1><?= $tituloPagina ?></h1>
    </header>

    <main class="contenido">

        <div class="logs-wrapper">
            <h2 class="logs-title">Registro del Sistema</h2>

            <!-- FILTROS -->
            <form method="GET" class="filtros">
                <input 
                    type="text" 
                    name="buscar" 
                    placeholder="Buscar por usuario o acción..." 
                    value="<?= htmlspecialchars($buscar) ?>"
                >
                <input 
                    type="date" 
                    name="fecha" 
                    value="<?= htmlspecialchars($filtroFecha) ?>"
                    title="Filtrar por fecha"
                >

                <!-- Selector de usuario SOLO para admins -->
                <?php if ($esAdmin == 1): ?>
                <select name="usuario_filtro" title="Filtrar por usuario">
                    <option value="">Todos los usuarios</option>
                    <?php while ($u = $usuarios->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($u['usuario']) ?>" 
                            <?= $filtroUsuario == $u['usuario'] ? "selected" : "" ?>>
                            <?= htmlspecialchars($u['usuario']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php endif; ?>

                <button class="btn-pag" type="submit">Filtrar</button>
            </form>

            <!-- TABLA DE LOGS -->
            <table class="tabla-logs" role="table">
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs->num_rows > 0): ?>
                        <?php while ($row = $logs->fetch_assoc()): ?>
                        <?php
                            $accion = strtolower($row["accion"]);
                            $clase = "log-info";

                            if (str_contains($accion, "creó") || str_contains($accion, "crear")) {
                                $clase = "log-create";
                            } elseif (str_contains($accion, "eliminó") || str_contains($accion, "borrar") || str_contains($accion, "detuvo")) {
                                $clase = "log-delete";
                            } elseif (str_contains($accion, "editó") || str_contains($accion, "editar")) {
                                $clase = "log-edit";
                            } elseif (str_contains($accion, "inició sesión")) {
                                $clase = "log-login";
                            } elseif (str_contains($accion, "cerró sesión")) {
                                $clase = "log-logout";
                            } elseif (str_contains($accion, "reinició")) {
                                $clase = "log-restart";
                            } elseif (str_contains($accion, "inició")) {
                                $clase = "log-create";
                            }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row["fecha"]) ?></td>
                            <td><?= htmlspecialchars($row["usuario"]) ?></td>
                            <td class="<?= $clase ?>"><?= htmlspecialchars($row["accion"]) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #8b949e; padding: 20px;">
                                No hay registros disponibles
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- PAGINACIÓN -->
            <div class="paginacion">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=1&buscar=<?= urlencode($buscar) ?>&fecha=<?= urlencode($filtroFecha) ?>&usuario_filtro=<?= urlencode($filtroUsuario) ?>" 
                       class="btn-pag" title="Primera página">Primera</a>
                    <a href="?pagina=<?= $pagina - 1 ?>&buscar=<?= urlencode($buscar) ?>&fecha=<?= urlencode($filtroFecha) ?>&usuario_filtro=<?= urlencode($filtroUsuario) ?>" 
                       class="btn-pag" title="Página anterior">Anterior</a>
                <?php endif; ?>

                <span>Página <?= $pagina ?> de <?= $totalPaginas ?></span>

                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>&buscar=<?= urlencode($buscar) ?>&fecha=<?= urlencode($filtroFecha) ?>&usuario_filtro=<?= urlencode($filtroUsuario) ?>" 
                       class="btn-pag" title="Página siguiente">Siguiente</a>
                    <a href="?pagina=<?= $totalPaginas ?>&buscar=<?= urlencode($buscar) ?>&fecha=<?= urlencode($filtroFecha) ?>&usuario_filtro=<?= urlencode($filtroUsuario) ?>" 
                       class="btn-pag" title="Última página">Última</a>
                <?php endif; ?>
            </div>

        </div>

    </main>

    <footer class="footer">
        GameDock — Todos los derechos reservados © <?= date("Y") ?>
    </footer>

</div>

<script src="/TFG/JS/panel.js"></script>

</body>
</html>
