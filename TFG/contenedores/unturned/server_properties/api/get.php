<?php
require_once __DIR__ . "/../../../../config.php";
header("Content-Type: application/json");

$nombre = $_GET["nombre"] ?? null;

if (!$nombre) {
    echo json_encode(["status" => "error", "message" => "Falta el nombre"]);
    exit;
}



$ruta = "/home/steam/unturned/Servers/$nombre/Server/Commands.dat";

$out = [];
$ret = 0;

// Leer Commands.dat desde dentro del contenedor
exec('docker exec ' . escapeshellcmd($nombre) . ' cat ' . escapeshellarg($ruta) . ' 2>&1', $out, $ret);

if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo leer Commands.dat",
        "docker_output" => $out
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "content" => implode("\n", $out)
]);
