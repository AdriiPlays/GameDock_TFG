<?php
header("Content-Type: application/json");

$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

if ($isWindows) {

    // RAM
    $ramTotal = preg_replace('/[^0-9]/', '', trim(shell_exec("wmic computersystem get TotalPhysicalMemory /Value"))) / 1024 / 1024 / 1024;
    $ramLibre = preg_replace('/[^0-9]/', '', trim(shell_exec("wmic OS get FreePhysicalMemory /Value"))) / 1024 / 1024;
    $ramUsada = $ramTotal - $ramLibre;
    $ramPorcentaje = round(($ramUsada / $ramTotal) * 100, 1);

    // CPU
    $cpuPorcentaje = preg_replace('/[^0-9]/', '', trim(shell_exec("wmic cpu get LoadPercentage /Value")));

    // GPU
    $gpuUso = trim(shell_exec("nvidia-smi --query-gpu=utilization.gpu --format=csv,noheader 2>NUL"));
    $gpuTemp = trim(shell_exec("nvidia-smi --query-gpu=temperature.gpu --format=csv,noheader 2>NUL"));

    // Disco
    $disk = disk_free_space("C:") / disk_total_space("C:");
    $diskPorcentaje = round((1 - $disk) * 100, 1);

    // Red (Windows no tiene comando directo)
    $redTotal = rand(10, 200); // Simulación

} else {

    // RAM
    $meminfo = file_get_contents("/proc/meminfo");
    preg_match("/MemTotal:\s+(\d+)/", $meminfo, $totalMem);
    preg_match("/MemAvailable:\s+(\d+)/", $meminfo, $freeMem);

    $ramTotal = round($totalMem[1] / 1024 / 1024, 2);
    $ramLibre = round($freeMem[1] / 1024 / 1024, 2);
    $ramUsada = $ramTotal - $ramLibre;
    $ramPorcentaje = round(($ramUsada / $ramTotal) * 100, 1);

    // CPU
    $cpuLoad = sys_getloadavg()[0];
    $cpuCores = trim(shell_exec("nproc"));
    $cpuPorcentaje = round(($cpuLoad / $cpuCores) * 100, 1);

    // GPU
    $gpuUso = trim(shell_exec("nvidia-smi --query-gpu=utilization.gpu --format=csv,noheader 2>/dev/null"));
    $gpuTemp = trim(shell_exec("nvidia-smi --query-gpu=temperature.gpu --format=csv,noheader 2>/dev/null"));

    // Disco
    $disk = disk_free_space("/") / disk_total_space("/");
    $diskPorcentaje = round((1 - $disk) * 100, 1);

    // Red
    $rx1 = trim(shell_exec("cat /sys/class/net/eth0/statistics/rx_bytes"));
    $tx1 = trim(shell_exec("cat /sys/class/net/eth0/statistics/tx_bytes"));
    usleep(500000);
    $rx2 = trim(shell_exec("cat /sys/class/net/eth0/statistics/rx_bytes"));
    $tx2 = trim(shell_exec("cat /sys/class/net/eth0/statistics/tx_bytes"));

    $redTotal = round((($rx2 - $rx1) + ($tx2 - $tx1)) / 1024, 1);
}

echo json_encode([
    "ram" => [
        "total" => $ramTotal,
        "usada" => $ramUsada,
        "porcentaje" => $ramPorcentaje
    ],
    "cpu" => [
        "porcentaje" => $cpuPorcentaje,
        "temperatura" => $isWindows ? null : trim(shell_exec("cat /sys/class/thermal/thermal_zone0/temp")) / 1000
    ],
    "gpu" => [
        "porcentaje" => is_numeric($gpuUso) ? $gpuUso : null,
        "temperatura" => is_numeric($gpuTemp) ? $gpuTemp : null
    ],
    "disco" => [
        "porcentaje" => $diskPorcentaje
    ],
    "red" => [
        "total" => $redTotal
    ]
]);
