<?php
session_start();

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["estado" => "error", "mensaje" => "No autorizado"]);
    exit;
}

$servidor = $_POST["servidor"] ?? null;
$ruta = $_POST["ruta"] ?? null;
$contenido = $_POST["contenido"] ?? null;

if (!$servidor || !$ruta || $contenido === null) {
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

// Archivo temporal
$tmp = sys_get_temp_dir() . "/" . uniqid("save_") . "_" . basename($ruta);

// Guardar contenido temporalmente
file_put_contents($tmp, $contenido);

// Copiar al contenedor
$comando = "docker cp " . escapeshellarg($tmp) . " " . escapeshellarg("$servidor:$ruta");
exec($comando, $salida, $codigo);

// Borrar archivo temporal
unlink($tmp);

if ($codigo !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo guardar el archivo"]);
    exit;
}

echo json_encode(["estado" => "exito", "mensaje" => "Archivo guardado correctamente"]);
