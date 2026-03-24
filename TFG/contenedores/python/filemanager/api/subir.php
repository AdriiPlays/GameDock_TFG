<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

$servidor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? "/data";

if (!$servidor || !isset($_FILES["archivo"])) {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan datos"]);
    exit;
}

$archivoTmp = $_FILES["archivo"]["tmp_name"];
$nombreArchivo = basename($_FILES["archivo"]["name"]);

// Ruta destino dentro del contenedor
$rutaDestino = rtrim($ruta, "/") . "/" . $nombreArchivo;

// Comando docker cp
$comando = "docker cp " . escapeshellarg($archivoTmp) . " " .
           escapeshellarg($servidor . ":" . $rutaDestino);

exec($comando, $salida, $codigo);

if ($codigo !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "Error al subir el archivo"]);
    exit;
}

echo json_encode(["estado" => "exito", "mensaje" => "Archivo subido correctamente"]);
