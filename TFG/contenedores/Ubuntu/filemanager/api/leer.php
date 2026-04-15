<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

$servidor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? null;

if (!$servidor || !$ruta) {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan parámetros"]);
    exit;
}

// Normalizar ruta
if (!str_starts_with($ruta, "/")) {
    $ruta = "/" . $ruta;
}

// Forzar ruta base correcta para Ubuntu SSH
if (!str_starts_with($ruta, "/data")) {
    $ruta = "/data" . $ruta;
}

// Archivo temporal
$tmp = sys_get_temp_dir() . "/" . uniqid("edit_") . "_" . basename($ruta);

// Extraer archivo del contenedor
$comando = "docker cp " . escapeshellarg($servidor . ":" . $ruta) . " " . escapeshellarg($tmp);
exec($comando, $salida, $codigo);

if ($codigo !== 0 || !file_exists($tmp)) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo leer el archivo"]);
    exit;
}

$contenido = file_get_contents($tmp);
unlink($tmp);

echo json_encode([
    "estado" => "exito",
    "contenido" => $contenido
]);
