<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$id            = $data["id"];
$nombreActual  = $data["nombreActual"];
$nuevoNombre   = $data["nuevoNombre"];
$nuevaVersion  = $data["nuevaVersion"];
$nuevoTipo     = $data["nuevoTipo"];
$nuevoPuerto   = $data["nuevoPuerto"];
$puertoActual  = $data["puertoActual"];
$nuevaRAM      = $data["nuevaRAM"] ?? null;

// ===============================
// OBTENER RAM ACTUAL DE LA BD
// ===============================
$stmtRAM = $conn->prepare("SELECT ram FROM minecraft WHERE id = ?");
$stmtRAM->bind_param("i", $id);
$stmtRAM->execute();
$resRAM = $stmtRAM->get_result()->fetch_assoc();
$stmtRAM->close();

$ramActual = intval($resRAM["ram"]);

// ===============================
// CONVERTIR RAM A FORMATO ITZG
// ===============================
$ramG = null;
if ($nuevaRAM !== null) {
    $ramG = round($nuevaRAM / 1024, 2) . "G";
}

// ===============================
// 1. ACTUALIZAR TABLA contenedores
// ===============================
$stmt = $conn->prepare("
    UPDATE contenedores
    SET nombre = ?, version = ?, puerto = ?
    WHERE id = ?
");
$stmt->bind_param("ssii", $nuevoNombre, $nuevaVersion, $nuevoPuerto, $id);
$stmt->execute();
$stmt->close();

// ===============================
// 2. ACTUALIZAR TABLA minecraft
// ===============================
$stmt2 = $conn->prepare("
    UPDATE minecraft
    SET nombre = ?, version = ?, tipo = ?, puerto = ?, ram = ?
    WHERE id = ?
");
$stmt2->bind_param("sssisi", $nuevoNombre, $nuevaVersion, $nuevoTipo, $nuevoPuerto, $nuevaRAM, $id);
$stmt2->execute();
$stmt2->close();

// ===============================
// 3. RENOMBRAR CONTENEDOR SI CAMBIA NOMBRE
// ===============================
if ($nombreActual !== $nuevoNombre) {
    exec("docker rename $nombreActual $nuevoNombre");
}

// ===============================
// 4. DETERMINAR SI HAY QUE RECREAR
// ===============================
$requiereRecrear = false;

if ($nuevoPuerto != $puertoActual) {
    $requiereRecrear = true;
}

if ($nuevaRAM !== null && intval($nuevaRAM) !== intval($ramActual)) {
    $requiereRecrear = true;
}

// ===============================
// 5. RECREAR CONTENEDOR SI ES NECESARIO
// ===============================
if ($requiereRecrear) {

    // Parar contenedor
    exec("docker stop $nuevoNombre");

    // Eliminar contenedor
    exec("docker rm $nuevoNombre");

    // Construir comando docker run
    // Crear contenedor Unturned
    $cmdRun = sprintf(
        'docker run -d --name %s -p %d:27015/udp -p %d:27015/tcp -e STEAMCMD_APPID=304930 -e SERVER_NAME=%s -e SERVER_PORT=%d -v unturned_%s:/home/steam/unturned cm2network/unturned 2>&1',
        escapeshellcmd($nuevoNombre),
        $nuevoPuerto,
        $nuevoPuerto,
        escapeshellcmd($nuevoNombre),
        $nuevoPuerto,
        escapeshellcmd($nuevoNombre)
    );

    $outRun = [];
    $retRun = 0;
    exec($cmdRun, $outRun, $retRun);

    if ($retRun !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Error al recrear el contenedor Unturned",
            "docker_output" => $outRun,
            "cmd" => $cmdRun
        ]);
        exit;
    }
}

// ===============================
// 6. RESPUESTA FINAL
// ===============================
echo json_encode([
    "status" => "success",
    "message" => "Servidor Minecraft actualizado correctamente"
]);
