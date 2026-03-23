<?php
header("Content-Type: application/json");

$contenedor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? null;

if (!$contenedor || !$ruta) {
    echo json_encode(["estado" => "error", "mensaje" => "Parámetros inválidos"]);
    exit;
}

$cmd = "docker exec " . escapeshellarg($contenedor) . " rm -rf " . escapeshellarg($ruta);
exec($cmd, $out, $code);

echo json_encode([
    "estado" => $code === 0 ? "exito" : "error",
    "mensaje" => $code === 0 ? "Eliminado" : "No se pudo eliminar"
]);
