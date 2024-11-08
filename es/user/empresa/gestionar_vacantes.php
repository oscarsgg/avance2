<?php
// gestionar_vacantes.php

session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');
require_once 'check_membership.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener el número de la empresa asociada al usuario
$query_empresa = "SELECT numero FROM Empresa WHERE usuario = $user_id";
$result_empresa = mysqli_query($conexion, $query_empresa);

if (mysqli_num_rows($result_empresa) == 0) {
    die("Error: No se encontró una empresa asociada a este usuario.");
}

$empresa = mysqli_fetch_assoc($result_empresa);
$empresa_id = $empresa['numero'];

// Obtener el término de búsqueda si existe
$search = isset($_GET['search']) ? mysqli_real_escape_string($conexion, $_GET['search']) : '';

// Consulta para obtener las vacantes de la empresa
$query = "SELECT numero, titulo, fechaCierre FROM Vacante WHERE empresa = $empresa_id";
if (!empty($search)) {
    $query .= " AND titulo LIKE '%$search%'";
}
$query .= " ORDER BY fechaCierre DESC";

$result = mysqli_query($conexion, $query);

?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Vacantes - Sistema de Outsourcing</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            color: #333;
            background-color: #f2f5f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }
        .container {
            max-width: 1100px;
            width: 90%;
            background-color: #fff;
            padding: 40px 30px;
            margin-top: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #222;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 2.5em;
            font-weight: 600;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            padding: 0 10%;
        }
        .search-container input[type="text"] {
            width: 80%;
            padding: 15px 20px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 50px 0 0 50px;
            outline: none;
            transition: border 0.3s;
        }
        .search-container input[type="text"]:focus {
            border-color: #666;
        }
        .search-container button {
            width: 15%;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 0 50px 50px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-container button:hover {
            background-color: #555;
        }
        .vacantes-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .vacante-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }
        .vacante-item:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .vacante-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .vacante-title {
            font-size: 1.5em;
            font-weight: 600;
            color: #333;
        }
        .vacante-status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-top: 5px;
            width: fit-content;
        }
        .status-active {
            background-color: #e0f7ea;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .status-inactive {
            background-color: #fce4ec;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        .vacante-link {
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            font-size: 0.9em;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
        }
        .vacante-link:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Gestionar Vacantes</h1>
        
        <div class="search-container">
            <form action="" method="GET" style="display: flex; width: 100%;">
                <input type="text" name="search" placeholder="Buscar vacantes..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <div class="vacantes-list">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $is_active = strtotime($row['fechaCierre']) >= strtotime(date('Y-m-d'));
                    ?>
                    <div class="vacante-item">
                        <div class="vacante-details">
                            <div class="vacante-title"><?php echo htmlspecialchars($row['titulo']); ?></div>
                            <span class="vacante-status <?php echo $is_active ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $is_active ? 'Activa' : 'Inactiva'; ?>
                            </span>
                        </div>
                        <a href="detalle_vacante.php?id=<?php echo $row['numero']; ?>" class="vacante-link">Ver Detalles</a>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No se encontraron vacantes.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
