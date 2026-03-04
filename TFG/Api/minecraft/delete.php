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

$nombre = escapeshellcmd($input["nombre"]);
$usuario = $_SESSION["usuario"];

// 1. Parar contenedor
exec("docker stop $nombre 2>&1");

// 2. Eliminar contenedor
exec("docker rm $nombre 2>&1");

// 3. Eliminar volumen asociado
$volumen = "minecraft_$nombre"; // O mariadb_$nombre según ISO
exec("docker volume rm $volumen 2>&1");

// 4. Borrar de la BD
$stmt = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->close();

// 5. Log
registrarLog($conn, $usuario, "Eliminó servidor '$nombre' y su volumen");

// Respuesta
echo json_encode([
    "status" => "success",
    "message" => "Contenedor y volumen eliminados correctamente"
]);
