<?php
require_once "Funciones/Sesion.php";

$tituloPagina = "Centro de Actualizaciones";
$versionLocal = trim(file_get_contents("version.txt"));
$fechaActualizacion = date("d/m/Y H:i", filemtime("version.txt"));
$phpVersion = phpversion();
$osInfo = php_uname();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $tituloPagina ?></title>
<link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>.css">
<link rel="stylesheet" href="/TFG/css/temas/<?= $temaUsuario ?>/update-<?= strtolower($temaUsuario) ?>.css">
<link rel="icon" type="image/png" href="img/iconogrande.png">
</head>
<body>
    <?php include __DIR__ . "/php/menu.php"; ?>


<div class="main-content">

    <header class="header">
        <div id="menu-btn" class="menu-btn">☰</div>
        <h1><?= $tituloPagina ?></h1>
    </header>

    <main class="contenido-update">

        <!-- TARJETA DE VERSIÓN -->
        <div class="card version-info">
            <div class="card-title">
                <span class="icon">📦</span>
                Información de la Versión
            </div>
            <div class="version-display"><?= $versionLocal ?></div>
            <div class="version-meta">
                <div>Última actualización: <?= $fechaActualizacion ?></div>
                <div style="margin-top: 10px;">
                    <span class="badge badge-success">✓ Sistema Activo</span>
                </div>
            </div>
            <div class="update-actions">
                <button id="btnCheck" class="btn-save" style="width: 100%; background: rgba(255, 255, 255, 0.2); color: white; border: 2px solid white;">
                    🔍 Buscar actualizaciones
                </button>
            </div>
        </div>
            <!-- SECCIÓN DE RESULTADO -->
    <div id="resultado" class="resultado"></div>
<br>
        <!-- TARJETA DE CARACTERÍSTICAS -->
        <div class="card">
            <div class="card-title">
                <span class="icon">✨</span>
                Características
            </div>
            <div class="features-grid">
               
                <div class="feature-item">
                    <span class="check">✓</span>
                    <span>Respaldos automáticos</span>
                </div>
                <div class="feature-item">
                    <span class="check">✓</span>
                    <span>Historial de cambios</span>
                </div>
                <div class="feature-item">
                    <span class="check">✓</span>
                    <span>Soporte técnico 24/7</span>
                </div>
            </div>
        </div>

    </main>



    
    <div class="changelog">
        <div class="card-title" style="margin-bottom: 20px;">
            <span class="icon">📋</span>
            Historial de Actualizaciones
        </div>

        <div class="changelog-item">
            <div class="changelog-version">v<?= $versionLocal ?> - Versión Actual</div>
            <div class="changelog-content">
                ✓ Versión estable en producción
                <span class="badge badge-success" style="margin-left: 10px;">ACTUAL</span>
            </div>
        </div>

        <div class="changelog-item">
            <div class="changelog-version">Última Actualización</div>
            <div class="changelog-content">
                • Mejoras de rendimiento
                <br>• Correcciones de seguridad
                <br>• Interfaz mejorada
            </div>
        </div>

        <div class="changelog-item">
            <div class="changelog-version">Próximas Mejoras</div>
            <div class="changelog-content">
                • Panel más intuitivo
                <br>• Nuevas funcionalidades
                <br>• Optimización de base de datos
                <span class="badge badge-info" style="margin-left: 10px;">EN DESARROLLO</span>
            </div>
        </div>
    </div>

    <footer class="footer">
        <strong>GameDock</strong> — Todos los derechos reservados © <?= date("Y") ?>
        <div style="margin-top: 10px; font-size: 0.9rem;">
        </div>
    </footer>

</div>

<!-- MODAL DE ACTUALIZACIÓN -->
<div id="updateModal" class="update-modal">
    <div class="update-box">
        <h2 id="modal-title">Preparando…</h2>

        <div class="progress-container">
            <div id="progress-bar" class="progress-bar"></div>
            <span id="progress-text" class="progress-text">0%</span>
        </div>

        <p id="progress-status" class="progress-status">Iniciando…</p>
    </div>
</div>

<audio id="update-sound" src="sounds/update_complete.mp3" preload="auto"></audio>

<script src="JS/panel.js"></script>

<script>

    
document.getElementById("btnCheck").onclick = () => {
    fetch("api/check_update.php")
        .then(r => r.json())
        .then(res => {
            const box = document.getElementById("resultado");

            if (res.estado === "actualizado") {
                box.innerHTML = `
                    <div class="card">
                        <div class="alert ok">
                            ✔️ <strong>Sistema Actualizado</strong><br>
                            Tu panel está ejecutando la última versión disponible.
                        </div>
                    </div>
                `;
            } else if (res.estado === "disponible") {
                box.innerHTML = `
                    <div class="card">
                        <div class="card-title">
                            <span class="icon">🚀</span>
                            Nueva Versión Disponible
                        </div>
                        <p style="margin-bottom: 15px; color: #4a5568;">
                            Versión <strong>${res.version}</strong> está lista para instalar.
                        </p>
                        <button onclick="actualizar()" class="btn-update" style="width: 100%; justify-content: center;">
                            ⬇️ Actualizar Ahora
                        </button>
                        <button onclick="document.getElementById('resultado').innerHTML = '';" class="btn-secondary" style="width: 100%; justify-content: center; margin-top: 10px;">
                            Cancelar
                        </button>
                    </div>
                `;
            } else {
                box.innerHTML = `
                    <div class="card">
                        <div class="alert error">
                            ❌ <strong>Error</strong><br>
                            ${res.mensaje || 'No se pudo verificar las actualizaciones.'}
                        </div>
                    </div>
                `;
            }
        })
        .catch(err => {
            document.getElementById("resultado").innerHTML = `
                <div class="card">
                    <div class="alert error">
                        ❌ <strong>Error de Conexión</strong><br>
                        No se pudo conectar con el servidor.
                    </div>
                </div>
            `;
        });
};

function actualizar() {
    if (!confirm("¿Seguro que quieres actualizar el panel? Se realizará un respaldo antes de actualizar.")) return;

    const modal = document.getElementById("updateModal");
    const bar = document.getElementById("progress-bar");
    const text = document.getElementById("progress-text");
    const status = document.getElementById("progress-status");
    const title = document.getElementById("modal-title");
    const sound = document.getElementById("update-sound");

    modal.style.display = "flex";

    bar.style.width = "0%";
    text.innerText = "0%";
    status.innerText = "Preparando…";
    title.innerText = "Preparando…";

    let progreso = 0;

    function actualizarTexto(p) {
        if (p < 20) {
            title.innerText = "Preparando…";
            status.innerText = "Creando respaldo de seguridad…";
        }
        else if (p < 50) {
            title.innerText = "Descargando…";
            status.innerText = "Descargando actualización…";
        }
        else if (p < 80) {
            title.innerText = "Instalando…";
            status.innerText = "Instalando componentes…";
        }
        else if (p < 100) {
            title.innerText = "Finalizando…";
            status.innerText = "Aplicando cambios finales…";
        }
    }

    const intervalo = setInterval(() => {
        progreso += Math.floor(Math.random() * 10) + 5;

        if (progreso >= 100) {
            progreso = 100;
            clearInterval(intervalo);
        }

        bar.style.width = progreso + "%";
        text.innerText = progreso + "%";
        actualizarTexto(progreso);

    }, 300);

    fetch("api/do_update.php")
        .then(r => r.json())
        .then(res => {

            bar.style.width = "100%";
            text.innerText = "100%";
            title.innerText = "✔️ Completado";
            status.innerText = "Actualización finalizada exitosamente";

            sound.play().catch(() => {});

            setTimeout(() => {
                modal.style.display = "none";
                alert(res.mensaje || "Actualización completada correctamente");
                location.reload();
            }, 1500);
        })
        .catch(err => {
            bar.style.width = "0%";
            title.innerText = "❌ Error";
            status.innerText = "Error durante la actualización";
            alert("Error: " + err.message);
            setTimeout(() => {
                modal.style.display = "none";
            }, 2000);
        });
}
</script>

</body>
</html>
