<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../../config.php";

// Leer JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$nombre = $data["nombre"] ?? null;

if (!$nombre) {
    echo json_encode(["status" => "error", "message" => "Falta el nombre del contenedor"]);
    exit;
}

// OBTENER ID DEL CONTENEDOR
$stmt = $conn->prepare("SELECT id FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    echo json_encode(["status" => "error", "message" => "Contenedor no encontrado en la base de datos"]);
    exit;
}

$id = $res["id"];

// ELIMINAR CONTENEDOR DOCKER
$out = [];
$ret = 0;

exec("docker rm -f " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);
$dockerDeleteInfo = $out;

// ELIMINAR VOLUMEN python_nombre
$outVol = [];
$retVol = 0;

$volName = "python_" . $nombre;

exec("docker volume rm " . escapeshellcmd($volName) . " 2>&1", $outVol, $retVol);

// ELIMINAR BD: python
$stmt2 = $conn->prepare("DELETE FROM python WHERE id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$stmt2->close();

// ELIMINAR BD: contenedores
$stmt3 = $conn->prepare("DELETE FROM contenedores WHERE id = ?");
$stmt3->bind_param("i", $id);
$stmt3->execute();
$stmt3->close();

// RESPUESTA JSON
echo json_encode([
    "status" => "success",
    "message" => "Contenedor Python eliminado correctamente",
    "docker_output" => $dockerDeleteInfo,
    "volume_output" => $outVol
]);
