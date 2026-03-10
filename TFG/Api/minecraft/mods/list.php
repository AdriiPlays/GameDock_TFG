<?php
session_start();
require_once "../../../config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

if (!isset($_GET["nombre"])) {
    echo json_encode(["status" => "error", "message" => "Nombre no especificado"]);
    exit;
}

$nombre = escapeshellcmd($_GET["nombre"]);

$mods = [];
exec("docker exec $nombre ls /data/mods 2>&1", $mods, $ret);

if ($ret !== 0) {
    echo json_encode(["status" => "error", "message" => "No se pudieron leer los mods"]);
    exit;
}

echo json_encode([
    "status" => "success",
    "mods" => $mods
]);
