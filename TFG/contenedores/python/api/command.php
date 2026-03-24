<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$nombre = $data["nombre"] ?? null;
$cmd    = $data["cmd"] ?? null;

if (!$nombre || !$cmd) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

// Comandos prohibidos por seguridad
$prohibidos = ["rm ", "docker", "shutdown", "reboot", "poweroff", "mkfs", "kill -9"];

foreach ($prohibidos as $p) {
    if (stripos($cmd, $p) !== false) {
        echo json_encode(["status" => "error", "message" => "Comando no permitido"]);
        exit;
    }
}

// Ejecutar comando dentro del contenedor Python
$fullCmd = "docker exec " . escapeshellcmd($nombre) . " bash -c " . escapeshellarg($cmd);

exec($fullCmd . " 2>&1", $out, $ret);

// Registrar en logs
registrarLog($conn, $_SESSION["usuario"], "Ejecutó comando en '{$nombre}': $cmd");

echo json_encode([
    "status" => $ret === 0 ? "success" : "error",
    "output" => implode("\n", $out)
]);
