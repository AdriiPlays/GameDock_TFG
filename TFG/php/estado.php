<?php

/* bibliografia 

https://www.chartjs.org/docs/latest/
https://www.php.net/manual/en/
https://www.kernel.org/doc/html/latest/filesystems/proc.html
https://learn.microsoft.com/en-us/windows/win32/wmisdk/wmic

*/
require_once "../Funciones/Sesion.php";

$tituloPagina = "Estado del Servidor";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $tituloPagina ?></title>

      <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
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

<h2>Estado del Servidor</h2>


<div class="tarjeta-sistema">
    <div class="sistema-header">
        <img id="iconoSO" class="icono-so" src="" alt="SO">
        <h3>Información del Sistema</h3>
    </div>

    <div class="sistema-info">
        <p><strong>Sistema Operativo:</strong> <span id="soNombre"></span></p>
        <p><strong>Kernel:</strong> <span id="soKernel"></span></p>
        <p><strong>Arquitectura:</strong> <span id="soArq"></span></p>
        <p><strong>CPU:</strong> <span id="soCpu"></span></p>
        <p><strong>RAM Total:</strong> <span id="soRam"></span></p>
        <p><strong>Disco Total:</strong> <span id="soDisco"></span></p>
        <p><strong>IP del Servidor:</strong> <span id="soIp"></span></p>
    </div>
</div>

<!-- DIV DE CUADRICULAS -->
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


 // Imagenes de los sistemas
function detectarIconoSO(nombre) {
    nombre = nombre.toLowerCase();

    if (nombre.includes("ubuntu")) return "/TFG/img/os/ubuntu.png";
    if (nombre.includes("windows")) return "/TFG/img/os/windows.png";

    return "/TFG/img/os/linux.png";
}


function cargarInfoSistema() {
    fetch("../api/estado.php")
        .then(r => r.json())
        .then(data => {

            
            // Información del sistema 
           
            document.getElementById("soNombre").innerText = data.sistema.so;
            document.getElementById("soKernel").innerText = data.sistema.kernel;
            document.getElementById("soArq").innerText = data.sistema.arquitectura;
            document.getElementById("soCpu").innerText = data.sistema.cpu;
            document.getElementById("soRam").innerText = data.sistema.ram_total;
            document.getElementById("soDisco").innerText = data.sistema.disco_total;
            document.getElementById("soIp").innerText = data.sistema.ip;

            // Colocar icono 
            document.getElementById("iconoSO").src = detectarIconoSO(data.sistema.so);

          
            // Gráficas 
         
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
}


setInterval(cargarInfoSistema, 2000);


cargarInfoSistema();
</script>

<script src="/TFG/JS/panel.js"></script>
</body>
</html>
