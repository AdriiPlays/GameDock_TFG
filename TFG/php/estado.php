<?php

/* bibliografia 

https://www.chartjs.org/docs/latest/
https://www.php.net/manual/en/
https://www.kernel.org/doc/html/latest/filesystems/proc.html
https://learn.microsoft.com/en-us/windows/win32/wmisdk/wmic

*/
session_start();
require_once "../config.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: ../Index.php");
    exit;
}

$tituloPagina = "Estado del Servidor";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>

    <link rel="stylesheet" href="../css/panel.css">
    <link rel="stylesheet" href="../css/estado.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include "menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <div id="menu-btn" class="menu-btn">☰</div>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido">

<h2>📊 Estado del Servidor</h2>

<div class="cuadricula-estado">

    <div class="tarjeta-estado">
        <h3>RAM</h3>
        <div class="valor-porcentaje" id="ramValor">0%</div>
        <canvas id="ramChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>CPU</h3>
        <div class="valor-porcentaje" id="cpuValor">0%</div>
        <canvas id="cpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>GPU</h3>
        <div class="valor-porcentaje" id="gpuValor">0%</div>
        <canvas id="gpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>Temperatura CPU</h3>
        <div class="valor-porcentaje" id="tempCpuValor">0°C</div>
        <canvas id="tempCpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>Temperatura GPU</h3>
        <div class="valor-porcentaje" id="tempGpuValor">0°C</div>
        <canvas id="tempGpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>Disco</h3>
        <div class="valor-porcentaje" id="discoValor">0%</div>
        <canvas id="diskChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>Red</h3>
        <div class="valor-porcentaje" id="redValor">0 KB/s</div>
        <canvas id="netChart"></canvas>
    </div>

</div>

</main>

<script>
// Crear gráficas
function crearGrafica(id, etiqueta, color) {
    return new Chart(document.getElementById(id), {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                label: etiqueta,
                data: [],
                borderColor: color,
                borderWidth: 2,
                fill: false,
                tension: 0.2
            }]
        },
        options: {
            animation: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

let ramChart = crearGrafica("ramChart", "RAM %", "blue");
let cpuChart = crearGrafica("cpuChart", "CPU %", "red");
let gpuChart = crearGrafica("gpuChart", "GPU %", "green");
let tempCpuChart = crearGrafica("tempCpuChart", "Temp CPU", "orange");
let tempGpuChart = crearGrafica("tempGpuChart", "Temp GPU", "purple");
let diskChart = crearGrafica("diskChart", "Disco %", "brown");
let netChart = crearGrafica("netChart", "Red KB/s", "teal");

setInterval(() => {
    fetch("../api/estado.php")
        .then(r => r.json())
        .then(data => {

            function actualizarGrafica(chart, valor) {
                chart.data.labels.push("");
                chart.data.datasets[0].data.push(valor);

                if (chart.data.labels.length > 20) {
                    chart.data.labels.shift();
                    chart.data.datasets[0].data.shift();
                }

                chart.update();
            }

            actualizarGrafica(ramChart, data.ram.porcentaje);
            actualizarGrafica(cpuChart, data.cpu.porcentaje);
            actualizarGrafica(gpuChart, data.gpu.porcentaje || 0);
            actualizarGrafica(tempCpuChart, data.cpu.temperatura || 0);
            actualizarGrafica(tempGpuChart, data.gpu.temperatura || 0);
            actualizarGrafica(diskChart, data.disco.porcentaje);
            actualizarGrafica(netChart, data.red.total);

            document.getElementById("ramValor").innerText = data.ram.porcentaje + "%";
            document.getElementById("cpuValor").innerText = data.cpu.porcentaje + "%";
            document.getElementById("gpuValor").innerText = (data.gpu.porcentaje || 0) + "%";
            document.getElementById("tempCpuValor").innerText = (data.cpu.temperatura || 0) + "°C";
            document.getElementById("tempGpuValor").innerText = (data.gpu.temperatura || 0) + "°C";
            document.getElementById("discoValor").innerText = data.disco.porcentaje + "%";
            document.getElementById("redValor").innerText = data.red.total + " KB/s";
        });
}, 2000);
</script>
<script src="/TFG/JS/panel.js"></script>
</body>
</html>
