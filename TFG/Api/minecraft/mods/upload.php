<?php
session_start();
require_once "../../../config.php";

header("Content-Type: application/json");

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

if (!isset($_FILES["mod"]) || !isset($_POST["nombre"])) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

$nombre = escapeshellcmd($_POST["nombre"]);
$archivo = $_FILES["mod"]["tmp_name"];
$nombreArchivo = basename($_FILES["mod"]["name"]);

if (pathinfo($nombreArchivo, PATHINFO_EXTENSION) !== "jar") {
    echo json_encode(["status" => "error", "message" => "Solo se permiten archivos .jar"]);
    exit;
}

// Copiar mod dentro del contenedor
exec("docker cp $archivo $nombre:/data/mods/$nombreArchivo 2>&1", $out, $ret);

if ($ret !== 0) {
    echo json_encode(["status" => "error", "message" => "Error al copiar mod", "docker" => $out]);
    exit;
}

// Reiniciar contenedor para cargar mods
exec("docker restart $nombre");

echo json_encode(["status" => "success", "message" => "Mod subido y servidor reiniciado"]);
