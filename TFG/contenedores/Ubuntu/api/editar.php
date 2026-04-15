<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

$data = json_decode(file_get_contents("php://input"), true);

$nombreActual = $data["nombreActual"] ?? null;
$nuevoNombre  = $data["nuevoNombre"] ?? null;

if (!$nombreActual || !$nuevoNombre) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

// Verificar si existe en BD
$stmt = $conn->prepare("SELECT * FROM contenedores WHERE nombre = ?");
$stmt->bind_param("s", $nombreActual);
$stmt->execute();
$cont = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cont) {
    echo json_encode(["status" => "error", "message" => "Servidor no encontrado"]);
    exit;
}

// Renombrar contenedor Docker
$cmd = sprintf(
    "docker rename ubuntu_%s ubuntu_%s 2>&1",
    escapeshellcmd($nombreActual),
    escapeshellcmd($nuevoNombre)
);

exec($cmd, $out, $code);

if ($code !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo renombrar el contenedor",
        "docker_output" => $out
    ]);
    exit;
}

// Actualizar BD
$stmt = $conn->prepare("UPDATE contenedores SET nombre = ? WHERE nombre = ?");
$stmt->bind_param("ss", $nuevoNombre, $nombreActual);
$stmt->execute();
$stmt->close();

// Registrar log
registrarLog($conn, $_SESSION["usuario"], "Renombró servidor Ubuntu SSH '{$nombreActual}' a '{$nuevoNombre}'");

echo json_encode([
    "status" => "success",
    "message" => "Servidor renombrado correctamente"
]);
