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

if (!$input || !isset($input["nombre"])) {
    echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
    exit;
}

$nombre = escapeshellcmd($input["nombre"]);
$usuario = $_SESSION["usuario"];

exec("docker rm -f $nombre 2>&1", $out, $ret);

if ($ret !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo eliminar el contenedor",
        "docker_output" => $out
    ]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->close();

registrarLog($conn, $usuario, "Eliminó el contenedor MariaDB '$nombre'");

echo json_encode(["status" => "success"]);
