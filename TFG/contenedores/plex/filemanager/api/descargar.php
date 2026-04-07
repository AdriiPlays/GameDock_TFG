<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    die("No autorizado");
}

$servidor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? null;

if (!$servidor || !$ruta) {
    die("Faltan parámetros");
}

// Normalizar ruta
if (!str_starts_with($ruta, "/")) {
    $ruta = "/" . $ruta;
}

// Forzar ruta base correcta para Plex
if (!str_starts_with($ruta, "/data")) {
    $ruta = "/data" . $ruta;
}

// Nombre del archivo
$nombreArchivo = basename($ruta);

// Archivo temporal
$tmp = sys_get_temp_dir() . "/" . uniqid("dl_") . "_" . $nombreArchivo;

// Copiar desde el contenedor al host
$comando = "docker cp " . escapeshellarg($servidor . ":" . $ruta) . " " . escapeshellarg($tmp);
exec($comando, $salida, $codigo);

if ($codigo !== 0 || !file_exists($tmp)) {
    die("Error al extraer el archivo del contenedor");
}

// Enviar archivo al navegador
header("Content-Disposition: attachment; filename=\"$nombreArchivo\"");
header("Content-Type: application/octet-stream");
header("Content-Length: " . filesize($tmp));

readfile($tmp);

// Borrar archivo temporal
unlink($tmp);
exit;
