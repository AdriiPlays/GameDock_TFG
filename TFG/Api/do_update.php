<?php
header("Content-Type: application/json");

// URL del ZIP del repositorio
$zipUrl = "https://github.com/AdriiPlays/GameDock_TFG/archive/refs/heads/main.zip";

$tmpZip = "../update.zip";
$tmpDir = "../update_tmp";

// Descargar ZIP
file_put_contents($tmpZip, fopen($zipUrl, "r"));

// Crear carpeta temporal
if (!is_dir($tmpDir)) mkdir($tmpDir);

// Descomprimir
$zip = new ZipArchive;
if ($zip->open($tmpZip) === TRUE) {
    $zip->extractTo($tmpDir);
    $zip->close();
} else {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo descomprimir el ZIP"]);
    exit;
}

// Carpeta principal del ZIP (GameDock_TFG-main)
$rootFolder = glob($tmpDir . "/*")[0];

// Carpeta TFG dentro del ZIP
$folder = $rootFolder . "/TFG";

// Función recursiva para copiar archivos
function copiar($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);

    while(false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copiar($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// Copiar archivos sobre el panel
copiar($folder, "../");

// Limpiar
unlink($tmpZip);
exec("rm -rf " . escapeshellarg($tmpDir));

echo json_encode(["estado" => "ok", "mensaje" => "Panel actualizado correctamente"]);
