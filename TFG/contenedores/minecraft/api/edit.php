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


$stmt = $conn->prepare("
    UPDATE contenedores
    SET nombre = ?, version = ?, puerto = ?
    WHERE id = ?
");
$stmt->bind_param("ssii", $nuevoNombre, $nuevaVersion, $nuevoPuerto, $id);
$stmt->execute();
$stmt->close();


$stmt2 = $conn->prepare("
    UPDATE minecraft
    SET nombre = ?, version = ?, tipo = ?, puerto = ?
    WHERE id = ?
");
$stmt2->bind_param("sssii", $nuevoNombre, $nuevaVersion, $nuevoTipo, $nuevoPuerto, $id);
$stmt2->execute();
$stmt2->close();


if ($nombreActual !== $nuevoNombre) {
    exec("docker rename $nombreActual $nuevoNombre");
}


if ($nuevoPuerto != $puertoActual) {

    // Parar contenedor
    exec("docker stop $nuevoNombre");

    // Eliminar contenedor
    exec("docker rm $nuevoNombre");

    // Crear contenedor nuevo
 $cmdRun = sprintf(
    'docker run -d --name %s -p %d:25565 -v /TFG/servers/%s:/data -e EULA=TRUE -e VERSION=%s -e TYPE=%s itzg/minecraft-server',
    escapeshellcmd($nuevoNombre),
    $nuevoPuerto,
    escapeshellcmd($nuevoNombre),
    escapeshellcmd($nuevaVersion),
    escapeshellcmd($nuevoTipo)
);


    exec($cmdRun, $outRun, $retRun);

    if ($retRun !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Error al recrear el contenedor con el nuevo puerto",
            "docker_output" => $outRun,
            "cmd" => $cmdRun
        ]);
        exit;
    }
}


echo json_encode([
    "status" => "success",
    "message" => "Servidor Minecraft actualizado correctamente"
]);
