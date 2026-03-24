<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$contenedor = $data["nombre"] ?? null;
$nuevas = trim($data["dependencias"] ?? "");

if (!$contenedor || !$nuevas) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

// 1. Obtener requirements.txt actual
$tmpOld = sys_get_temp_dir() . "/req_old_" . uniqid() . ".txt";
exec("docker cp $contenedor:/home/python/app/requirements.txt $tmpOld 2>/dev/null");

$actual = file_exists($tmpOld) ? file_get_contents($tmpOld) : "";
unlink($tmpOld);

// 2. Añadir nuevas dependencias sin borrar las anteriores
$lineas = explode("\n", $actual);
$lineas = array_filter(array_map("trim", $lineas)); // limpiar vacíos

$nuevasLineas = explode("\n", $nuevas);
foreach ($nuevasLineas as $dep) {
    $dep = trim($dep);
    if ($dep !== "" && !in_array($dep, $lineas)) {
        $lineas[] = $dep;
    }
}

$actualizado = implode("\n", $lineas) . "\n";

// 3. Guardar requirements.txt actualizado
$tmpNew = sys_get_temp_dir() . "/req_new_" . uniqid() . ".txt";
file_put_contents($tmpNew, $actualizado);

exec("docker cp $tmpNew $contenedor:/home/python/app/requirements.txt");
unlink($tmpNew);

// 4. Instalar dependencias y enviar logs a main.log
$cmd = "docker exec $contenedor sh -c \"pip install -r /home/python/app/requirements.txt 2>&1 | tee -a /home/python/app/main.log\"";
exec($cmd, $out, $ret);

echo json_encode([
    "status" => $ret === 0 ? "success" : "error",
    "message" => $ret === 0 ? "Dependencias instaladas correctamente" : "Error al instalar dependencias",
    "log" => implode("\n", $out)
]);
