<?php
header("Content-Type: application/json");

// Versión local
$versionLocal = trim(file_get_contents("../version.txt"));

// URL RAW de la versión
$url = "https://raw.githubusercontent.com/AdriiPlays/GameDock_TFG/main/TFG/version.txt";

// Obtener versión 
$versionGitHub = @trim(file_get_contents($url));

if (!$versionGitHub) {
    echo json_encode(["estado" => "error", "mensaje" => "No se pudo conectar a GitHub"]);
    exit;
}

// Comparar versiones
if (version_compare($versionGitHub, $versionLocal, ">")) {
    echo json_encode([
        "estado" => "disponible",
        "version" => $versionGitHub
    ]);
} else {
    echo json_encode(["estado" => "actualizado"]);
}
