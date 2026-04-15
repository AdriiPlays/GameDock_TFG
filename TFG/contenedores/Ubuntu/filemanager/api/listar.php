<?php

header("Content-Type: application/json");
session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

$servidor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? "/data"; // Ruta base correcta para Ubuntu SSH

if (!$servidor) {
    echo json_encode(["estado" => "error", "mensaje" => "No se especificó el contenedor"]);
    exit;
}

// Normalizar ruta
if (!str_starts_with($ruta, "/")) {
    $ruta = "/" . $ruta;
}

if (!str_starts_with($ruta, "/data")) {
    $ruta = "/data" . $ruta;
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

    // Construir ruta completa dentro del contenedor
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
