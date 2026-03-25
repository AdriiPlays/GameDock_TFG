<!-- ALERTA GLOBAL -->
<div id="alerta" style="
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 99999;
">
    <div id="alerta-box" style="
        background: #1e1e1e;
        padding: 25px 35px;
        border-radius: 10px;
        color: white;
        font-family: Arial;
        min-width: 300px;
        max-width: 500px;
        text-align: center;
        box-shadow: 0 0 20px rgba(0,0,0,0.5);
        transform: scale(0.8);
        opacity: 0;
        transition: 0.2s ease;
    ">
        <h3 id="alerta-titulo" style="margin-top: 0; margin-bottom: 10px;">Aviso</h3>
        <p id="alerta-mensaje" style="margin-bottom: 20px;">Mensaje</p>
        <button onclick="cerrarAlerta()" style="
            padding: 8px 20px;
            background: #4da3ff;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        ">Aceptar</button>
    </div>
</div>

<script>
let alertaCallback = null;

// Mostrar alerta con callback opcional
function mostrarAlerta(mensaje, titulo = "Aviso", callback = null) {
    alertaCallback = callback;

    document.getElementById("alerta-mensaje").innerText = mensaje;
    document.getElementById("alerta-titulo").innerText = titulo;

    const alerta = document.getElementById("alerta");
    const box = document.getElementById("alerta-box");

    alerta.style.display = "flex";

    setTimeout(() => {
        box.style.opacity = "1";
        box.style.transform = "scale(1)";
    }, 10);
}

// Atajos
function mostrarAlertaError(mensaje, callback = null) {
    mostrarAlerta(mensaje, "❌ Error", callback);
}

function mostrarAlertaOK(mensaje, callback = null) {
    mostrarAlerta(mensaje, "✅ Correcto", callback);
}

// Cerrar alerta
function cerrarAlerta() {
    const alerta = document.getElementById("alerta");
    const box = document.getElementById("alerta-box");

    box.style.opacity = "0";
    box.style.transform = "scale(0.8)";

    setTimeout(() => {
        alerta.style.display = "none";

        if (alertaCallback) {
            alertaCallback();
            alertaCallback = null;
        }

    }, 150);
}

window.alert = function(mensaje) {
    mostrarAlerta(mensaje);
};


function mostrarConfirmacion(mensaje, callbackAceptar, callbackCancelar = null) {
    document.getElementById("alerta-mensaje").innerText = mensaje;
    document.getElementById("alerta-titulo").innerText = "Confirmar acción";

    const alerta = document.getElementById("alerta");
    const box = document.getElementById("alerta-box");

    // Cambiar botón
    box.innerHTML = `
        <h3 id="alerta-titulo">Confirmar acción</h3>
        <p id="alerta-mensaje">${mensaje}</p>
        <button id="btnAceptar" style="
            padding: 8px 20px;
            background: #4da3ff;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            margin-right: 10px;
        ">Aceptar</button>

        <button id="btnCancelar" style="
            padding: 8px 20px;
            background: #e40606;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        ">Cancelar</button>
    `;

    alerta.style.display = "flex";

    setTimeout(() => {
        box.style.opacity = "1";
        box.style.transform = "scale(1)";
    }, 10);

    document.getElementById("btnAceptar").onclick = () => {
        alerta.style.display = "none";
        callbackAceptar();
    };

    document.getElementById("btnCancelar").onclick = () => {
        alerta.style.display = "none";
        if (callbackCancelar) callbackCancelar();
    };
}

</script>
