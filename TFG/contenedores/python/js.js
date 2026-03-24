function openTab(tab) {
    document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
    document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

    document.querySelector(`[onclick="openTab('${tab}')"]`).classList.add("active");
    document.getElementById(tab).classList.add("active");
}

// ACCIONES DEL SERVIDOR
function accion(tipo) {
    fetch("/TFG/contenedores/python/api/actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ tipo, nombre: servidorNombre })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (tipo === "delete" && res.status === "success") {
            location.href = "/TFG/panel.php";
        } else {
            location.reload();
        }
    });
}

function guardarCambios() {
    fetch("api/edit.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id: servidorId,
            nombreActual: servidorNombre,
            nuevoNombre: document.getElementById("nuevoNombre").value,
            nuevaVersion: document.getElementById("nuevaVersion")?.value || "",
            nuevoTipo: document.getElementById("nuevoTipo")?.value || "",
            nuevoPuerto: document.getElementById("nuevoPuerto").value,
            puertoActual: puertoActual
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.status === "success") {
            location.href = "/TFG/panel.php";
        }
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
        fetch("/TFG/contenedores/python/api/console.php?nombre=" + servidorNombre)
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

    fetch("/TFG/contenedores/python/api/command.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nombre: servidorNombre,
            cmd: cmd
        })
    })
    .then(r => r.json())
    .then(res => {
        alert(res.status === "success" ? "Comando ejecutado" : "Error: " + res.message);
    });

    document.getElementById("cmdInput").value = "";
}




function instalarDependencias() {
    const deps = document.getElementById("nuevasDependencias").value;

    fetch("api/instalar_dependencias.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            nombre: servidorNombre,
            dependencias: deps
        })
    })
    .then(r => r.json())
    .then(d => {
        alert(d.message);
        cargarDependencias();
    });
}

// Cargar dependencias al abrir la pestaña
document.querySelector(`[onclick="openTab('dependencias')"]`)
    .addEventListener("click", cargarDependencias);
