<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ERROR | E_PARSE);

header("Content-Type: application/json");

$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

/* ============================
   SISTEMA OPERATIVO
   ============================ */

if ($isWindows) {

    // Nombre del SO
    $soRaw = shell_exec("wmic os get Caption /value 2>NUL");
    $so = "Windows";
    if ($soRaw && str_contains($soRaw, "=")) {
        $so = trim(explode("=", $soRaw)[1]);
    }

    // Kernel
    $kernel = php_uname("r");

    // Arquitectura
    $arq = php_uname("m");

    // Uptime seguro
    $uptimeFmt = "00:00:00";
    $bootRaw = shell_exec("wmic os get LastBootUpTime /value 2>NUL");
    if ($bootRaw && str_contains($bootRaw, "=")) {
        $boot = strtotime(trim(explode("=", $bootRaw)[1]));
        if ($boot) {
            $uptimeFmt = gmdate("H:i:s", time() - $boot);
        }
    }

    // CPU
    $cpuRaw = shell_exec("wmic cpu get Name /value 2>NUL");
    $cpu = "CPU";
    if ($cpuRaw && str_contains($cpuRaw, "=")) {
        $cpu = trim(explode("=", $cpuRaw)[1]);
    }

    // RAM total
    $ramRaw = shell_exec("wmic computersystem get TotalPhysicalMemory /value 2>NUL");
    preg_match("/=(\d+)/", $ramRaw, $matchRam);
    $ramTotalSys = isset($matchRam[1]) ? round($matchRam[1] / 1024 / 1024 / 1024, 2) . " GB" : "0 GB";

    // Disco total
    $discoTotalSys = round(disk_total_space("C:") / 1024 / 1024 / 1024, 2) . " GB";

    // IP
    $ip = "127.0.0.1";
    $ipRaw = shell_exec("ipconfig 2>NUL");
    if ($ipRaw && preg_match("/IPv4.*?: ([0-9\.]+)/", $ipRaw, $m)) {
        $ip = trim($m[1]);
    }

} else {

    // SO
    $osRelease = @parse_ini_file("/etc/os-release");
    $so = $osRelease["PRETTY_NAME"] ?? php_uname("s");

    // Kernel
    $kernel = php_uname("r");

    // Arquitectura
    $arq = php_uname("m");

    // Uptime
    $uptimeFmt = "00:00:00";
    $uptime = @file_get_contents("/proc/uptime");
    if ($uptime) {
        $uptimeSeg = explode(" ", $uptime)[0];
        $uptimeFmt = gmdate("H:i:s", (int)$uptimeSeg);
    }

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
    $ip = trim(shell_exec("hostname -I 2>/dev/null"));
}

/* ============================
   MÉTRICAS
   ============================ */

if ($isWindows) {

    // RAM
    preg_match("/=(\d+)/", shell_exec("wmic computersystem get TotalPhysicalMemory /value 2>NUL"), $t);
    preg_match("/=(\d+)/", shell_exec("wmic os get FreePhysicalMemory /value 2>NUL"), $f);

    $ramTotal = isset($t[1]) ? round($t[1] / 1024 / 1024 / 1024, 2) : 0;
    $ramLibre = isset($f[1]) ? round($f[1] / 1024 / 1024, 2) : 0;
    $ramUsada = $ramTotal - ($ramLibre / 1024);
    $ramPorcentaje = $ramTotal > 0 ? round(($ramUsada / $ramTotal) * 100, 1) : 0;

    // CPU
    preg_match("/=(\d+)/", shell_exec("wmic cpu get LoadPercentage /value 2>NUL"), $c);
    $cpuPorcentaje = $c[1] ?? 0;

    // GPU (si no existe → 0)
    $gpuUso = 0;
    $gpuTemp = 0;

    // Disco
    $disk = disk_free_space("C:") / disk_total_space("C:");
    $diskPorcentaje = round((1 - $disk) * 100, 1);

    // Red simulada
    $redTotal = rand(10, 200);

} else {

    // RAM
    $meminfo = @file_get_contents("/proc/meminfo");
    preg_match("/MemTotal:\s+(\d+)/", $meminfo, $totalMem);
    preg_match("/MemAvailable:\s+(\d+)/", $meminfo, $freeMem);

    $ramTotal = round(($totalMem[1] ?? 1) / 1024 / 1024, 2);
    $ramLibre = round(($freeMem[1] ?? 1) / 1024 / 1024, 2);
    $ramUsada = $ramTotal - $ramLibre;
    $ramPorcentaje = round(($ramUsada / $ramTotal) * 100, 1);

    // CPU
    $cpuLoad = sys_getloadavg()[0] ?? 0;
    $cpuCores = trim(shell_exec("nproc 2>/dev/null")) ?: 1;
    $cpuPorcentaje = round(($cpuLoad / $cpuCores) * 100, 1);

    // GPU (si no existe → 0)
    $gpuUso = 0;
    $gpuTemp = 0;

    // Disco
    $disk = disk_free_space("/") / disk_total_space("/");
    $diskPorcentaje = round((1 - $disk) * 100, 1);

    // Red (interfaz automática)
    $iface = trim(shell_exec("ls /sys/class/net | head -n 1"));
    $rx1 = @file_get_contents("/sys/class/net/$iface/statistics/rx_bytes");
    $tx1 = @file_get_contents("/sys/class/net/$iface/statistics/tx_bytes");
    usleep(500000);
    $rx2 = @file_get_contents("/sys/class/net/$iface/statistics/rx_bytes");
    $tx2 = @file_get_contents("/sys/class/net/$iface/statistics/tx_bytes");

    $redTotal = round((($rx2 - $rx1) + ($tx2 - $tx1)) / 1024, 1);
}

/* ============================
   RESPUESTA JSON
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
        "temperatura" => null
    ],
    "gpu" => [
        "porcentaje" => $gpuUso,
        "temperatura" => $gpuTemp
    ],
    "disco" => [
        "porcentaje" => $diskPorcentaje
    ],
    "red" => [
        "total" => $redTotal
    ]
]);
