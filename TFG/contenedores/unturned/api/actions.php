<?php
header("Content-Type: application/json");
session_start(); 

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../../Funciones/logs.php";

// Leer JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$tipo   = $data["tipo"] ?? null;
$nombre = $data["nombre"] ?? null;

if (!$tipo || !$nombre) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

$out = [];
$ret = 0;

// Reiniciar contenedor

if ($tipo === "restart") {

    exec("docker restart " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

    if ($ret !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo reiniciar el servidor Unturned",
            "docker_output" => $out
        ]);
        exit;
    }

    registrarLog($conn, $_SESSION["usuario"], "Reinició el servidor Unturned '{$nombre}'");

    echo json_encode([
        "status" => "success",
        "message" => "Servidor reiniciado correctamente"
    ]);
    exit;
}

 // SWITCH DE ACCIONES

switch ($tipo) {

    case "start":
        exec("docker start " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo iniciar el servidor Unturned",
                "docker_output" => $out
            ]);
            exit;
        }

        registrarLog($conn, $_SESSION["usuario"], "Inició el servidor Unturned '{$nombre}'");

        echo json_encode(["status" => "success", "message" => "Servidor iniciado"]);
        break;


    case "stop":
        exec("docker stop " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo detener el servidor Unturned",
                "docker_output" => $out
            ]);
            exit;
        }

        registrarLog($conn, $_SESSION["usuario"], "Detuvo el servidor Unturned '{$nombre}'");

        echo json_encode(["status" => "success", "message" => "Servidor detenido"]);
        break;


    case "delete":

        // Eliminar contenedor 
        exec("docker rm -f " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo eliminar el contenedor Docker",
                "docker_output" => $out
            ]);
            exit;
        }

        // Eliminar volumen  
        $volumen = "unturned_" . $nombre;
        exec("docker volume rm " . escapeshellcmd($volumen) . " 2>&1", $outVol, $retVol);

        $volumenEliminado = ($retVol === 0);

        // Eliminar de la tabla unturned
        $stmt = $conn->prepare("DELETE FROM unturned WHERE id = (SELECT id FROM contenedores WHERE nombre = ?)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->close();

        // Eliminar de la tabla contenedores
        $stmt2 = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
        $stmt2->bind_param("s", $nombre);
        $stmt2->execute();
        $stmt2->close();

        // Registrar log
        registrarLog($conn, $_SESSION["usuario"], "Eliminó el servidor Unturned '{$nombre}'");

        echo json_encode([
            "status" => "success",
            "message" => "Servidor eliminado completamente",
            "volumen_eliminado" => $volumenEliminado
        ]);
        break;


  case "update":
    // Pasamos los datos a update.php
    $_POST = $data;
    require __DIR__ . "/update.php";
    exit;


    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
}
