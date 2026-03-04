<?php
session_start();
require_once "../../config.php";
require_once "../../Funciones/logs.php";

header("Content-Type: application/json");

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || empty($input["nombre"])) {
    echo json_encode(["status" => "error", "message" => "Nombre inválido"]);
    exit;
}

$version = strtoupper($input["version"] ?? "LATEST");


$nombre  = escapeshellcmd($input["nombre"]);
$version = strtoupper(escapeshellcmd($input["version"]));
$usuario = $_SESSION["usuario"];

// Imagen de Minecraft
$imagen = "itzg/minecraft-server";

// Puerto del servidor Minecraft
$puerto = 25500 + rand(1, 500);

// Comando Docker
$cmd = "docker run -d --name $nombre -e EULA=TRUE -e VERSION=$version -p $puerto:25565 -v minecraft_$nombre:/data itzg/minecraft-server";
exec($cmd . " 2>&1", $out, $ret);


if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al crear contenedor",
        "docker_output" => $out,
        "cmd" => $cmd
    ]);
    exit;
}

// Guardar en BD
$stmt = $conn->prepare("
    INSERT INTO contenedores (nombre, iso, version, puerto, estado)
    VALUES (?, 'minecraft', ?, ?, 'online')
");
$stmt->bind_param("ssi", $nombre, $version, $puerto);
$stmt->execute();
$stmt->close();

registrarLog($conn, $usuario, "Creó servidor Minecraft '$nombre'");

echo json_encode([
    "status" => "success",
    "container" => [
        "name" => $nombre,
        "image" => $imagen,
        "mc_port" => $puerto,
        "state" => "online"
    ]
]);
