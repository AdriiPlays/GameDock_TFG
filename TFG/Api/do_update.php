<?php
header("Content-Type: application/json");

try {

    $zipUrl = "https://github.com/AdriiPlays/GameDock_TFG/archive/refs/heads/main.zip";

    $tmpZip = "../update.zip";
    $tmpDir = "../update_tmp";

    // Descargar ZIP
    if (!file_put_contents($tmpZip, fopen($zipUrl, "r"))) {
        throw new Exception("No se pudo descargar el ZIP desde GitHub");
    }

    // Crear carpeta temporal
    if (!is_dir($tmpDir)) mkdir($tmpDir);

    // Detectar sistema operativo
    $os = strtoupper(substr(PHP_OS, 0, 3)); // WIN / LIN / DAR

    // 🔥 DESCOMPRIMIR SEGÚN EL SISTEMA OPERATIVO
    if ($os === "WIN") {
        // WINDOWS → PowerShell
        $cmd = 'powershell -command "Expand-Archive -Path ' . escapeshellarg($tmpZip) . ' -DestinationPath ' . escapeshellarg($tmpDir) . ' -Force"';
        shell_exec($cmd);
    } else {
        // LINUX → unzip o tar
        $output = shell_exec("unzip -o " . escapeshellarg($tmpZip) . " -d " . escapeshellarg($tmpDir) . " 2>&1");

        if (strpos($output, "command not found") !== false) {
            // Fallback → tar
            shell_exec("tar -xf " . escapeshellarg($tmpZip) . " -C " . escapeshellarg($tmpDir));
        }
    }

    // Carpeta principal del ZIP (GameDock_TFG-main)
    $rootFolder = glob($tmpDir . "/*")[0];

    // Carpeta TFG dentro del ZIP
    $folder = $rootFolder . "/TFG";

    if (!is_dir($folder)) {
        throw new Exception("No se encontró la carpeta TFG dentro del ZIP descargado");
    }

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

    if ($os === "WIN") {
        exec("rmdir /S /Q " . escapeshellarg($tmpDir));
    } else {
        exec("rm -rf " . escapeshellarg($tmpDir));
    }

    echo json_encode(["estado" => "ok", "mensaje" => "Panel actualizado correctamente"]);

} catch (Exception $e) {
    echo json_encode(["estado" => "error", "mensaje" => $e->getMessage()]);
}
