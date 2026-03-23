<?php
header("Content-Type: application/json");

$contenedor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? null;

if (!$contenedor || !$ruta || !isset($_FILES["archivo"])) {
    echo json_encode(["estado" => "error", "mensaje" => "Parámetros inválidos"]);
    exit;
}

$tmp = $_FILES["archivo"]["tmp_name"];
$destino = rtrim($ruta, "/") . "/" . basename($_FILES["archivo"]["name"]);

$cmd = "docker cp " . escapeshellarg($tmp) . " " . escapeshellarg("$contenedor:$destino");
exec($cmd, $out, $code);

echo json_encode([
    "estado" => $code === 0 ? "exito" : "error",
    "mensaje" => $code === 0 ? "Archivo subido" : "No se pudo subir"
]);
