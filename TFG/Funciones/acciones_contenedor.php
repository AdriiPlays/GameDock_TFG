<?php
session_start();
require_once "../config.php";
require_once "../Funciones/logs.php";

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

$accion = $input["accion"] ?? null;
$nombre = isset($input["nombre"]) ? escapeshellcmd($input["nombre"]) : null;
$usuario = $_SESSION["usuario"] ?? "desconocido";

if (!$accion || !$nombre) {
    echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
    exit;
}

$out = [];
$ret = 0;
$msg = "Acción desconocida";

switch ($accion) {
    case "start":
        exec("docker start \"$nombre\" 2>&1", $out, $ret);
        $msg = "Contenedor iniciado";
        break;

    case "stop":
        exec("docker stop \"$nombre\" 2>&1", $out, $ret);
        $msg = "Contenedor detenido";
        break;

    case "restart":
        exec("docker restart \"$nombre\" 2>&1", $out, $ret);
        $msg = "Contenedor reiniciado";
        break;

    case "delete":
        exec("docker rm -f \"$nombre\" 2>&1", $out, $ret);
        if ($ret === 0) {
            $stmt = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $stmt->close();
        }
        $msg = "Contenedor eliminado";
        break;
}

registrarLog($conn, $usuario, "$msg: $nombre");

echo json_encode([
    "status" => $ret === 0 ? "success" : "error",
    "message" => $ret === 0 ? $msg : ("Error: " . implode("\n", $out))
]);
