<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Asumimos que el número de contrato se pasa como parámetro GET
$numero_contrato = isset($_GET['numero']) ? intval($_GET['numero']) : 0;
$numero_contrato = 1; 

if ($numero_contrato <= 0) {
    die("Número de contrato no válido");
}

// Consulta para obtener los datos del contrato
$query = "SELECT c.numero, c.fechaInicio, c.fechaCierre, 
                 p.nombre, p.primerApellido, p.segundoApellido,
                 tc.nombre AS tipo_contrato, tc.descripcion AS descripcion_contrato
          FROM Contrato c
          JOIN Prospecto p ON c.prospecto = p.numero
          JOIN Tipo_Contrato tc ON c.tipo_contrato = tc.codigo
          WHERE c.numero = ?";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $numero_contrato);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($contrato = mysqli_fetch_assoc($resultado)) {
    $nombre_completo = $contrato['nombre'] . ' ' . $contrato['primerApellido'] . 
                       ($contrato['segundoApellido'] ? ' ' . $contrato['segundoApellido'] : '');
} else {
    die("Contrato no encontrado");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato <?php echo $contrato['numero']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .contract-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .contract-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .contract-info {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .contract-info p {
            margin: 10px 0;
        }
        .contract-body {
            text-align: justify;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 250px;
            text-align: center;
            padding-top: 10px;
        }
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="contract-header">
        <h1 class="contract-title">Contrato de Trabajo</h1>
    </div>
    
    <div class="contract-info">
        <p><strong>Número de Contrato:</strong> <?php echo htmlspecialchars($contrato['numero']); ?></p>
        <p><strong>Fecha de Inicio:</strong> <?php echo htmlspecialchars($contrato['fechaInicio']); ?></p>
        <p><strong>Fecha de Cierre:</strong> <?php echo htmlspecialchars($contrato['fechaCierre']); ?></p>
        <p><strong>Nombre del Empleado:</strong> <?php echo htmlspecialchars($nombre_completo); ?></p>
        <p><strong>Tipo de Contrato:</strong> <?php echo htmlspecialchars($contrato['tipo_contrato']); ?></p>
    </div>
    
    <div class="contract-body">
        <h2>Descripción del Contrato</h2>
        <p><?php echo htmlspecialchars($contrato['descripcion_contrato']); ?></p>
        
        <h2>Términos y Condiciones</h2>
        <p>Este contrato se rige por las leyes laborales vigentes y establece los términos y condiciones de empleo entre la empresa y el empleado mencionado anteriormente.</p>
        
        <p>El empleado se compromete a cumplir con sus responsabilidades y deberes según lo establecido por la empresa, y la empresa se compromete a proporcionar las condiciones de trabajo y remuneración acordadas.</p>
    </div>
    
    <div class="signature-line">
        Firma del Empleado
    </div>
    
    <div class="signature-line">
        Firma del Representante de la Empresa
    </div>
</body>
</html>