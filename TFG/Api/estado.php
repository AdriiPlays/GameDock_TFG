<?php
header("Content-Type: application/json");

$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

/* ============================
   SISTEMA OPERATIVO (Linux/Windows)
   ============================ */

if ($isWindows) {

    // Nombre del SO
    $so = trim(shell_exec("wmic os get Caption /Value"));
    $so = explode("=", $so)[1] ?? "Windows";

    // Kernel
    $kernel = php_uname("r");

    // Arquitectura
    $arq = php_uname("m");

    // Uptime
    $uptimeRaw = trim(shell_exec("wmic os get LastBootUpTime /Value"));
    $uptimeRaw = explode("=", $uptimeRaw)[1] ?? "";
    $boot = strtotime($uptimeRaw);
    $uptimeFmt = gmdate("H:i:s", time() - $boot);

    // CPU info
    $cpu = trim(shell_exec("wmic cpu get Name /Value"));
    $cpu = explode("=", $cpu)[1] ?? "CPU";

    // RAM total
    $ramTotalSys = preg_replace('/[^0-9]/', '', trim(shell_exec("wmic computersystem get TotalPhysicalMemory /Value"))) / 1024 / 1024 / 1024;
    $ramTotalSys = round($ramTotalSys, 2) . " GB";

    // Disco total
    $discoTotalSys = round(disk_total_space("C:") / 1024 / 1024 / 1024, 2) . " GB";

    // IP
    $ip = trim(shell_exec("ipconfig | findstr /R /C:\"IPv4\""));
    $ip = explode(":", $ip)[1] ?? "Desconocida";
    $ip = trim($ip);

} else {

    // SO
    $osRelease = @parse_ini_file("/etc/os-release");
    $so = $osRelease["PRETTY_NAME"] ?? php_uname("s");

    // Kernel
    $kernel = php_uname("r");

    // Arquitectura
    $arq = php_uname("m");

    // Uptime
    $uptime = @file_get_contents("/proc/uptime");
    $uptimeSeg = explode(" ", $uptime)[0] ?? 0;
    $uptimeFmt = gmdate("H:i:s", (int)$uptimeSeg);

    // CPU
    $cpu = "Desconocido";
    if (file_exists("/proc/cpuinfo")) {
        foreach (file("/proc/cpuinfo") as $line) {
            if (strpos($line, "model name") !== false) {
                $cpu = trim(explode(":", $line)[1]);
                break;
            }
        }
    }

    // RAM total
    $memInfo = @file_get_contents("/proc/meminfo");
    preg_match("/MemTotal:\s+(\d+)/", $memInfo, $m);
    $ramTotalSys = round(($m[1] ?? 0) / 1024 / 1024, 2) . " GB";

    // Disco total
    $discoTotalSys = round(disk_total_space("/") / 1024 / 1024 / 1024, 2) . " GB";

    // IP
    $ip = trim(shell_exec("hostname -I | awk '{print $1}'"));
}

/* ============================
   MÉTRICAS (RAM, CPU, GPU, DISCO, RED)
   ============================ */

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

    // Red (simulada)
    $redTotal = rand(10, 200);

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

/* ============================
   RESPUESTA JSON COMPLETA
   ============================ */

echo json_encode([
    "sistema" => [
        "so" => $so,
        "kernel" => $kernel,
        "arquitectura" => $arq,
        "uptime" => $uptimeFmt,
        "cpu" => $cpu,
        "ram_total" => $ramTotalSys,
        "disco_total" => $discoTotalSys,
        "ip" => $ip
    ],
    "ram" => [
        "total" => $ramTotal,
        "usada" => $ramUsada,
        "porcentaje" => $ramPorcentaje
    ],
    "cpu" => [
        "porcentaje" => $cpuPorcentaje,
        "temperatura" => $isWindows ? null : trim(@shell_exec("cat /sys/class/thermal/thermal_zone0/temp")) / 1000
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
