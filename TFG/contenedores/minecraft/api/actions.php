<?php
header("Content-Type: application/json");
session_start(); // NECESARIO PARA USAR $_SESSION

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

// -----------------------------
// RESTART (fuera del switch)
// -----------------------------
if ($tipo === "restart") {

    exec("docker restart " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

    if ($ret !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo reiniciar el servidor",
            "docker_output" => $out
        ]);
        exit;
    }

    registrarLog($conn, $_SESSION["usuario"], "Reinició el servidor Minecraft '{$nombre}'");

    echo json_encode([
        "status" => "success",
        "message" => "Servidor reiniciado correctamente"
    ]);
    exit;
}


// -----------------------------
// SWITCH DE ACCIONES
// -----------------------------
switch ($tipo) {

    case "start":
        exec("docker start " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo iniciar el servidor",
                "docker_output" => $out
            ]);
            exit;
        }

        registrarLog($conn, $_SESSION["usuario"], "Inició el servidor Minecraft '{$nombre}'");

        echo json_encode(["status" => "success", "message" => "Servidor iniciado"]);
        break;


    case "stop":
        exec("docker stop " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

        if ($ret !== 0) {
            echo json_encode([
                "status" => "error",
                "message" => "No se pudo detener el servidor",
                "docker_output" => $out
            ]);
            exit;
        }

        registrarLog($conn, $_SESSION["usuario"], "Detuvo el servidor Minecraft '{$nombre}'");

        echo json_encode(["status" => "success", "message" => "Servidor detenido"]);
        break;

case "delete":

    // 1. Eliminar contenedor Docker
    exec("docker rm -f " . escapeshellcmd($nombre) . " 2>&1", $out, $ret);

    if ($ret !== 0) {
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo eliminar el contenedor Docker",
            "docker_output" => $out
        ]);
        exit;
    }

    // 2. Eliminar volumen Docker asociado
    $volumen = "mc_" . $nombre;
    exec("docker volume rm " . escapeshellcmd($volumen) . " 2>&1", $outVol, $retVol);

    // No hacemos exit si falla, porque puede no existir
    if ($retVol === 0) {
        $volumenEliminado = true;
    } else {
        $volumenEliminado = false;
    }

    // 3. Eliminar de la tabla minecraft
    $stmt = $conn->prepare("DELETE FROM minecraft WHERE id = (SELECT id FROM contenedores WHERE nombre = ?)");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $stmt->close();

    // 4. Eliminar de la tabla contenedores
    $stmt2 = $conn->prepare("DELETE FROM contenedores WHERE nombre = ?");
    $stmt2->bind_param("s", $nombre);
    $stmt2->execute();
    $stmt2->close();

    // 5. Registrar log
    registrarLog($conn, $_SESSION["usuario"], "Eliminó el servidor Minecraft '{$nombre}'");

    // 6. Respuesta final
    echo json_encode([
        "status" => "success",
        "message" => "Servidor eliminado completamente",
        "volumen_eliminado" => $volumenEliminado
    ]);
    break;




    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
}
