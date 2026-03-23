<?php
header("Content-Type: application/json");

$contenedor = $_GET["servidor"] ?? null;
$ruta = $_GET["ruta"] ?? null;

if (!$contenedor || !$ruta) {
    echo json_encode(["estado" => "error", "mensaje" => "Parámetros inválidos"]);
    exit;
}

$tmp = sys_get_temp_dir() . "/" . uniqid("read_");

$cmd = "docker cp " . escapeshellarg("$contenedor:$ruta") . " " . escapeshellarg($tmp);
exec($cmd, $out, $code);

if ($code !== 0 || !file_exists($tmp)) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo leer"]);
    exit;
}

echo json_encode([
    "estado" => "exito",
    "contenido" => file_get_contents($tmp)
]);

unlink($tmp);
