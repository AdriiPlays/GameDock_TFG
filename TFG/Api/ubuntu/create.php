<?php
session_start();
require_once "../../config.php";
require_once "../../Funciones/logs.php";


header("Content-Type: application/json");

// 1. Verificar autenticación
if (!isset($_SESSION["usuario"])) {
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado"
    ]);
    exit;
}

// 2. Leer JSON de entrada
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["nombre"]) || !isset($input["version"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Datos inválidos"
    ]);
    exit;
}

// 3. Sanitizar datos
$nombre  = escapeshellcmd($input["nombre"]);
$version = escapeshellcmd($input["version"]);
$usuario = $_SESSION["usuario"];

// 4. Construir imagen Ubuntu
$imagen = "ubuntu:$version";

// 5. Ejecutar Docker
$cmd = "docker run -d --name $nombre $imagen";
exec($cmd . " 2>&1", $out, $ret);

if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al crear contenedor",
        "docker_output" => $out
    ]);
    exit;
}

// 6. Guardar en BD
$stmt = $conn->prepare("
    INSERT INTO contenedores (nombre, iso, version, estado)
    VALUES (?, 'ubuntu', ?, 'online')
");
$stmt->bind_param("ss", $nombre, $version);
$stmt->execute();
$stmt->close();

// 7. Registrar log
registrarLog($conn, $usuario, "Creó el contenedor Ubuntu '$nombre'");

// 8. Respuesta JSON
echo json_encode([
    "status" => "success",
    "container" => [
        "name" => $nombre,
        "image" => $imagen,
        "state" => "online"
    ]
]);
