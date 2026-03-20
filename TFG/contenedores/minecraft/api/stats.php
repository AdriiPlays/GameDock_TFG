<?php
header("Content-Type: application/json");

if (!isset($_GET["nombre"])) {
    echo json_encode(["status" => "error", "message" => "Falta nombre"]);
    exit;
}

$nombre = escapeshellcmd($_GET["nombre"]);

$out = [];
exec("docker stats $nombre --no-stream --format \"{{.MemUsage}}\"", $out);

if (empty($out)) {
    echo json_encode(["status" => "error", "message" => "No se pudo obtener stats"]);
    exit;
}

// Ejemplo: "512MiB / 2GiB"
list($used, $total) = explode(" / ", $out[0]);

echo json_encode([
    "status" => "success",
    "used" => $used,
    "total" => $total
]);
