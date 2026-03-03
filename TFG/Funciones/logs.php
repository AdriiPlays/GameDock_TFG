<?php
function registrarLog($conn, $usuario, $accion) {
    $stmt = $conn->prepare("INSERT INTO logs (usuario, accion) VALUES (?, ?)");
    $stmt->bind_param("ss", $usuario, $accion);
    $stmt->execute();
    $stmt->close();
}
