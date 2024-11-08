<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'PRO') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Función para obtener los datos del prospecto
function getProspectoData($conexion, $user_id) {
    $stmt = $conexion->prepare("SELECT p.*, u.correo FROM Prospecto p JOIN Usuario u ON p.usuario = u.numero WHERE u.numero = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Función para obtener las experiencias del prospecto
function getExperiencias($conexion, $prospecto_id) {
    $stmt = $conexion->prepare("SELECT e.*, r.descripcion AS responsabilidad FROM Experiencia e LEFT JOIN Responsabilidades r ON e.numero = r.experiencia WHERE e.prospecto = ? ORDER BY e.fechaInicio DESC");
    $stmt->bind_param("i", $prospecto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $experiencias = [];
    while ($row = $result->fetch_assoc()) {
        $exp_num = $row['numero'];
        if (!isset($experiencias[$exp_num])) {
            $experiencias[$exp_num] = $row;
            $experiencias[$exp_num]['responsabilidades'] = [];
        }
        if ($row['responsabilidad']) {
            $experiencias[$exp_num]['responsabilidades'][] = $row['responsabilidad'];
        }
    }
    return $experiencias;
}

// Función para obtener las carreras estudiadas del prospecto
function getCarreras($conexion, $prospecto_id) {
    $stmt = $conexion->prepare("SELECT ce.*, c.nombre AS nombre_carrera 
                                FROM Carreras_estudiadas ce 
                                JOIN Carrera c ON ce.carrera = c.codigo 
                                WHERE ce.prospecto = ?");
    $stmt->bind_param("i", $prospecto_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


$prospecto = getProspectoData($conexion, $user_id);
$experiencias = getExperiencias($conexion, $prospecto['numero']);
$carreras = getCarreras($conexion, $prospecto['numero']);

// Procesar formularios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_basic_info'])) {
        $nombre = $_POST['nombre'];
        $primerApellido = $_POST['primerApellido'];
        $segundoApellido = $_POST['segundoApellido'];
        $fechaNacimiento = $_POST['fechaNacimiento'];
        $numTel = $_POST['numTel'];
        $resumen = $_POST['resumen'];

        $stmt = $conexion->prepare("UPDATE Prospecto SET nombre = ?, primerApellido = ?, segundoApellido = ?, fechaNacimiento = ?, numTel = ?, resumen = ? WHERE numero = ?");
        $stmt->bind_param("ssssssi", $nombre, $primerApellido, $segundoApellido, $fechaNacimiento, $numTel, $resumen, $prospecto['numero']);
        $stmt->execute();

        // Actualizar los datos del prospecto después de la modificación
        $prospecto = getProspectoData($conexion, $user_id);
    } elseif (isset($_POST['add_experience'])) {
        $puesto = $_POST['puesto'];
        $nombreEmpresa = $_POST['nombreEmpresa'];
        $fechaInicio = $_POST['fechaInicio'];
        $fechaFin = $_POST['fechaFin'];
        $descripcion = $_POST['descripcionExperiencia'];

        $stmt = $conexion->prepare("INSERT INTO Experiencia (prospecto, puesto, nombreEmpresa, fechaInicio, fechaFin, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $prospecto['numero'], $puesto, $nombreEmpresa, $fechaInicio, $fechaFin, $descripcion);
        $stmt->execute();

        $exp_id = $conexion->insert_id;

        $responsabilidades = explode("\n", $_POST['responsabilidades']);
        foreach ($responsabilidades as $responsabilidad) {
            if (trim($responsabilidad) !== '') {
                $stmt = $conexion->prepare("INSERT INTO Responsabilidades (experiencia, descripcion) VALUES (?, ?)");
                $stmt->bind_param("is", $exp_id, $responsabilidad);
                $stmt->execute();
            }
        }

        // Actualizar las experiencias después de la adición
        $experiencias = getExperiencias($conexion, $prospecto['numero']);
    } elseif (isset($_POST['add_education'])) {
        $carrera = $_POST['carrera'];
        $anioConcluido = $_POST['anioConcluido'];
    
        $stmt = $conexion->prepare("INSERT INTO Carreras_estudiadas (prospecto, carrera, anioConcluido) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $prospecto['numero'], $carrera, $anioConcluido);
        $stmt->execute();

        // Actualizar las carreras después de la adición
        $carreras = getCarreras($conexion, $prospecto['numero']);
    } elseif (isset($_POST['delete_education'])) {
        $carrera_codigo = $_POST['carrera_codigo'];
        
        // Eliminar responsabilidades asociadas
        $stmt = $conexion->prepare("DELETE FROM Responsabilidades WHERE experiencia = ?");
        $stmt->bind_param("i", $exp_id);
        $stmt->execute();
        
        // Eliminar la experiencia
        $stmt = $conexion->prepare("DELETE FROM Experiencia WHERE numero = ? AND prospecto = ?");
        $stmt->bind_param("ii", $exp_id, $prospecto['numero']);
        $stmt->execute();
        
        // Actualizar las experiencias después de la eliminación
        $experiencias = getExperiencias($conexion, $prospecto['numero']);
    } elseif (isset($_POST['delete_education'])) {
        $edu_id = $_POST['edu_id'];
        
        // Eliminar la carrera estudiada
        $stmt = $conexion->prepare("DELETE FROM Carreras_estudiadas WHERE carrera = ? AND prospecto = ?");
        $stmt->bind_param("si", $edu_id, $prospecto['numero']);
        $stmt->execute();
        
        // Actualizar las carreras después de la eliminación
        $carreras = getCarreras($conexion, $prospecto['numero']);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($prospecto['nombre']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .main-content {
            flex: 2;
            margin-right: 20px;
        }
        .sidebar {
            flex: 1;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }
        .profile-info h1 {
            margin: 0 0 10px 0;
        }
        .section {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .experience-item, .education-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .experience-item:last-child, .education-item:last-child {
            border-bottom: none;
        }
        .btn {
            display: inline-block;
            background-color: #333;
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #555;
        }
        .btn-delete {
            background-color: #ff4444;
        }
        .btn-delete:hover {
            background-color: #cc0000;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        form input, form textarea, form select {
            margin-bottom: 10px;
            padding: 5px;
        }
        form button {
            align-self: flex-start;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="profile-header">
                <img src="img/default-profile.jpg" alt="Foto de perfil" class="profile-image">
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($prospecto['nombre'] . ' ' . $prospecto['primerApellido'] . ' ' . $prospecto['segundoApellido']); ?></h1>
                    <p><?php echo htmlspecialchars($prospecto['resumen']); ?></p>
                    <p>Nacimiento: <?php echo date('d/m/Y', strtotime($prospecto['fechaNacimiento'])); ?> · Email: <?php echo htmlspecialchars($prospecto['correo']); ?></p>
                </div>
            </div>
            <div class="section">
                <h2>Experiencia Laboral</h2>
                <?php foreach ($experiencias as $experiencia): ?>
                    <div class="experience-item">
                        <h3><?php echo htmlspecialchars($experiencia['puesto']); ?></h3>
                        <p><?php echo htmlspecialchars($experiencia['nombreEmpresa']); ?></p>
                        <p><?php echo date('m/Y', strtotime($experiencia['fechaInicio'])) . ' - ' . date('m/Y', strtotime($experiencia['fechaFin'])); ?></p>
                        <p><?php echo htmlspecialchars($experiencia['descripcion']); ?></p>
                        <?php if (!empty($experiencia['responsabilidades'])): ?>
                            <ul>
                                <?php foreach ($experiencia['responsabilidades'] as $responsabilidad): ?>
                                    <li><?php echo htmlspecialchars($responsabilidad); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="delete_experience" value="1">
                            <input type="hidden" name="exp_id" value="<?php echo $experiencia['numero']; ?>">
                            <button type="submit" class="btn btn-delete">Eliminar Experiencia</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="sidebar">
            <div class="section">
                <h2>Acerca de mí</h2>
                <p><?php echo nl2br(htmlspecialchars($prospecto['resumen'])); ?></p>
            </div>
            <div class="section">
            <h2>Educación</h2>
            <?php foreach ($carreras as $carrera): ?>
                <div class="education-item">
                    <h3><?php echo htmlspecialchars($carrera['nombre_carrera']); ?></h3>
                    <p>Año de conclusión: <?php echo htmlspecialchars($carrera['anioConcluido']); ?></p>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="delete_education" value="1">
                        <input type="hidden" name="carrera_codigo" value="<?php echo htmlspecialchars($carrera['carrera']); ?>">
                        <button type="submit" class="btn btn-delete">Eliminar Educación</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>

    <div class="container">
        <div class="section">
            <h2>Editar Información Básica</h2>
            <form method="POST" action="">
                <input type="hidden" name="update_basic_info" value="1">
                <input type="text" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($prospecto['nombre']); ?>" required>
                <input type="text" name="primerApellido" placeholder="Primer Apellido" value="<?php echo htmlspecialchars($prospecto['primerApellido']); ?>" required>
                <input type="text" name="segundoApellido" placeholder="Segundo Apellido" value="<?php echo htmlspecialchars($prospecto['segundoApellido']); ?>">
                <input type="date" name="fechaNacimiento" value="<?php echo $prospecto['fechaNacimiento']; ?>" required>
                <input type="tel" name="numTel" placeholder="Teléfono" value="<?php echo htmlspecialchars($prospecto['numTel']); ?>" required>
                <textarea name="resumen" placeholder="Resumen" required><?php echo htmlspecialchars($prospecto['resumen']); ?></textarea>
                <button type="submit" class="btn">Guardar Cambios</button>
            </form>
        </div>

        <div class="section">
            <h2>Añadir Experiencia</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_experience" value="1">
                <input type="text" name="puesto" placeholder="Puesto" required>
                <input type="text" name="nombreEmpresa" placeholder="Nombre de la Empresa" required>
                <input type="date" name="fechaInicio" required>
                <input type="date" name="fechaFin" required>
                <textarea name="descripcionExperiencia" placeholder="Descripción" required></textarea>
                <textarea name="responsabilidades" placeholder="Responsabilidades (una por línea)"></textarea>
                <button type="submit" class="btn">Añadir Experiencia</button>
            </form>
        </div>

        <div class="section">
            <h2>Añadir Educación</h2>
            <form method="POST" action="">
                <input type="hidden" name="add_education" value="1">
                <select name="carrera" required>
                    <?php
                    $stmt = $conexion->prepare("SELECT codigo, nombre FROM Carrera");
                    $stmt->execute();
                    $carreras_disponibles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    foreach ($carreras_disponibles as $carrera) {
                        echo "<option value='" . htmlspecialchars($carrera['codigo']) . "'>" . htmlspecialchars($carrera['nombre']) . "</option>";
                    }
                    ?>
                </select>
                <input type="number" name="anioConcluido" placeholder="Año de Conclusión" required>
                <button type="submit" class="btn">Añadir Educación</button>
            </form>
        </div>

    </div>
</body>
</html>