function accion(tipo) {

    mostrarLoader();

    fetch("/TFG/contenedores/ubuntu/api/actions.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ tipo, nombre: servidorNombre })
    })
    .then(r => r.json())
    .then(res => {

        ocultarLoader();

        if (res.status === "success") {
            mostrarAlertaOK(res.message, () => location.reload());
        } else {
            mostrarAlertaError(res.message);
        }
    })
    .catch(() => {
        ocultarLoader();
        mostrarAlertaError("No se pudo conectar con la API");
    });
}
