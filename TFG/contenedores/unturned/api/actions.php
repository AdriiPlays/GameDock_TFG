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

/* ============================
   ACCIONES START / STOP / DELETE
   ============================ */

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

    // Preparamos el JSON que update.php necesita
    $json = json_encode(["nombre" => $nombre]);

    // Ejecutamos update.php como script independiente
    $cmd = "php " . __DIR__ . "/update.php";

    $descriptors = [
        0 => ["pipe", "r"], // STDIN
        1 => ["pipe", "w"], // STDOUT
        2 => ["pipe", "w"]  // STDERR
    ];

    $proc = proc_open($cmd, $descriptors, $pipes);

    // Enviar JSON a update.php
    fwrite($pipes[0], $json);
    fclose($pipes[0]);

    // Leer salida
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // Leer errores
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    proc_close($proc);

    // Si update.php devolvió HTML → error
    if (str_starts_with(trim($output), "<")) {
        echo json_encode([
            "status" => "error",
            "message" => "Error interno en update.php",
            "debug" => $output
        ]);
        exit;
    }

    // Devolver JSON tal cual
    echo $output;
    exit;

    default:
        echo json_encode(["status" => "error", "message" => "Acción no válida"]);
}
