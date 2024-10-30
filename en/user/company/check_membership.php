<?php
// check_membership.php

include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar el estado de la membresía
$query = "SELECT fechaVencimiento FROM Membresia WHERE empresa = $user_id ORDER BY fechaVencimiento DESC LIMIT 1";
$result = mysqli_query($conexion, $query);
$membresia = mysqli_fetch_assoc($result);

if (!$membresia || strtotime($membresia['fechaVencimiento']) < time()) {
    // Redirigir a la página de membresía si ha expirado
    header("Location: membresia.php");
    exit();
}