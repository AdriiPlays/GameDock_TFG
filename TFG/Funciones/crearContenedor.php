<?php


session_start();
require_once "../config.php";
require_once "logs.php";

if (!isset($_SESSION["usuario"])) {
    echo "error";
    exit;
}

$nombre  = escapeshellarg($_POST["nombre"]);
$iso     = escapeshellarg($_POST["iso"]);
$version = escapeshellarg($_POST["version"]);
$usuario = $_SESSION["usuario"];

// Construir imagen completa: ubuntu:latest, ubuntu:22.04, etc.
$imagen = "$iso:$version";

// Comando Docker
$cmd = "docker run -d --name $nombre $imagen";

// Ejecutar
exec($cmd . " 2>&1", $out, $ret);

if ($ret !== 0) {
    echo "error";
    exit;
}

// Guardar en BD
$stmt = $conn->prepare("INSERT INTO contenedores (nombre, iso, version, estado) VALUES (?, ?, ?, 'online')");
$stmt->bind_param("sss", $_POST["nombre"], $_POST["iso"], $_POST["version"]);
$stmt->execute();
$stmt->close();

// Registrar log
registrarLog($conn, $usuario, "Creó el contenedor '{$_POST["nombre"]}'");

echo "ok";
