<?php
header("Content-Type: application/json");
require_once "../../config.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$nombreActual = $data["nombreActual"] ?? null;
$nuevoNombre  = $data["nuevoNombre"] ?? null;
$nuevaVersion = $data["nuevaVersion"] ?? null;
$nuevoPuerto  = $data["nuevoPuerto"] ?? null;
$tipo         = $data["tipo"] ?? null;

if (!$nombreActual || !$nuevoNombre) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

// Actualizar BD
$stmt = $conn->prepare("
    UPDATE contenedores 
    SET nombre = ?, version = ?, puerto = ?, tipo = ?
    WHERE nombre = ?
");
$stmt->bind_param("sssss",
    $nuevoNombre,
    $nuevaVersion,
    $nuevoPuerto,
    $tipo,
    $nombreActual
);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al actualizar BD"]);
    exit;
}
$stmt->close();

// Renombrar contenedor en Docker
exec('docker rename ' . escapeshellcmd($nombreActual) . ' ' . escapeshellcmd($nuevoNombre) . ' 2>&1', $outRename, $retRename);

if ($retRename !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo renombrar el contenedor en Docker:\n" . implode("\n", $outRename)
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Cambios guardados correctamente"
]);
