<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

$data = json_decode(file_get_contents("php://input"), true);

$nombre = $data["nombre"] ?? null;
$claim  = $data["claim"] ?? null;

if (!$nombre) {
    echo json_encode(["status" => "error", "message" => "Falta el nombre del servidor"]);
    exit;
}

if (!$claim) {
    echo json_encode(["status" => "error", "message" => "Falta el token PLEX_CLAIM"]);
    exit;
}

// Insertar en BD
$stmt = $conn->prepare("INSERT INTO contenedores (nombre, iso, version, puerto) VALUES (?, 'plex', '', 32400)");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->close();

// Crear contenedor Plex
$cmd = sprintf(
    'docker run -d --name plex_%s ' .
    '-p 32400:32400 ' .
    '-e TZ="Europe/Madrid" ' .
    '-e PLEX_CLAIM=%s ' .
    '-v plex_%s_config:/config ' .
    '-v plex_%s_transcode:/transcode ' .
    '-v plex_%s_media:/data ' .
    'plexinc/pms-docker 2>&1',
    escapeshellcmd($nombre),
    escapeshellarg($claim),
    escapeshellcmd($nombre),
    escapeshellcmd($nombre),
    escapeshellcmd($nombre)
);

exec($cmd, $out, $code);

if ($code !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al crear Plex",
        "docker_output" => $out
    ]);
    exit;
}

registrarLog($conn, $_SESSION["usuario"], "Creó un servidor Plex '{$nombre}'");

echo json_encode([
    "status" => "success",
    "message" => "Servidor Plex creado correctamente"
]);
