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

            <!-- TARJETA DE VERSIÓN ACTUAL -->
            <div class="card version-card">
                <div class="card-header">
                    <h2>Versión Actual</h2>
                </div>
                <div class="version-container">
                    <div class="version-display"><?= $versionLocal ?></div>
                    <div class="version-details">
                        <p>Última actualización</p>
                        <span class="version-date"><?= $fechaActualizacion ?></span>
                    </div>
                </div>
                <div class="system-status">
                    <div class="status-item">
                        <span class="status-label">Estado del Sistema</span>
                        <span class="status-badge active">Activo</span>
                    </div>
                </div>
                <button id="btnCheck" class="btn-primary">
                    Buscar Actualizaciones
                </button>
            </div>

            <!-- SECCIÓN DE RESULTADO -->
            <div id="resultado" class="resultado"></div>

            <!-- TARJETA DE CARACTERÍSTICAS -->
            <div class="card features-card">
                <div class="card-header">
                    <h2>Características del Sistema</h2>
                </div>
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <div class="feature-content">
                            <h4>Respaldos Automáticos</h4>
                            <p>Protección automática de tus datos</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <div class="feature-content">
                            <h4>Historial de Cambios</h4>
                            <p>Seguimiento completo de actualizaciones</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <div class="feature-content">
                            <h4>Soporte Técnico 24/7</h4>
                            <p>Asistencia continua disponible</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HISTORIAL DE ACTUALIZACIONES -->
            <div class="card changelog-card">
                <div class="card-header">
                    <h2>Historial de Actualizaciones</h2>
                </div>

                <div class="changelog-item current">
                    <div class="changelog-header">
                        <h3>v<?= $versionLocal ?></h3>
                        <span class="version-badge current">Versión Actual</span>
                    </div>
                    <div class="changelog-body">
                        <p>Versión estable en producción</p>
                    </div>
                </div>

                <div class="changelog-item">
                    <div class="changelog-header">
                        <h3>Última Actualización</h3>
                    </div>
                    <div class="changelog-body">
                        <ul>
                            <li>Mejoras de rendimiento del sistema</li>
                            <li>Correcciones de seguridad</li>
                            <li>Interfaz mejorada</li>
                        </ul>
                    </div>
                </div>

                <div class="changelog-item upcoming">
                    <div class="changelog-header">
                        <h3>Próximas Mejoras</h3>
                        <span class="version-badge upcoming">En Desarrollo</span>
                    </div>
                    <div class="changelog-body">
                        <ul>
                            <li>Panel más intuitivo</li>
                            <li>Nuevas funcionalidades</li>
                            <li>Optimización de base de datos</li>
                        </ul>
                    </div>
                </div>
            </div>

        </main>

        <footer class="footer">
            <p><strong>GameDock</strong> — Todos los derechos reservados © <?= date("Y") ?></p>
        </footer>

    </div>

    <!-- MODAL DE ACTUALIZACIÓN -->
    <div id="updateModal" class="update-modal">
        <div class="update-box">
            <h2 id="modal-title">Preparando actualización</h2>

            <div class="progress-container">
                <div id="progress-bar" class="progress-bar"></div>
                <span id="progress-text" class="progress-text">0%</span>
            </div>

            <p id="progress-status" class="progress-status">Iniciando proceso...</p>
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
                            <div class="card alert-card">
                                <div class="alert alert-success">
                                    <div class="alert-header">
                                        <h3>Sistema Actualizado</h3>
                                    </div>
                                    <p>Tu panel está ejecutando la última versión disponible.</p>
                                </div>
                            </div>
                        `;
                    } else if (res.estado === "disponible") {
                        box.innerHTML = `
                            <div class="card alert-card">
                                <div class="alert alert-info">
                                    <div class="alert-header">
                                        <h3>Nueva Versión Disponible</h3>
                                    </div>
                                    <p>Versión <strong>${res.version}</strong> está lista para instalar.</p>
                                    <div class="alert-actions">
                                        <button onclick="actualizar()" class="btn-primary">
                                            Actualizar Ahora
                                        </button>
                                        <button onclick="document.getElementById('resultado').innerHTML = '';" class="btn-secondary">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        box.innerHTML = `
                            <div class="card alert-card">
                                <div class="alert alert-error">
                                    <div class="alert-header">
                                        <h3>Error al Verificar</h3>
                                    </div>
                                    <p>${res.mensaje || 'No se pudo verificar las actualizaciones.'}</p>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    document.getElementById("resultado").innerHTML = `
                        <div class="card alert-card">
                            <div class="alert alert-error">
                                <div class="alert-header">
                                    <h3>Error de Conexión</h3>
                                </div>
                                <p>No se pudo conectar con el servidor.</p>
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
            status.innerText = "Preparando...";
            title.innerText = "Preparando actualización";

            let progreso = 0;

            function actualizarTexto(p) {
                if (p < 20) {
                    title.innerText = "Preparando actualización";
                    status.innerText = "Creando respaldo de seguridad...";
                }
                else if (p < 50) {
                    title.innerText = "Descargando";
                    status.innerText = "Descargando actualización...";
                }
                else if (p < 80) {
                    title.innerText = "Instalando";
                    status.innerText = "Instalando componentes...";
                }
                else if (p < 100) {
                    title.innerText = "Finalizando";
                    status.innerText = "Aplicando cambios finales...";
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
                    title.innerText = "Actualización Completada";
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
                    title.innerText = "Error en la Actualización";
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
