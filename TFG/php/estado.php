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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title><?= $tituloPagina ?></title>
    <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
    <link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>/estado-<?= $temaUsuario ?>.css">
   

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/png" href="img/iconogrande.png">

    <style>
        /* Mejoras de responsive inline para Chart.js */
        .tarjeta-estado canvas {
            max-width: 100%;
        }
    </style>
</head>
<body>

<?php include "menu.php"; ?>

<div class="main-content" id="main">

<header class="header">
    <button id="menu-btn" class="menu-btn">☰</button>
    <h1><?= $tituloPagina ?></h1>
</header>

<main class="contenido" style="max-width: 1400px; margin: 0 auto; padding: 30px 20px; overflow-x: hidden;">



<!-- TARJETA DE INFORMACIÓN DEL SISTEMA -->
<div class="tarjeta-sistema">
    <div class="sistema-header">
        <img id="iconoSO" class="icono-so" src="" alt="SO">
        <h3>Información del Sistema</h3>
    </div>

    <div class="sistema-info">
        <div>
            <p><strong>Sistema Operativo:</strong></p>
            <p id="soNombre" style="margin-top: 2px;">--</p>
        </div>
        <div>
            <p><strong>Kernel:</strong></p>
            <p id="soKernel" style="margin-top: 2px;">--</p>
        </div>
        <div>
            <p><strong>Arquitectura:</strong></p>
            <p id="soArq" style="margin-top: 2px;">--</p>
        </div>
        <div>
            <p><strong>CPU:</strong></p>
            <p id="soCpu" style="margin-top: 2px;">--</p>
        </div>
        <div>
            <p><strong>RAM Total:</strong></p>
            <p id="soRam" style="margin-top: 2px;">--</p>
        </div>
        <div>
            <p><strong>Disco Total:</strong></p>
            <p id="soDisco" style="margin-top: 2px;">--</p>
        </div>
        <div>
            <p><strong>IP del Servidor:</strong></p>
            <p id="soIp" style="margin-top: 2px;">--</p>
        </div>
    </div>
</div>

<!-- TARJETAS DE ESTADO -->
<div class="cuadricula-estado">

    <div class="tarjeta-estado">
        <h3>🧠 RAM</h3>
        <div class="valor-porcentaje" id="ramValor">0%</div>
        <canvas id="ramChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>⚙️ CPU</h3>
        <div class="valor-porcentaje" id="cpuValor">0%</div>
        <canvas id="cpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>🎮 GPU</h3>
        <div class="valor-porcentaje" id="gpuValor">0%</div>
        <canvas id="gpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>🌡️ Temp CPU</h3>
        <div class="valor-porcentaje" id="tempCpuValor">0°C</div>
        <canvas id="tempCpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>🌡️ Temp GPU</h3>
        <div class="valor-porcentaje" id="tempGpuValor">0°C</div>
        <canvas id="tempGpuChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>💾 Disco</h3>
        <div class="valor-porcentaje" id="discoValor">0%</div>
        <canvas id="diskChart"></canvas>
    </div>

    <div class="tarjeta-estado">
        <h3>🌐 Red</h3>
        <div class="valor-porcentaje" id="redValor">0 KB/s</div>
        <canvas id="netChart"></canvas>
    </div>

</div>

</main>

</div>

<script>
// Configuración global de Chart.js 
Chart.defaults.font.family = "'Segoe UI', sans-serif";
Chart.defaults.color = '#8b949e';

// Función para crear gráficas 
function crearGrafica(id, etiqueta, color) {
    const ctx = document.getElementById(id);
    return new Chart(ctx, {
        type: "line",
        data: {
            labels: [],
            datasets: [{
                label: etiqueta,
                data: [],
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointBackgroundColor: color,
                pointBorderColor: '#161b22',
                pointBorderWidth: 2,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 10,
                    titleColor: '#fff',
                    bodyColor: color,
                    borderColor: color,
                    borderWidth: 1,
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: etiqueta.includes('Temp') ? 100 : 
                         etiqueta.includes('Red') ? 10000 : 100,
                    grid: {
                        color: 'rgba(201, 209, 217, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#8b949e',
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        display: false
                    }
                }
            }
        }
    });
}

// Inicializar gráficas
let ramChart = crearGrafica("ramChart", "RAM %", "#3b82f6");
let cpuChart = crearGrafica("cpuChart", "CPU %", "#ef4444");
let gpuChart = crearGrafica("gpuChart", "GPU %", "#10b981");
let tempCpuChart = crearGrafica("tempCpuChart", "Temp CPU", "#f59e0b");
let tempGpuChart = crearGrafica("tempGpuChart", "Temp GPU", "#a855f7");
let diskChart = crearGrafica("diskChart", "Disco %", "#8b5cf6");
let netChart = crearGrafica("netChart", "Red KB/s", "#06b6d4");

// Función para detectar el icono del SO
function detectarIconoSO(nombre) {
    nombre = nombre.toLowerCase();

    if (nombre.includes("ubuntu")) return "/TFG/img/os/ubuntu.png";
    if (nombre.includes("windows")) return "/TFG/img/os/windows.png";

    return "/TFG/img/os/linux.png";
}

// Función para cargar la información del sistema
function cargarInfoSistema() {
    fetch("../api/estado.php")
        .then(r => r.json())
        .then(data => {
            // Información del sistema 
            document.getElementById("soNombre").innerText = data.sistema.so || '--';
            document.getElementById("soKernel").innerText = data.sistema.kernel || '--';
            document.getElementById("soArq").innerText = data.sistema.arquitectura || '--';
            document.getElementById("soCpu").innerText = data.sistema.cpu || '--';
            document.getElementById("soRam").innerText = data.sistema.ram_total || '--';
            document.getElementById("soDisco").innerText = data.sistema.disco_total || '--';
            document.getElementById("soIp").innerText = data.sistema.ip || '--';

            // Colocar icono del SO
            document.getElementById("iconoSO").src = detectarIconoSO(data.sistema.so);

            // Función para actualizar gráficas
            function actualizarGrafica(chart, valor) {
                chart.data.labels.push("");
                chart.data.datasets[0].data.push(valor);

                
                if (chart.data.labels.length > 20) {
                    chart.data.labels.shift();
                    chart.data.datasets[0].data.shift();
                }

                chart.update();
            }

            // Actualizar todas las gráficas
            actualizarGrafica(ramChart, data.ram.porcentaje);
            actualizarGrafica(cpuChart, data.cpu.porcentaje);
            actualizarGrafica(gpuChart, data.gpu.porcentaje || 0);
            actualizarGrafica(tempCpuChart, data.cpu.temperatura || 0);
            actualizarGrafica(tempGpuChart, data.gpu.temperatura || 0);
            actualizarGrafica(diskChart, data.disco.porcentaje);
            actualizarGrafica(netChart, Math.min(data.red.total, 10000)); // Limitar a 10000 para la escala

            // Actualizar valores mostrados
            document.getElementById("ramValor").innerText = data.ram.porcentaje + "%";
            document.getElementById("cpuValor").innerText = data.cpu.porcentaje + "%";
            document.getElementById("gpuValor").innerText = (data.gpu.porcentaje || 0) + "%";
            document.getElementById("tempCpuValor").innerText = (data.cpu.temperatura || 0) + "°C";
            document.getElementById("tempGpuValor").innerText = (data.gpu.temperatura || 0) + "°C";
            document.getElementById("discoValor").innerText = data.disco.porcentaje + "%";
            document.getElementById("redValor").innerText = data.red.total + " KB/s";
        })
        .catch(err => console.error("Error cargando datos del sistema:", err));
}

// Cargar datos cada 2 segundos
setInterval(cargarInfoSistema, 2000);

// Cargar datos iniciales
cargarInfoSistema();

// Reajustar gráficas al cambiar el tamaño de la ventana
window.addEventListener('resize', () => {
    ramChart.resize();
    cpuChart.resize();
    gpuChart.resize();
    tempCpuChart.resize();
    tempGpuChart.resize();
    diskChart.resize();
    netChart.resize();
});
</script>

<script src="/TFG/JS/panel.js"></script>
</body>
</html>
