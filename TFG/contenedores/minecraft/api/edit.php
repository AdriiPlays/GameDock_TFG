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

$id            = $data["id"] ?? null;
$nombreActual  = $data["nombreActual"] ?? null;
$nuevoNombre   = $data["nuevoNombre"] ?? null;
$nuevaVersion  = $data["nuevaVersion"] ?? null;
$nuevoTipo     = $data["nuevoTipo"] ?? null;
$nuevoPuerto   = $data["nuevoPuerto"] ?? null;

if (!$id || !$nombreActual || !$nuevoNombre || !$nuevaVersion || !$nuevoTipo || !$nuevoPuerto) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

// -----------------------------
// 1. ACTUALIZAR TABLA contenedores
// -----------------------------
$stmt = $conn->prepare("
    UPDATE contenedores
    SET nombre = ?, version = ?, puerto = ?
    WHERE id = ?
");
$stmt->bind_param("ssii", $nuevoNombre, $nuevaVersion, $nuevoPuerto, $id);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al actualizar contenedores"]);
    exit;
}
$stmt->close();

// -----------------------------
// 2. ACTUALIZAR TABLA minecraft
// -----------------------------
$stmt2 = $conn->prepare("
    UPDATE minecraft
    SET nombre = ?, version = ?, tipo = ?, puerto = ?
    WHERE id = ?
");
$stmt2->bind_param("sssii", $nuevoNombre, $nuevaVersion, $nuevoTipo, $nuevoPuerto, $id);

if (!$stmt2->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al actualizar tabla minecraft"]);
    exit;
}
$stmt2->close();

// -----------------------------
// 3. RENOMBRAR CONTENEDOR EN DOCKER (si cambia el nombre)
// -----------------------------
if ($nombreActual !== $nuevoNombre) {

    $cmdRename = sprintf(
        'docker rename %s %s 2>&1',
        escapeshellcmd($nombreActual),
        escapeshellcmd($nuevoNombre)
    );

    $outRename = [];
    $retRename = 0;
    exec($cmdRename, $outRename, $retRename);

    if ($retRename !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo renombrar el contenedor en Docker",
            "docker_output" => $outRename,
            "cmd" => $cmdRename
        ]);
        exit;
    }
}

// -----------------------------
// 4. RESPUESTA FINAL
// -----------------------------
echo json_encode([
    "status" => "success",
    "message" => "Servidor Minecraft actualizado correctamente"
]);
