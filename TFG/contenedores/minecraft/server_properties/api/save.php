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

// Crear archivo temporal
$tmp = tempnam(sys_get_temp_dir(), "srv_");
file_put_contents($tmp, $contenido);

// Copiar al contenedor
exec("docker cp $tmp " . escapeshellcmd($nombre) . ":/data/server.properties 2>&1", $out, $ret);

// Borrar temporal
unlink($tmp);

if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo guardar server.properties",
        "docker_output" => $out
    ]);
    exit;
}

// FORZAR PERMISOS 
exec("docker run --rm -v mc_" . escapeshellcmd($nombre) . ":/data alpine sh -c \"chmod 666 /data/server.properties\"");

// Respuesta final
echo json_encode([
    "status" => "success",
    "message" => "server.properties guardado correctamente"
]);
