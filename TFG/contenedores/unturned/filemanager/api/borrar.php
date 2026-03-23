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

// Asegurar ruta absoluta dentro del contenedor
if (!str_starts_with($ruta, "/")) {
    $ruta = "/" . $ruta;
}

// NO forzar /data → Unturned NO usa esa ruta
// Usamos la ruta tal cual viene del FileManager

// Comando para borrar 
$comando = "docker exec " . escapeshellarg($servidor) . " rm -rf " . escapeshellarg($ruta);
exec($comando, $salida, $codigo);

if ($codigo !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo borrar el archivo o carpeta"]);
    exit;
}

echo json_encode(["estado" => "exito", "mensaje" => "Elemento borrado correctamente"]);
