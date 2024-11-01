<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y es una empresa
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'EMP') {
    header("Location: login.php");
    exit();
}

// Obtener las vacantes de la empresa
$empresa_id = 1; // Asume que tienes el ID de la empresa actual
$sql_vacantes = "SELECT numero, titulo FROM Vacante WHERE empresa = $empresa_id";
$result_vacantes = $conexion->query($sql_vacantes);

// Función para obtener el nombre del estatus
function obtener_nombre_estatus($codigo) {
    global $conexion;
    $sql = "SELECT nombre FROM Estatus_Solicitud WHERE codigo = '$codigo'";
    $result = $conexion->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['nombre'];
    }
    return $codigo;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Solicitudes</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            margin-left: 90px;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        h1 {
            color: #000;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        select, button {
            padding: 12px 20px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        select:focus, button:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
        }
        .filters {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 20px;
        }
        .solicitud {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .solicitud:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .solicitud h3 {
            margin-top: 0;
            color: #000;
            font-size: 1.2em;
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
            background-color: rgba(0,0,0,0.6);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border: 1px solid #888;
            width: 80%;
            max-width: 800px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
        }
        .btn {
            background-color: #000;
            color: #fff;
            border: none;
            margin-top: 15px;
            margin-right: 15px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #333;
        }
        .btn-secondary {
            background-color: #fff;
            color: #000;
            border: 1px solid #000;
        }
        .btn-secondary:hover {
            background-color: #f0f0f0;
        }
        .welcome-message {
            text-align: center;
            margin-top: 50px;
            font-size: 1.2em;
            color: #666;
        }
        .profile-section {
            margin-bottom: 20px;
        }
        .profile-section h4 {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .experience-item, .responsibility-item {
            margin-bottom: 15px;
            padding-left: 20px;
            position: relative;
        }
        .experience-item:before, .responsibility-item:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #000;
        }
    </style>
</head>
<body>
    <?php include 'incluides/sidebar.php'; ?>
    <div class="container">
        <h1>Revisar Solicitudes</h1>
        
        <div class="filters">
            <select id="vacante-select">
                <option value="">Seleccione una vacante</option>
                <?php while($row = $result_vacantes->fetch_assoc()): ?>
                    <option value="<?php echo $row['numero']; ?>"><?php echo $row['titulo']; ?></option>
                <?php endwhile; ?>
            </select>
            
            <select id="estatus-select">
                <option value="">Todos los estatus</option>
                <option value="PEND">Pendiente</option>
                <option value="APRO">Aprobada</option>
                <option value="RECH">Rechazada</option>
                <option value="PFRM">Por firmar</option>
                <option value="CERR">Cerrada</option>
            </select>
        </div>
        
        <div id="solicitudes-list">
            <div class="welcome-message">
                <p>Bienvenido al sistema de revisión de solicitudes.</p>
                <p>Por favor, seleccione una vacante y un estatus para comenzar.</p>
            </div>
        </div>
    </div>

    <div id="perfil-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Perfil del Prospecto</h2>
            <div id="perfil-content"></div>
        </div>
    </div>

    <div id="confirm-modal" class="modal">
        <div class="modal-content">
            <h2>Confirmar cambios</h2>
            <p>¿Está seguro que desea cambiar el estatus de esta solicitud?</p>
            <button id="confirm-yes" class="btn">Sí, cambiar</button>
            <button id="confirm-no" class="btn btn-secondary">Cancelar</button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function cargarSolicitudes() {
                var vacante_id = $('#vacante-select').val();
                var estatus = $('#estatus-select').val();
                if (vacante_id) {
                    $.ajax({
                        url: 'obtener_solicitudes.php',
                        method: 'GET',
                        data: { vacante_id: vacante_id, estatus: estatus },
                        success: function(response) {
                            $('#solicitudes-list').html(response);
                        }
                    });
                } else {
                    $('#solicitudes-list').html('<div class="welcome-message"><p>Por favor, seleccione una vacante para ver las solicitudes.</p></div>');
                }
            }

            $('#vacante-select, #estatus-select').change(cargarSolicitudes);

            $(document).on('click', '.ver-perfil', function() {
                var prospecto_id = $(this).data('prospecto');
                $.ajax({
                    url: 'obtener_perfil.php',
                    method: 'GET',
                    data: { prospecto_id: prospecto_id },
                    success: function(response) {
                        $('#perfil-content').html(response);
                        $('#perfil-modal').css('display', 'block');
                    }
                });
            });

            $('.close').click(function() {
                $('#perfil-modal').css('display', 'none');
            });

            $(window).click(function(event) {
                if (event.target == document.getElementById('perfil-modal')) {
                    $('#perfil-modal').css('display', 'none');
                }
            });

            var cambioEstatusData = {};

            $(document).on('click', '.cambiar-estatus', function() {
                cambioEstatusData.prospecto = $(this).data('prospecto');
                cambioEstatusData.vacante = $(this).data('vacante');
                cambioEstatusData.nuevo_estatus = $(this).data('estatus');
                $('#confirm-modal').css('display', 'block');
            });

            $('#confirm-yes').click(function() {
                $.ajax({
                    url: 'cambiar_estatus.php',
                    method: 'POST',
                    data: cambioEstatusData,
                    success: function(response) {
                        $('#confirm-modal').css('display', 'none');
                        cargarSolicitudes();
                    }
                });
            });

            $('#confirm-no').click(function() {
                $('#confirm-modal').css('display', 'none');
            });
        });
    </script>
</body>
</html>