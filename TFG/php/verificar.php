<?php
require_once "../config.php";

if (!isset($_GET["token"])) {
    die("<div style='
        font-family: Arial; 
        background:#1e1e1e; 
        color:#ff6b6b; 
        padding:20px; 
        border-radius:10px; 
        max-width:400px; 
        margin:50px auto; 
        text-align:center;
    '>
        <h2>❌ Token inválido</h2>
        <p>El enlace no es válido o está incompleto.</p>
        <a href='../index.php' style='color:#4ea1ff;'>Volver al inicio</a>
    </div>");
}

$token = $_GET["token"];

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE token_verificacion = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {

    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    $update = $conn->prepare("UPDATE usuarios SET verificado = 1, token_verificacion = NULL WHERE id = ?");
    $update->bind_param("i", $id);
    $update->execute();

    echo "
    <div style='
        font-family: Arial, sans-serif;
        background: #0d1117;
        color: #e6edf3;
        padding: 30px;
        border-radius: 12px;
        max-width: 450px;
        margin: 80px auto;
        text-align: center;
        border: 1px solid #30363d;
    '>
        <h2 style='color:#3fb950; font-size:28px;'>✔ Cuenta verificada</h2>

        <p style='font-size:16px; line-height:1.6; margin-top:10px;'>
            Tu cuenta ha sido verificada correctamente.<br>
            Ya puedes iniciar sesión en GameDock.
        </p>

        <a href='../index.php' style='
            display:inline-block;
            margin-top:25px;
            background:#238636;
            color:white;
            padding:12px 20px;
            border-radius:6px;
            text-decoration:none;
            font-size:16px;
        '>Iniciar sesión</a>
    </div>
    ";

} else {

    echo "
    <div style='
        font-family: Arial, sans-serif;
        background: #1e1e1e;
        color: #ff6b6b;
        padding: 30px;
        border-radius: 12px;
        max-width: 450px;
        margin: 80px auto;
        text-align: center;
        border: 1px solid #3a3a3a;
    '>
        <h2 style='font-size:26px;'>❌ Token inválido o expirado</h2>

        <p style='font-size:15px; margin-top:10px;'>
            El enlace ya no es válido. Es posible que ya hayas verificado tu cuenta.
        </p>

        <a href='../index.php' style='
            display:inline-block;
            margin-top:25px;
            background:#4ea1ff;
            color:white;
            padding:12px 20px;
            border-radius:6px;
            text-decoration:none;
            font-size:16px;
        '>Volver al inicio</a>
    </div>
    ";
}
