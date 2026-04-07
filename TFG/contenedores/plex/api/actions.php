<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

$data = json_decode(file_get_contents("php://input"), true);

$tipo   = $data["tipo"] ?? null;
$nombre = $data["nombre"] ?? null;

if (!$tipo || !$nombre) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

$container = "plex_" . $nombre;
$out = [];
$ret = 0;

switch ($tipo) {

    case "start":
        exec("docker start $container 2>&1", $out, $ret);
        break;

    case "stop":
        exec("docker stop $container 2>&1", $out, $ret);
        break;

    case "restart":
        exec("docker restart $container 2>&1", $out, $ret);
        break;

    case "delete":
        exec("docker rm -f $container 2>&1", $out, $ret);

        // Eliminar volúmenes
        exec("docker volume rm plex_{$nombre}_config 2>&1");
        exec("docker volume rm plex_{$nombre}_transcode 2>&1");
        exec("docker volume rm plex_{$nombre}_media 2>&1");

        // Eliminar BD
        $stmt = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->close();

        registrarLog($conn, $_SESSION["usuario"], "Eliminó Plex '{$nombre}'");

        echo json_encode(["status" => "success", "message" => "Servidor Plex eliminado"]);
        exit;

    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
        exit;
}

if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Error ejecutando acción",
        "docker_output" => $out
    ]);
    exit;
}

registrarLog($conn, $_SESSION["usuario"], ucfirst($tipo) . " Plex '{$nombre}'");

echo json_encode(["status" => "success", "message" => "Acción ejecutada"]);
