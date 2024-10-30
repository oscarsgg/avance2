<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADM') {
    header("Location: login.php");
    exit();
}

// Incluye el sidebar en tu página
include 'incluides/sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Administrador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            padding-left: 120px; /* Desplaza todo el contenido 100px a la derecha */
        }
        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <div> <!-- Contenedor principal -->
        <h1>Bienvenido, Administrador</h1>
        <p>Esta es la página de inicio para administradores.</p>
        <a href="/Outsourcing/logout.php">Cerrar sesión</a>
    </div>
</body>
</html>

