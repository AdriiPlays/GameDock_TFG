<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

$data = json_decode(file_get_contents("php://input"), true);

$nombre   = $data["nombre"]   ?? null;
$password = $data["password"] ?? null;
$puerto   = $data["puerto"]   ?? null;

if (!$nombre || !$password || !$puerto) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

$nombreContenedor = "ubuntu_" . $nombre;

// Comando Docker en UNA sola línea (Windows compatible)
$cmd = 'docker run -d --name ' . escapeshellarg($nombreContenedor) .
       ' -p ' . intval($puerto) . ':22 ' .
       'ubuntu:22.04 bash -c "apt update && apt install -y openssh-server && mkdir -p /run/sshd && echo root:' . $password .
       ' | chpasswd && sed -i \'s/#PermitRootLogin prohibit-password/PermitRootLogin yes/\' /etc/ssh/sshd_config && sed -i \'s/PermitRootLogin no/PermitRootLogin yes/\' /etc/ssh/sshd_config && /usr/sbin/sshd -D"';

exec($cmd . " 2>&1", $out, $code);

if ($code !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al crear Ubuntu SSH",
        "docker_output" => $out
    ]);
    exit;
}

// Guardar en BD
$stmt = $conn->prepare("INSERT INTO contenedores (nombre, iso, version, puerto)
VALUES (?, 'ubuntu', '', ?)");
$stmt->bind_param("si", $nombre, $puerto);
$stmt->execute();
$stmt->close();

registrarLog($conn, $_SESSION["usuario"], "Creó un servidor Ubuntu SSH '{$nombre}'");

echo json_encode([
    "status" => "success",
    "message" => "Servidor Ubuntu SSH creado correctamente"
]);
