<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

$servidor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? null;

if (!$servidor || !$ruta) {
    echo json_encode(["estado" => "error", "mensaje" => "Faltan parámetros"]);
    exit;
}

// Normalizar ruta
if (!str_starts_with($ruta, "/")) {
    $ruta = "/" . $ruta;
}

// Forzar ruta base correcta para Ubuntu SSH
if (!str_starts_with($ruta, "/data")) {
    $ruta = "/data" . $ruta;
}

// Comando para borrar
$comando = "docker exec " . escapeshellarg($servidor) . " rm -rf " . escapeshellarg($ruta);
exec($comando, $salida, $codigo);

if ($codigo !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo borrar el archivo o carpeta"]);
    exit;
}

echo json_encode(["estado" => "exito", "mensaje" => "Elemento borrado correctamente"]);
