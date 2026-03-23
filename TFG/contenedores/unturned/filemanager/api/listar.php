<?php
header("Content-Type: application/json");

$contenedor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? null;

if (!$contenedor || !$ruta) {
    echo json_encode(["estado" => "error", "mensaje" => "Parámetros inválidos"]);
    exit;
}

$cmd = "docker exec " . escapeshellarg($contenedor) . " ls -1p " . escapeshellarg($ruta);
exec($cmd, $salida, $code);

if ($code !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo listar"]);
    exit;
}

$archivos = [];

foreach ($salida as $item) {
    $item = trim($item);
    $esCarpeta = str_ends_with($item, "/");
    $rutaCompleta = rtrim($ruta, "/") . "/" . rtrim($item, "/");

    $archivos[] = [
        "nombre" => rtrim($item, "/"),
        "ruta" => $rutaCompleta,
        "es_carpeta" => $esCarpeta
    ];
}

echo json_encode(["estado" => "exito", "archivos" => $archivos]);
