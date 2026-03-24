<!-- LOADER GLOBAL -->
<div id="loader" style="
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.75);
    display: none;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    z-index: 9999;
    color: white;
    font-family: Arial;
">

    <!-- SPINNER -->
    <div class="spinner"></div>

    <!-- TEXTO + PUNTITOS -->
    <div style="margin-top: 20px; font-size: 22px;">
        Creando servidor<span class="dots"></span>
    </div>

</div>

<style>
/* Spinner azul animado */
.spinner {
    width: 70px;
    height: 70px;
    border: 8px solid rgba(255,255,255,0.2);
    border-top: 8px solid #4da3ff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Animación del spinner */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Animación de los 3 puntitos */
.dots::after {
    content: '';
    animation: dots 1.5s steps(3, end) infinite;
}

@keyframes dots {
    0%   { content: ''; }
    33%  { content: '.'; }
    66%  { content: '..'; }
    100% { content: '...'; }
}
</style>

<script>
// Funciones globales para mostrar/ocultar loader
function mostrarLoader() {
    document.getElementById("loader").style.display = "flex";
}

function ocultarLoader() {
    document.getElementById("loader").style.display = "none";
}
</script>
