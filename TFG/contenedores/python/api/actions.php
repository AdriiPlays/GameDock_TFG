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

/* ============================
   RESTART
   ============================ */

if ($tipo === "restart") {

    exec("docker restart " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

    if ($ret !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo reiniciar el contenedor Python",
            "docker_output" => $out
        ]);
        exit;
    }

    registrarLog($conn, $_SESSION["usuario"], "Reinició el contenedor Python '{$nombre}'");

    echo json_encode([
        "status" => "success",
        "message" => "Contenedor reiniciado correctamente"
    ]);
    exit;
}

/* ============================
   ACCIONES START / STOP / DELETE
   ============================ */

switch ($tipo) {

    case "start":
        exec("docker start " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo iniciar el contenedor Python",
                "docker_output" => $out
            ]);
            exit;
        }

        registrarLog($conn, $_SESSION["usuario"], "Inició el contenedor Python '{$nombre}'");

        echo json_encode(["status" => "success", "message" => "Contenedor iniciado"]);
        break;


    case "stop":
        exec("docker stop " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo detener el contenedor Python",
                "docker_output" => $out
            ]);
            exit;
        }

        registrarLog($conn, $_SESSION["usuario"], "Detuvo el contenedor Python '{$nombre}'");

        echo json_encode(["status" => "success", "message" => "Contenedor detenido"]);
        break;


    case "delete":

        // Eliminar contenedor Docker
        exec("docker rm -f " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo eliminar el contenedor Docker",
                "docker_output" => $out
            ]);
            exit;
        }

        // Eliminar volumen Docker 
        $volumen = "python_" . $nombre;
        exec("docker volume rm " . escapeshellcmd($volumen) . " 2>&1", $outVol, $retVol);

        $volumenEliminado = ($retVol === 0);

        // Eliminar de la tabla python
        $stmt = $conn->prepare("DELETE FROM python WHERE id = (SELECT id FROM contenedores WHERE nombre = ?)");
        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->close();

        // Eliminar de la tabla contenedores
        $stmt2 = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
        $stmt2->bind_param("s", $nombre);
        $stmt2->execute();
        $stmt2->close();

        // Registrar log
        registrarLog($conn, $_SESSION["usuario"], "Eliminó el contenedor Python '{$nombre}'");

        echo json_encode([
            "status" => "success",
            "message" => "Contenedor eliminado completamente",
            "volumen_eliminado" => $volumenEliminado
        ]);
        break;


    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
}
