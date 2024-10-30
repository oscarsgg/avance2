<?php
// membresia.php

session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está autenticado como empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Obtener información de la membresía actual
$query = "SELECT * FROM Membresia WHERE empresa = $user_id ORDER BY fechaVencimiento DESC LIMIT 1";
$result = mysqli_query($conexion, $query);
$membresia = mysqli_fetch_assoc($result);

// Verificar si la membresía ha expirado
$membresia_expirada = strtotime($membresia['fechaVencimiento']) < time();

// Obtener planes de suscripción
$query = "SELECT * FROM Plan_suscripcion ORDER BY precio ASC";
$result = mysqli_query($conexion, $query);
$planes = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Procesar el pago si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $plan_id = mysqli_real_escape_string($conexion, $_POST['plan_id']);
    $numero_tarjeta = mysqli_real_escape_string($conexion, $_POST['numero_tarjeta']);
    $fecha_vencimiento = mysqli_real_escape_string($conexion, $_POST['fecha_vencimiento']);
    $cvv = mysqli_real_escape_string($conexion, $_POST['cvv']);

    // Validar los datos de la tarjeta
    if (strlen($numero_tarjeta) !== 16 || !ctype_digit($numero_tarjeta)) {
        $error = "El número de tarjeta debe tener 16 dígitos.";
    } elseif (strtotime($fecha_vencimiento) <= time()) {
        $error = "La fecha de vencimiento de la tarjeta no es válida.";
    } elseif (strlen($cvv) !== 3 || !ctype_digit($cvv)) {
        $error = "El CVV debe tener 3 dígitos.";
    } else {
        // Procesar el pago (aquí deberías integrar con un sistema de pago real)
        // Por ahora, asumiremos que el pago fue exitoso

        // Actualizar la membresía
        $query = "SELECT duracion FROM Plan_suscripcion WHERE codigo = '$plan_id'";
        $result = mysqli_query($conexion, $query);
        $duracion = mysqli_fetch_assoc($result)['duracion'];

        $nueva_fecha_vencimiento = date('Y-m-d', strtotime("+$duracion months"));
        
        $query = "UPDATE Membresia SET fechaVencimiento = '$nueva_fecha_vencimiento', estatus = 1, plan_suscripcion = '$plan_id' WHERE empresa = $user_id";
        mysqli_query($conexion, $query);

        // Registrar la renovación
        $query = "INSERT INTO Renovacion (fechaRenovacion, membresia) VALUES (CURDATE(), {$membresia['numero']})";
        mysqli_query($conexion, $query);

        $success = "¡Pago procesado con éxito! Su membresía ha sido actualizada.";
        
        // Actualizar la información de la membresía
        $query = "SELECT * FROM Membresia WHERE empresa = $user_id ORDER BY fechaVencimiento DESC LIMIT 1";
        $result = mysqli_query($conexion, $query);
        $membresia = mysqli_fetch_assoc($result);
        $membresia_expirada = false;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membresía - Sistema de Outsourcing</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            margin-left: 100px;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #000;
        }
        .plan-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        .plan-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 10px;
            width: 300px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .plan-card:hover {
            transform: translateY(-5px);
        }
        .plan-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .plan-price {
            font-size: 36px;
            font-weight: bold;
            color: #000;
            margin-bottom: 20px;
        }
        .plan-details {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #333;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        .error {
            color: #ff0000;
            margin-bottom: 10px;
        }
        .success {
            color: #008000;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'incluides/sidebar.php'; ?>
        <h1>Membresía</h1>
        
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if ($membresia_expirada): ?>
            <div class="error">
                <p>Su membresía ha expirado. Por favor, renueve su plan para continuar usando el sistema.</p>
            </div>
        <?php else: ?>
            <p>Su membresía actual vence el: <?php echo date('d/m/Y', strtotime($membresia['fechaVencimiento'])); ?></p>
        <?php endif; ?>

        <h2>Planes disponibles</h2>
        <div class="plan-container">
            <?php foreach ($planes as $plan): ?>
                <div class="plan-card">
                    <div class="plan-title"><?php echo ucfirst(strtolower(substr($plan['codigo'], 0, 3))); ?></div>
                    <div class="plan-price">$<?php echo number_format($plan['precio'], 2); ?></div>
                    <div class="plan-details">
                        <p>Duración: <?php echo $plan['duracion']; ?> meses</p>
                        <p>Precio mensual: $<?php echo number_format($plan['precioMensual'], 2); ?></p>
                    </div>
                    <a href="#" class="btn select-plan" data-plan="<?php echo $plan['codigo']; ?>">Seleccionar</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirmar pago</h2>
            <form id="paymentForm" method="POST">
                <input type="hidden" id="plan_id" name="plan_id" value="">
                <div>
                    <label for="numero_tarjeta">Número de tarjeta:</label>
                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" required maxlength="16">
                </div>
                <div>
                    <label for="fecha_vencimiento">Fecha de vencimiento:</label>
                    <input type="month" id="fecha_vencimiento" name="fecha_vencimiento" required>
                </div>
                <div>
                    <label for="cvv">CVV:</label>
                    <input type="text" id="cvv" name="cvv" required maxlength="3">
                </div>
                <button type="submit" class="btn">Confirmar pago</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("paymentModal");
        const selectPlanButtons = document.querySelectorAll(".select-plan");
        const closeButton = document.getElementsByClassName("close")[0];
        const planIdInput = document.getElementById("plan_id");

        selectPlanButtons.forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                const planId = this.getAttribute("data-plan");
                planIdInput.value = planId;
                modal.style.display = "block";
            });
        });

        closeButton.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Validación del formulario
        document.getElementById("paymentForm").addEventListener("submit", function(e) {
            const numeroTarjeta = document.getElementById("numero_tarjeta").value;
            const fechaVencimiento = document.getElementById("fecha_vencimiento").value;
            const cvv = document.getElementById("cvv").value;

            if (numeroTarjeta.length !== 16 || !/^\d+$/.test(numeroTarjeta)) {
                alert("El número de tarjeta debe tener 16 dígitos.");
                e.preventDefault();
                return;
            }

            const hoy = new Date();
            const fechaVencimientoDate = new Date(fechaVencimiento);
            if (fechaVencimientoDate <= hoy) {
                alert("La fecha de vencimiento de la tarjeta no es válida.");
                e.preventDefault();
                return;
            }

            if (cvv.length !== 3 || !/^\d+$/.test(cvv)) {
                alert("El CVV debe tener 3 dígitos.");
                e.preventDefault();
                return;
            }

            if (!confirm("¿Está seguro de que desea proceder con el pago?")) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>