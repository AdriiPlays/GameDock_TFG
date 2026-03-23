<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

/* ============================
   LEER JSON
   ============================ */

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$nombre  = $data["nombre"] ?? null;
$version = $data["version"] ?? null;
$tipo    = $data["tipo"] ?? null;
$puerto  = $data["puerto"] ?? null;

if (!$nombre || !$version || !$tipo || !$puerto) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

/* ============================
   INSERTAR EN TABLA contenedores
   ============================ */

$stmt = $conn->prepare("
    INSERT INTO contenedores (nombre, iso, version, puerto)
    VALUES (?, 'unturned', ?, ?)
");
$stmt->bind_param("ssi", $nombre, $version, $puerto);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al insertar en contenedores"]);
    exit;
}

$idContenedor = $stmt->insert_id;
$stmt->close();

/* ============================
   INSERTAR EN TABLA unturned
   ============================ */

$ramDefault = 2048; // RAM inicial por defecto

$stmt2 = $conn->prepare("
    INSERT INTO unturned (id, nombre, version, tipo, puerto, ram)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt2->bind_param("isssii", $idContenedor, $nombre, $version, $tipo, $puerto, $ramDefault);

if (!$stmt2->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al insertar en tabla unturned"]);
    exit;
}

$stmt2->close();

/* ============================
   CREAR CONTENEDOR DOCKER
   ============================ */

$cmd = sprintf(
    'docker run -d --name %s ' .
    '-p %d:27015/udp -p %d:27015/tcp ' .
    '-v unturned_%s:/home/steam/unturned ' .
    'admuro/unturned:latest ' .
    './Unturned_Headless.x86_64 -nographics -batchmode +secureserver "%s" +port %d 2>&1',
    
    escapeshellcmd($nombre),
    $puerto,
    $puerto,
    escapeshellcmd($nombre),
    escapeshellcmd($nombre),
    $puerto
);
shell_exec($cmd . " > /dev/null 2>&1 &");


$out = [];
$ret = 0;
exec($cmd, $out, $ret);

if ($ret !== 0) {

    // Si falla Docker, borrar BD
    $conn->query("DELETE FROM unturned WHERE id = $idContenedor");
    $conn->query("DELETE FROM contenedores WHERE id = $idContenedor");

    echo json_encode([
        "status" => "error",
        "message" => "Error al crear el contenedor Docker",
        "docker_output" => $out,
        "cmd" => $cmd
    ]);
    exit;
}

/* ============================
   LOG Y RESPUESTA FINAL
   ============================ */

registrarLog($conn, $_SESSION["usuario"], "Creó el servidor Unturned '{$nombre}'");

echo json_encode([
    "status" => "success",
    "message" => "Servidor Unturned creado correctamente",
    "id" => $idContenedor
]);
