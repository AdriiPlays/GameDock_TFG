<?php
ini_set("session.cookie_lifetime", 0); // borrar al cerrar el navegador
ini_set("session.gc_maxlifetime", 0);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config.php";


$inactividadMax = 900; // 15 minutos

if (isset($_SESSION["ultimo_movimiento"])) {
    if (time() - $_SESSION["ultimo_movimiento"] > $inactividadMax) {
        session_unset();
        session_destroy();
        header("Location: /TFG/Index.php?timeout=1");
        exit;
    }
}

$_SESSION["ultimo_movimiento"] = time();


if (!isset($_SESSION["usuario"])) {
    header("Location: /TFG/Index.php");
    exit;
}

$usuario = $_SESSION["usuario"];
$stmt = $conn->prepare("SELECT correo, imagen FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($correoActual, $imagenActual);
$stmt->fetch();
$stmt->close();

$imagenPerfil = $imagenActual
    ? "/TFG/uploads/" . $imagenActual
    : "/TFG/uploads/default.png";

$temaUsuario = $_SESSION["tema"] ?? "light";
