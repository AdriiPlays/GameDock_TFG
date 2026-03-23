<?php
header("Content-Type: application/json");

$contenedor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? null;
$nombre = $_POST["nombre"] ?? null;

if (!$contenedor || !$ruta || !$nombre) {
    echo json_encode(["estado" => "error", "mensaje" => "Parámetros inválidos"]);
    exit;
}

$nuevaRuta = rtrim($ruta, "/") . "/" . $nombre;

$cmd = "docker exec " . escapeshellarg($contenedor) . " mkdir -p " . escapeshellarg($nuevaRuta);
exec($cmd, $out, $code);

echo json_encode([
    "estado" => $code === 0 ? "exito" : "error",
    "mensaje" => $code === 0 ? "Carpeta creada" : "No se pudo crear"
]);
