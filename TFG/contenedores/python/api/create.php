<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

// LEER JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$nombre = $data["nombre"] ?? null;
$puerto = $data["puerto"] ?? null;
$dependencias = $data["dependencias"] ?? ""; // opcional

if (!$nombre || !$puerto) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}


   // INSERTAR EN TABLA contenedores


$stmt = $conn->prepare("
    INSERT INTO contenedores (nombre, iso, version, puerto)
    VALUES (?, 'python', 'latest', ?)
");
$stmt->bind_param("si", $nombre, $puerto);

if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al insertar en contenedores"]);
    exit;
}

$idContenedor = $stmt->insert_id;
$stmt->close();


   // INSERTAR EN TABLA python


$stmt2 = $conn->prepare("
    INSERT INTO python (id, nombre, puerto)
    VALUES (?, ?, ?)
");
$stmt2->bind_param("isi", $idContenedor, $nombre, $puerto);

if (!$stmt2->execute()) {
    echo json_encode(["status" => "error", "message" => "Error al insertar en tabla python"]);
    exit;
}

$stmt2->close();


   // CREAR CONTENEDOR DOCKER


$cmd = sprintf(
    'docker run -d --name %s ' .
    '-p %d:8000 ' .
    '-v python_%s:/home/python/app ' .
    'admuro/python:latest 2>&1',

    escapeshellcmd($nombre),
    $puerto,
    escapeshellcmd($nombre)
);

$out = [];
$ret = 0;
exec($cmd, $out, $ret);

if ($ret !== 0) {

    // Si falla la creación, borrar BD
    $conn->query("DELETE FROM python WHERE id = $idContenedor");
    $conn->query("DELETE FROM contenedores WHERE id = $idContenedor");

    echo json_encode([
        "status" => "error",
        "message" => "Error al crear el contenedor Docker",
        "docker_output" => $out,
        "cmd" => $cmd
    ]);
    exit;
}


   // INSTALAR DEPENDENCIAS 


if (!empty($dependencias)) {

    $tmp = sys_get_temp_dir() . "/req_" . uniqid() . ".txt";
    file_put_contents($tmp, $dependencias);

    // Copiar requirements.txt al contenedor
    exec("docker cp " . escapeshellarg($tmp) . " " . escapeshellarg("$nombre:/home/python/app/requirements.txt"));

    unlink($tmp);

    // Ejecutar pip install
    exec("docker exec $nombre pip install -r /home/python/app/requirements.txt 2>&1", $outDeps, $retDeps);
}


   //RESPUESTA JSON


registrarLog($conn, $_SESSION["usuario"], "Creó el contenedor Python '{$nombre}'");

echo json_encode([
    "status" => "success",
    "message" => "Contenedor Python creado correctamente",
    "id" => $idContenedor,
    "dependencias_log" => $outDeps ?? []
]);
