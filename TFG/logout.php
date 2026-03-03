<?php
session_start();
require_once "config.php";
require_once "Funciones/logs.php";

if (isset($_SESSION["usuario"])) {
    registrarLog($conn, $_SESSION["usuario"], "Cerró sesión");
}

session_destroy();
header("Location: login.php");
exit;
