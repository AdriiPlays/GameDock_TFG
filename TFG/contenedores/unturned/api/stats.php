<?php
header("Content-Type: application/json");

if (!isset($_GET["nombre"])) {
    echo json_encode(["status" => "error", "message" => "Falta nombre"]);
    exit;
}

$nombre = escapeshellcmd($_GET["nombre"]);

// Obtener estadísticas de Docker
$out = [];
exec("docker stats $nombre --no-stream --format \"{{.MemUsage}}\"", $out);

if (empty($out)) {
    echo json_encode(["status" => "error", "message" => "No se pudo obtener stats"]);
    exit;
}

/*
    Ejemplo de salida:
    "512MiB / 2GiB"
*/
if (!str_contains($out[0], "/")) {
    echo json_encode(["status" => "error", "message" => "Formato inválido en stats"]);
    exit;
}

list($used, $total) = explode(" / ", $out[0]);

echo json_encode([
    "status" => "success",
    "used" => trim($used),
    "total" => trim($total)
]);
