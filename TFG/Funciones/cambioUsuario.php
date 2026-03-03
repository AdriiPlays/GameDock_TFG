<?php
session_start();
require_once "../config.php";
require_once "logs.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: ../login.php");
    exit;
}

$usuario = $_SESSION["usuario"];


$stmt = $conn->prepare("SELECT correo, password, imagen FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($correoActual, $passActualBD, $imagenActual);
$stmt->fetch();
$stmt->close();


$nuevoCorreo = $_POST["nuevo_correo"] ?? "";
$passActual = $_POST["pass_actual"] ?? "";
$passNueva = $_POST["pass_nueva"] ?? "";
$passConfirmar = $_POST["pass_confirmar"] ?? "";
$nuevaImagen = $_FILES["nueva_imagen"] ?? null;

if ($nuevoCorreo !== "" && $nuevoCorreo !== $correoActual) {
    $stmt = $conn->prepare("UPDATE usuarios SET correo = ? WHERE usuario = ?");
    $stmt->bind_param("ss", $nuevoCorreo, $usuario);
    $stmt->execute();
    $stmt->close();

    registrarLog($conn, $usuario, "Cambió su correo de '$correoActual' a '$nuevoCorreo'");
}


if ($passActual !== "" && $passNueva !== "" && $passConfirmar !== "") {

    if (!password_verify($passActual, $passActualBD)) {
        header("Location: ../usuario.php?error=pass_incorrecta");
        exit;
    }

    if ($passNueva !== $passConfirmar) {
        header("Location: ../usuario.php?error=pass_no_coinciden");
        exit;
    }

    $passHash = password_hash($passNueva, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE usuarios SET pass = ? WHERE usuario = ?");
    $stmt->bind_param("ss", $passHash, $usuario);
    $stmt->execute();
    $stmt->close();

    registrarLog($conn, $usuario, "Cambió su contraseña");
}


if ($nuevaImagen && $nuevaImagen["size"] > 0) {

    $nombreArchivo = time() . "_" . basename($nuevaImagen["name"]);
    $rutaDestino = "../uploads/" . $nombreArchivo;

    if (move_uploaded_file($nuevaImagen["tmp_name"], $rutaDestino)) {

    
        $stmt = $conn->prepare("UPDATE usuarios SET imagen = ? WHERE usuario = ?");
        $stmt->bind_param("ss", $nombreArchivo, $usuario);
        $stmt->execute();
        $stmt->close();

     
        $_SESSION["imagen"] = $nombreArchivo;

        registrarLog($conn, $usuario, "Cambió su foto de perfil");
    }
}

header("Location: ../usuario.php?ok=1");
exit;
