<?php
require_once "../config.php";
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$tema = $data["tema"];

$stmt = $conn->prepare("UPDATE usuarios SET tema = ? WHERE usuario = ?");
$stmt->bind_param("ss", $tema, $_SESSION["usuario"]);
$stmt->execute();

$_SESSION["tema"] = $tema;

echo json_encode(["status" => "success"]);
