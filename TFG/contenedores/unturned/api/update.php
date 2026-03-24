<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

$data = json_decode(file_get_contents("php://input"), true);
$nombre = $data["nombre"] ?? null;

if (!$nombre) {
    echo json_encode(["status" => "error", "message" => "Falta el nombre del servidor"]);
    exit;
}

// Obtener puerto
$stmt = $conn->prepare("SELECT puerto FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->bind_result($puerto);
$stmt->fetch();
$stmt->close();

if (!$puerto) {
    echo json_encode(["status" => "error", "message" => "Servidor no encontrado"]);
    exit;
}

// Parar y borrar
exec("docker stop " . escapeshellarg($nombre));
exec("docker rm " . escapeshellarg($nombre));

// Recrear contenedor
$cmd = sprintf(
    'docker run -d --name %s ' .
    '-p %d:27015/udp -p %d:27015/tcp ' .
    '-v unturned_%s:/home/steam/unturned ' .
    'admuro/unturned:latest 2>&1',
    escapeshellcmd($nombre),
    $puerto,
    $puerto,
    escapeshellcmd($nombre)
);

exec($cmd, $out, $code);

if ($code !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al actualizar el servidor",
        "docker_output" => $out
    ]);
    exit;
}

registrarLog($conn, $_SESSION["usuario"], "Actualizó el servidor Unturned '{$nombre}'");

echo json_encode([
    "status" => "success",
    "message" => "Servidor actualizado correctamente"
]);
