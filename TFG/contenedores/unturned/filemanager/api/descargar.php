<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    die("No autorizado");
}

$contenedor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? null;

if (!$contenedor || !$ruta) {
    die("Faltan parámetros");
}

// Nombre del archivo
$nombreArchivo = basename($ruta);

// Archivo temporal
$tmp = sys_get_temp_dir() . "/" . uniqid("dl_") . "_" . $nombreArchivo;

// Copiar desde el contenedor al host
$cmd = "docker cp " . escapeshellarg("$contenedor:$ruta") . " " . escapeshellarg($tmp);
exec($cmd, $out, $code);

if ($code !== 0 || !file_exists($tmp)) {
    die("Error al extraer el archivo del contenedor");
}

// Enviar archivo al navegador
header("Content-Disposition: attachment; filename=\"" . $nombreArchivo . "\"");
header("Content-Type: application/octet-stream");
header("Content-Length: " . filesize($tmp));

readfile($tmp);

// Borrar archivo temporal
unlink($tmp);
exit;
