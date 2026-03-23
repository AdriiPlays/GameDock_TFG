<?php

header("Content-Type: application/json");
session_start();

$servidor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? null;

if (!$servidor) {
    echo json_encode(["estado" => "error", "mensaje" => "No se especificó el servidor"]);
    exit;
}

// Ruta base real de Unturned
if (!$ruta) {
    $ruta = "/home/steam/unturned/Servers/$servidor";
}

// Ejecutar ls dentro del contenedor
$comando = "docker exec " . escapeshellarg($servidor) . " ls -1p " . escapeshellarg($ruta);
exec($comando, $salida, $codigo);

if ($codigo !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo listar la carpeta"]);
    exit;
}

$archivos = [];

foreach ($salida as $nombre) {
    if ($nombre === "." || $nombre === "..") continue;

    $esCarpeta = str_ends_with($nombre, "/");

    // Construir ruta interna correcta
    $rutaCompleta = rtrim($ruta, "/") . "/" . rtrim($nombre, "/");

    $archivos[] = [
        "nombre" => rtrim($nombre, "/"),
        "ruta" => $rutaCompleta,
        "es_carpeta" => $esCarpeta,
        "tamano" => null
    ];
}

echo json_encode([
    "estado" => "exito",
    "archivos" => $archivos
]);
