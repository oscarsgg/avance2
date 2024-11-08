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

    <div id="contrato-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Generar Contrato</h2>
            <form id="contrato-form">
                <input type="hidden" id="contrato-prospecto" name="prospecto">
                <input type="hidden" id="contrato-vacante" name="vacante">
                <div>
                    <label for="fecha-inicio">Fecha de inicio:</label>
                    <input type="date" id="fecha-inicio" name="fecha_inicio" required>
                </div>
                <div>
                    <label for="fecha-cierre">Fecha de cierre:</label>
                    <input type="date" id="fecha-cierre" name="fecha_cierre" required>
                </div>
                <div>
                    <label for="salario">Salario:</label>
                    <input type="number" id="salario" name="salario" step="0.01" required>
                </div>
                <div>
                    <label for="horas-diarias">Horas diarias:</label>
                    <input type="number" id="horas-diarias" name="horas_diarias" required>
                </div>
                <div>
                    <label for="horario">Horario:</label>
                    <input type="text" id="horario" name="horario" required>
                </div>
                <div>
                    <label for="tipo-contrato">Tipo de contrato:</label>
                    <select id="tipo-contrato" name="tipo_contrato" required>
                        <?php
                        $sql_tipos_contrato = "SELECT codigo, nombre FROM Tipo_Contrato";
                        $result_tipos_contrato = $conexion->query($sql_tipos_contrato);
                        if ($result_tipos_contrato->num_rows > 0) {
                            while($row = $result_tipos_contrato->fetch_assoc()) {
                                echo "<option value='" . $row['codigo'] . "'>" . $row['nombre'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>No hay tipos de contrato disponibles</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="firma-canvas">Firma del prospecto:</label>
                    <canvas id="firma-canvas" width="400" height="200" style="border:1px solid #000000;"></canvas>
                    <button type="button" id="limpiar-firma">Limpiar firma</button>
                </div>
                <button type="submit" class="btn">Generar Contrato</button>
            </form>
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

            //Codigo para generar la firma y el contrato
            var canvas = document.getElementById('firma-canvas');
            var ctx = canvas.getContext('2d');
            var drawing = false;
            var lastX, lastY;

            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            function startDrawing(e) {
                drawing = true;
                [lastX, lastY] = [e.offsetX, e.offsetY];
            }


            function draw(e) {
                if (!drawing) return;
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
                [lastX, lastY] = [e.offsetX, e.offsetY];
            }

            function stopDrawing() {
                drawing = false;
            }

            $('#limpiar-firma').click(function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });

            $(document).on('click', '.generar-contrato', function() {
                var prospecto = $(this).data('prospecto');
                var vacante = $(this).data('vacante');
                $('#contrato-prospecto').val(prospecto);
                $('#contrato-vacante').val(vacante);
                $('#contrato-modal').css('display', 'block');
            });

            $('#contrato-form').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                // Convertir la firma a imagen y añadirla al FormData
                canvas.toBlob(function(blob) {
                    formData.append('firma', blob, 'firma.png');
                    
                    $.ajax({
                        url: 'generar_contrato.php',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(response) {
                            console.log('Respuesta del servidor:', response);
                            if (response.success) {
                                alert(response.message);
                                $('#contrato-modal').css('display', 'none');
                                cargarSolicitudes();
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error en la solicitud AJAX:', error);
                            console.error('Respuesta del servidor:', xhr.responseText);
                            alert('Error en la solicitud AJAX. Por favor, revise la consola para más detalles.');
                        }
                    });
                });
            });

            // Cerrar el modal cuando se hace clic en la 'x'
            $('.close').click(function() {
                $('#contrato-modal').css('display', 'none');
            });

            // Cerrar el modal cuando se hace clic fuera de él
            $(window).click(function(event) {
                if (event.target == document.getElementById('contrato-modal')) {
                    $('#contrato-modal').css('display', 'none');
                }
            });
        });
    </script>
</body>
</html>