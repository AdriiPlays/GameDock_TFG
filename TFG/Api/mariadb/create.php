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

if (!$input || !isset($input["nombre"]) || !isset($input["version"]) || !isset($input["password"])) {
    echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
    exit;
}

$nombre   = escapeshellcmd($input["nombre"]);
$version  = escapeshellcmd($input["version"]);
$password = escapeshellcmd($input["password"]);
$usuario  = $_SESSION["usuario"];

$imagen = "mariadb:$version";

$cmd = "docker run -d --name $nombre -e MARIADB_ROOT_PASSWORD=$password -p 3307:3306 $imagen";
exec($cmd . " 2>&1", $out, $ret);

if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al crear contenedor",
        "docker_output" => $out
    ]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO contenedores (nombre, iso, version, estado)
    VALUES (?, 'mariadb', ?, 'online')
");
$stmt->bind_param("ss", $nombre, $version);
$stmt->execute();
$stmt->close();

registrarLog($conn, $usuario, "Creó el contenedor MariaDB '$nombre'");

echo json_encode([
    "status" => "success",
    "container" => [
        "name" => $nombre,
        "image" => $imagen,
        "state" => "online"
    ]
]);
