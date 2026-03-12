<?php

header("Content-Type: application/json");
session_start();

$servidor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? "/data";

if (!$servidor) {
    echo json_encode(["estado" => "error", "mensaje" => "No se especificó el servidor"]);
    exit;
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

    $archivos[] = [
        "nombre" => rtrim($nombre, "/"),
        "ruta" => "/data/" . trim(str_replace("/data", "", $ruta), "/") . "/" . rtrim($nombre, "/"),
        "es_carpeta" => $esCarpeta,
        "tamano" => null
    ];
}


echo json_encode([
    "estado" => "exito",
    "archivos" => $archivos
]);
