// -----------------------------
// LISTAR MODS
// -----------------------------
function cargarMods() {
    fetch("Api/minecraft/mods/list.php?nombre=" + window.nombreContenedor)
        .then(r => r.json())
        .then(res => {
            const ul = document.getElementById("listaMods");
            ul.innerHTML = "";

            if (!res.mods || res.mods.length === 0) {
                ul.innerHTML = "<li>No hay mods instalados.</li>";
                return;
            }

            res.mods.forEach(mod => {
                ul.innerHTML += `
                    <li style="margin-bottom:8px;">
                        <strong>${mod}</strong>
                        <button onclick="borrarMod('${mod}')" 
                                style="margin-left:10px; background:#dc2626; color:white; border:none; padding:4px 8px; border-radius:5px; cursor:pointer;">
                            Eliminar
                        </button>
                    </li>
                `;
            });
        });
}

// -----------------------------
// SUBIR MOD
// -----------------------------
function subirMod() {
    const file = document.getElementById("modFile").files[0];
    if (!file) return alert("Selecciona un archivo .jar");

    const formData = new FormData();
    formData.append("mod", file);
    formData.append("nombre", window.nombreContenedor);

    fetch("Api/minecraft/mods/upload.php", {
        method: "POST",
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        cargarMods();
    });
}

// -----------------------------
// BORRAR MOD
// -----------------------------
function borrarMod(mod) {
    if (!confirm("¿Seguro que quieres eliminar este mod?")) return;

    fetch("Api/minecraft/mods/delete.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "nombre=" + window.nombreContenedor + "&mod=" + encodeURIComponent(mod)
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        cargarMods();
    });
}

// Inicializar
document.addEventListener("DOMContentLoaded", cargarMods);
