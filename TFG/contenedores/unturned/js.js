
const servidorNombre = NOMBRE_SERVIDOR;
const servidorId = ID_CONTENEDOR;
const puertoActual = PUERTO_ACTUAL;



function openTab(tab) {
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

    document.querySelector(`[onclick="openTab('${tab}')"]`).classList.add("active");
    document.getElementById(tab).classList.add("active");
}


function accion(tipo) {

    mostrarLoader();

    fetch("/TFG/contenedores/unturned/api/actions.php", {
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
        mostrarAlertaError("No se pudo conectar con la API");
    });
}


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



let consolaInterval = null;

function abrirConsola() {
    const consola = document.getElementById("consolaBox");
    const cmdBox = document.getElementById("cmdBox");

    consola.style.display = "block";
    cmdBox.style.display = "flex";

    consola.innerHTML = "Cargando logs...\n";

    if (consolaInterval) clearInterval(consolaInterval);

    consolaInterval = setInterval(() => {
        fetch(`/TFG/contenedores/unturned/api/console.php?nombre=${servidorNombre}`)
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


function enviarComando() {
    const cmd = document.getElementById("cmdInput").value.trim();
    if (!cmd) return;

    fetch("/TFG/contenedores/unturned/api/command.php", {
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


function actualizarRAM() {
    fetch(`/TFG/contenedores/unturned/api/stats.php?nombre=${servidorNombre}`)
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



document.getElementById("ramSlider").addEventListener("input", e => {
    document.getElementById("ramValor").innerText = e.target.value;
});


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
