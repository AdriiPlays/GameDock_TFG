<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

$servidor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? null;
$nombre = $_POST["nombre"] ?? null;

if (!$servidor || !$ruta || !$nombre) {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan parámetros"]);
    exit;
}

// Normalizar ruta
if (!str_starts_with($ruta, "/")) {
    $ruta = "/" . $ruta;
}

if (!str_starts_with($ruta, "/data")) {
    $ruta = "/data" . $ruta;
}

// Ruta final donde se creará la carpeta
$rutaNueva = rtrim($ruta, "/") . "/" . $nombre;

// Crear carpeta dentro del contenedor Ubuntu SSH
$comando = "docker exec " . escapeshellarg($servidor) . " mkdir -p " . escapeshellarg($rutaNueva);
exec($comando, $salida, $codigo);

if ($codigo !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo crear la carpeta"]);
    exit;
}

echo json_encode(["estado" => "exito", "mensaje" => "Carpeta creada correctamente"]);
