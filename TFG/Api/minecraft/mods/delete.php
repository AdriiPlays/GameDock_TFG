<?php
session_start();
require_once "../../../config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

if (!isset($_POST["nombre"]) || !isset($_POST["mod"])) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

$nombre = escapeshellcmd($_POST["nombre"]);
$mod = basename($_POST["mod"]); // seguridad

exec("docker exec $nombre rm /data/mods/$mod 2>&1", $out, $ret);

if ($ret !== 0) {
    echo json_encode(["status" => "error", "message" => "No se pudo eliminar el mod", "docker" => $out]);
    exit;
}

echo json_encode(["status" => "success", "message" => "Mod eliminado"]);
