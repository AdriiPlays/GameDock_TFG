<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$nombre = $_GET["nombre"] ?? null;

if (!$nombre) {
    echo json_encode(["status" => "error", "message" => "No se especificó el contenedor"]);
    exit;
}

$out = [];
$ret = 0;

exec("docker logs --tail 200 " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

echo json_encode([
    "status" => "success",
    "logs" => implode("\n", $out)
]);
