// ===============================
//   VARIABLES DESDE PHP
// ===============================
const servidorNombre = NOMBRE_SERVIDOR;
const servidorId = ID_CONTENEDOR;
const puertoActual = PUERTO_ACTUAL;


// ===============================
//   CAMBIO DE PESTAÑAS
// ===============================
function openTab(event, tabName) {

    // Ocultar todos los contenidos
    document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

    // Desactivar todos los botones
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));

    // Activar el contenido correcto
    const tab = document.getElementById(tabName);
    if (tab) tab.classList.add("active");

    // Activar el botón correcto
    if (event && event.target) {
        event.target.classList.add("active");
    }
}


// ===============================
//   ACCIONES DEL SERVIDOR
// ===============================
function accion(tipo) {

    mostrarLoader();

    fetch("/TFG/contenedores/minecraft/api/actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ tipo, nombre: servidorNombre })
    })
    .then(r => r.json())
    .then(res => {

        ocultarLoader();

        if (tipo === "delete" && res.status === "success") {
            mostrarAlerta(res.message, "Aviso", () => {
                location.href = "/TFG/panel.php";
            });
        } else {
            mostrarAlerta(res.message, "Aviso", () => {
                location.reload();
            });
        }
    })
    .catch(err => {
        ocultarLoader();
        console.error(err);
        mostrarAlertaError("No se pudo conectar con la API");
    });
}


// ===============================
//   GUARDAR CAMBIOS
// ===============================
function guardarCambios() {

    mostrarLoader();

    fetch("api/edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id: servidorId,
            nombreActual: servidorNombre,
            nuevoNombre: document.getElementById("nuevoNombre").value,
            nuevaVersion: document.getElementById("nuevaVersion").value,
            nuevoTipo: document.getElementById("nuevoTipo").value,
            nuevoPuerto: document.getElementById("nuevoPuerto").value,
            puertoActual: puertoActual
        })
    })
    .then(r => r.json())
    .then(res => {

        ocultarLoader();

        if (res.status === "success") {
            mostrarAlertaOK(res.message, () => {
                location.href = "/TFG/panel.php";
            });
        } else {
            mostrarAlertaError(res.message);
        }
    })
    .catch(err => {
        ocultarLoader();
        mostrarAlertaError("Error al conectar con la API");
    });
}


// ===============================
//   CONSOLA
// ===============================
let consolaInterval = null;

function abrirConsola() {
    const consola = document.getElementById("consolaBox");
    const cmdBox = document.getElementById("cmdBox");

    consola.style.display = "block";
    cmdBox.style.display = "flex";

    consola.innerHTML = "Cargando logs...\n";

    if (consolaInterval) clearInterval(consolaInterval);

    consolaInterval = setInterval(() => {
        fetch(`/TFG/contenedores/minecraft/api/console.php?nombre=${servidorNombre}`)
            .then(r => r.json())
            .then(res => {
                if (res.status === "success") {
                    consola.innerText = res.logs;
                    consola.scrollTop = consola.scrollHeight;
                }
            });
    }, 1000);
}

function cerrarConsola() {
    const consola = document.getElementById("consolaBox");
    const cmdBox = document.getElementById("cmdBox");

    consola.style.display = "none";
    cmdBox.style.display = "none";

    if (consolaInterval) {
        clearInterval(consolaInterval);
        consolaInterval = null;
    }
}


// ===============================
//   ENVIAR COMANDO
// ===============================
function enviarComando() {
    const cmd = document.getElementById("cmdInput").value.trim();
    if (!cmd) return;

    fetch("/TFG/contenedores/minecraft/api/command.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nombre: servidorNombre,
            cmd: cmd
        })
    })
    .then(r => r.json())
    .then(res => {
        mostrarAlerta(res.status === "success" ? "Comando ejecutado" : "Error: " + res.message);
    });

    document.getElementById("cmdInput").value = "";
}


// ===============================
//   RAM
// ===============================
function actualizarRAM() {
    fetch(`/TFG/contenedores/minecraft/api/stats.php?nombre=${servidorNombre}`)
        .then(r => r.json())
        .then(data => {
            if (data.status !== "success") return;

            let used = data.used.replace("MiB", "").replace("GiB", "");
            let total = data.total.replace("MiB", "").replace("GiB", "");

            if (data.used.includes("GiB")) used = used * 1024;
            if (data.total.includes("GiB")) total = total * 1024;

            let porcentaje = (used / total) * 100;

            document.getElementById("ramUso").innerText =
                `${Math.round(used)} MB / ${Math.round(total)} MB`;

            document.getElementById("ramBar").style.width = porcentaje + "%";
        });
}

setInterval(actualizarRAM, 2000);
actualizarRAM();


// ===============================
//   SLIDER RAM
// ===============================
document.getElementById("ramSlider").addEventListener("input", e => {
    document.getElementById("ramValor").innerText = e.target.value;
});


// ===============================
//   GUARDAR RAM
// ===============================
function guardarRAM() {

    mostrarLoader();

    let ram = document.getElementById("ramSlider").value;

    fetch("api/edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id: servidorId,
            nombreActual: servidorNombre,
            nuevoNombre: document.getElementById("nuevoNombre").value,
            nuevaVersion: document.getElementById("nuevaVersion").value,
            nuevoTipo: document.getElementById("nuevoTipo").value,
            nuevoPuerto: document.getElementById("nuevoPuerto").value,
            puertoActual: puertoActual,
            nuevaRAM: ram
        })
    })
    .then(r => r.json())
    .then(res => {

        ocultarLoader();

        if (res.status === "success") {
            mostrarAlertaOK(res.message, () => {
                location.reload();
            });
        } else {
            mostrarAlertaError(res.message);
        }
    })
    .catch(err => {
        ocultarLoader();
        mostrarAlertaError("Error al conectar con la API");
    });
}
