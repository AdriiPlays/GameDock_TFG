<?php
session_start();
require_once "../../config.php";
require_once "../../Funciones/logs.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// 1. Verificar autenticación
if (!isset($_SESSION["usuario"])) {
    echo json_encode([
        "status" => "error",
        "message" => "No autorizado"
    ]);
    exit;
}

// 2. Leer JSON
$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["nombre"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Nombre del contenedor requerido"
    ]);
    exit;
}

$nombre = escapeshellcmd($input["nombre"]);
$usuario = $_SESSION["usuario"];

// 3. Parar contenedor (por si está en ejecución)
exec("docker stop $nombre 2>&1", $outStop, $retStop);

// 4. Eliminar contenedor
exec("docker rm $nombre 2>&1", $outRm, $retRm);

if ($retRm !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo eliminar el contenedor",
        "docker_output" => $outRm
    ]);
    exit;
}

// 5. Eliminar de la BD
$stmt = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->close();

// 6. Registrar log
registrarLog($conn, $usuario, "Eliminó el contenedor '$nombre'");

// 7. Respuesta JSON
echo json_encode([
    "status" => "success",
    "message" => "Contenedor eliminado correctamente",
    "container" => $nombre
]);
