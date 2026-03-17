<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";


// Leer JSON
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


// INSERTAR EN TABLA contenedores

$stmt = $conn->prepare("
    INSERT INTO contenedores (nombre, iso, version, puerto)
    VALUES (?, 'minecraft', ?, ?)
");
$stmt->bind_param("ssi", $nombre, $version, $puerto);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al insertar en contenedores"]);
    exit;
}

$idContenedor = $stmt->insert_id;
$stmt->close();


// INSERTAR EN TABLA minecraft

$stmt2 = $conn->prepare("
    INSERT INTO minecraft (id, nombre, version, tipo, puerto)
    VALUES (?, ?, ?, ?, ?)
");
$stmt2->bind_param("isssi", $idContenedor, $nombre, $version, $tipo, $puerto);

if (!$stmt2->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al insertar en tabla minecraft"]);
    exit;
}

$stmt2->close();


// SELECCIONAR  VERSION DE JAVA AUTOMÁTICAMENTE (SOLUCIONAR ERRORES DE VERSIONES EN CONTENEDORES)

function seleccionarJava($version) {
    $v = floatval(substr($version, 0, 4));

    if ($v <= 1.12) {
        return "java8";
    } elseif ($v <= 1.16) {
        return "java11";
    } elseif ($v <= 1.20) {
        return "java17";
    } else {
        return "java21";
    }
}

$java = seleccionarJava($version);


// CREAR CONTENEDOR DOCKER


$imagen = "itzg/minecraft-server:$java";


$cmd = sprintf(
    'docker run -d --name %s -p %d:25565 -e EULA=TRUE -e VERSION=%s -e TYPE=%s -v mc_%s:/data %s 2>&1',
    escapeshellcmd($nombre),
    $puerto,
    escapeshellcmd($version),
    escapeshellcmd($tipo),
    escapeshellcmd($nombre),
    $imagen
);

// Arreglar permisos del archivo server.properties
exec("docker run --rm -v mc_$nombre:/data alpine sh -c \"chmod 666 /data/server.properties\"");


$out = [];
$ret = 0;
exec($cmd, $out, $ret);

if ($ret !== 0) {
    // Si falla Docker, borrar BD
    $conn->query("DELETE FROM minecraft WHERE id = $idContenedor");
    $conn->query("DELETE FROM contenedores WHERE id = $idContenedor");

    echo json_encode([
        "status" => "error",
        "message" => "Error al crear el contenedor Docker",
        "docker_output" => $out,
        "cmd" => $cmd
    ]);
    exit;
}


// RESPUESTA FINAL / LOGS

registrarLog($conn, $_SESSION["usuario"], "Creó el servidor Minecraft '{$nombre}'");

echo json_encode([
    "status" => "success",
    "message" => "Servidor Minecraft creado correctamente",
    "id" => $idContenedor
]);
