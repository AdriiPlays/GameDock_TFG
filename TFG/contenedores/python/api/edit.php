<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../../../config.php";

$data = json_decode(file_get_contents("php://input"), true);

$id            = $data["id"];
$nombreActual  = $data["nombreActual"];
$nuevoNombre   = $data["nuevoNombre"];
$nuevoPuerto   = $data["nuevoPuerto"];
$puertoActual  = $data["puertoActual"];


// CTUALIZAR TABLA contenedores

$stmt = $conn->prepare("
    UPDATE contenedores
    SET nombre = ?, puerto = ?
    WHERE id = ?
");
$stmt->bind_param("sii", $nuevoNombre, $nuevoPuerto, $id);
$stmt->execute();
$stmt->close();


// ACTUALIZAR TABLA python

$stmt2 = $conn->prepare("
    UPDATE python
    SET nombre = ?, puerto = ?
    WHERE id = ?
");
$stmt2->bind_param("sii", $nuevoNombre, $nuevoPuerto, $id);
$stmt2->execute();
$stmt2->close();


// RENOMBRAR CONTENEDOR 

if ($nombreActual !== $nuevoNombre) {
    exec("docker rename $nombreActual $nuevoNombre");
}

$requiereRecrear = false;

if ($nuevoPuerto != $puertoActual) {
    $requiereRecrear = true;
}

// RECREAR CONTENEDOR

if ($requiereRecrear) {

    // Parar contenedor
    exec("docker stop $nuevoNombre");

    // Eliminar contenedor
    exec("docker rm $nuevoNombre");

    // Crear contenedor Python
    $cmdRun = sprintf(
        'docker run -d --name %s -p %d:8000 -v python_%s:/home/python/app admuro/python:latest',
        escapeshellcmd($nuevoNombre),
        $nuevoPuerto,
        escapeshellcmd($nuevoNombre)
    );

    exec($cmdRun, $outRun, $retRun);

    if ($retRun !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Error al recrear el contenedor",
            "docker_output" => $outRun,
            "cmd" => $cmdRun
        ]);
        exit;
    }
}


// RESPUESTA JSON

echo json_encode([
    "status" => "success",
    "message" => "Contenedor Python actualizado correctamente"
]);
