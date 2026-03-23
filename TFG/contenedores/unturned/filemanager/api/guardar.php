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

// Asegurar formato correcto de ruta
if (!str_starts_with($ruta, "/")) {
    $ruta = "/" . $ruta;
}

// NO forzar /data → Unturned NO usa esa ruta
// Usamos la ruta tal cual viene del FileManager

// Archivo temporal
$tmp = sys_get_temp_dir() . "/" . uniqid("save_") . "_" . basename($ruta);

// Guardar contenido temporalmente
file_put_contents($tmp, $contenido);

// Copiar al contenedor
$comando = "docker cp " . escapeshellarg($tmp) . " " . escapeshellarg($servidor . ":" . $ruta);
exec($comando, $salida, $codigo);

// Borrar archivo temporal
unlink($tmp);

if ($codigo !== 0) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo guardar el archivo"]);
    exit;
}

echo json_encode(["estado" => "exito", "mensaje" => "Archivo guardado correctamente"]);
