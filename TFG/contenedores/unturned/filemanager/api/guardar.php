<?php
header("Content-Type: application/json");

$contenedor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? null;
$contenido = $_POST["contenido"] ?? null;

if (!$contenedor || !$ruta) {
    echo json_encode(["estado" => "error", "mensaje" => "Parámetros inválidos"]);
    exit;
}

$tmp = sys_get_temp_dir() . "/" . uniqid("save_");
file_put_contents($tmp, $contenido);

$cmd = "docker cp " . escapeshellarg($tmp) . " " . escapeshellarg("$contenedor:$ruta");
exec($cmd, $out, $code);

unlink($tmp);

echo json_encode([
    "estado" => $code === 0 ? "exito" : "error",
    "mensaje" => $code === 0 ? "Guardado" : "No se pudo guardar"
]);
