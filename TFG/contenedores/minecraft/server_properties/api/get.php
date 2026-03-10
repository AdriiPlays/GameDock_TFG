<?php
require_once __DIR__ . "/../../../../config.php";
header("Content-Type: application/json");

$nombre = $_GET["nombre"] ?? null;

if (!$nombre) {
    echo json_encode(["status" => "error", "message" => "Falta el nombre"]);
    exit;
}

$out = [];
$ret = 0;

exec('docker exec ' . escapeshellcmd($nombre) . ' cat /data/server.properties 2>&1', $out, $ret);

if ($ret !== 0) {
    echo json_encode(["status" => "error", "message" => "No se pudo leer server.properties"]);
    exit;
}

echo json_encode([
    "status" => "success",
    "lines" => $out
]);
