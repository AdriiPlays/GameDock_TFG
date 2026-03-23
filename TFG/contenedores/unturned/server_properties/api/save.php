<?php
require_once __DIR__ . "/../../../../config.php";

header("Content-Type: application/json");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$nombre = $data["nombre"] ?? null;
$contenido = $data["contenido"] ?? null;

if (!$nombre || $contenido === null) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

/*
    Ruta real del archivo Commands.dat dentro del contenedor Unturned:

    /home/steam/unturned/Servers/<ServerName>/Server/Commands.dat
*/

$ruta = "/home/steam/unturned/Servers/$nombre/Server/Commands.dat";

// Crear archivo temporal
$tmp = tempnam(sys_get_temp_dir(), "unt_");
file_put_contents($tmp, $contenido);

// Copiar al contenedor
$out = [];
$ret = 0;

exec(
    "docker cp " . escapeshellarg($tmp) . " " .
    escapeshellcmd($nombre) . ":" . escapeshellarg($ruta) . " 2>&1",
    $out,
    $ret
);

// Borrar archivo temporal
unlink($tmp);

if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo guardar Commands.dat",
        "docker_output" => $out
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Commands.dat guardado correctamente"
]);
