<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_registro = $_POST['tipo_registro'];
    
    // Validaciones comunes
    $correo = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
    if (!$correo || !preg_match('/\.[a-z]{2,}$/i', $_POST['correo'])) {
        $error = "El correo electrónico no es válido.";
    }

    $contrasenia = $_POST['contrasenia'];
    $confirmar_contrasenia = $_POST['confirmar_contrasenia'];
    if (strlen($contrasenia) < 5) {
        $error = "La contraseña debe tener al menos 5 caracteres.";
    } elseif ($contrasenia !== $confirmar_contrasenia) {
        $error = "Las contraseñas no coinciden.";
    }

    $numTel = preg_replace('/[^0-9]/', '', $_POST['numTel']);
    if (strlen($numTel) != 10) {
        $error = "El número de teléfono debe tener 10 dígitos.";
    }

    if (empty($error)) {
        if ($tipo_registro == 'prospecto') {
            // Validaciones específicas para prospecto
            $nombre = preg_replace('/[^a-zA-Z]/', '', $_POST['nombre']);
            $primerApellido = preg_replace('/[^a-zA-Z]/', '', $_POST['primerApellido']);
            $segundoApellido = preg_replace('/[^a-zA-Z]/', '', $_POST['segundoApellido']);
            
            if (strlen($nombre) < 2 || strlen($primerApellido) < 2) {
                $error = "El nombre debe tener al menos 2 caracteres y el primer apellido al menos 2 letras.";
            }

            $fechaNacimiento = $_POST['fechaNacimiento'];
            $resumen = mysqli_real_escape_string($conexion, $_POST['resumen']);

            // Verificar edad
            $edad = date_diff(date_create($fechaNacimiento), date_create('today'))->y;
            if ($edad < 18) {
                $error = "Debes ser mayor de 18 años para registrarte.";
            }

            if (empty($error)) {
                // Insertar en la tabla Usuario
                $query_usuario = "INSERT INTO Usuario (correo, contrasenia, rol) VALUES (?, ?, 'PRO')";
                $stmt_usuario = mysqli_prepare($conexion, $query_usuario);
                mysqli_stmt_bind_param($stmt_usuario, "ss", $correo, $contrasenia);
                
                if (mysqli_stmt_execute($stmt_usuario)) {
                    $id_usuario = mysqli_insert_id($conexion);
                    
                    // Insertar en la tabla Prospecto
                    $query_prospecto = "INSERT INTO Prospecto (nombre, primerApellido, segundoApellido, resumen, fechaNacimiento, numTel, usuario) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt_prospecto = mysqli_prepare($conexion, $query_prospecto);
                    mysqli_stmt_bind_param($stmt_prospecto, "ssssssi", $nombre, $primerApellido, $segundoApellido, $resumen, $fechaNacimiento, $numTel, $id_usuario);
                    
                    if (mysqli_stmt_execute($stmt_prospecto)) {
                        $success = "Registro de prospecto exitoso";
                    } else {
                        $error = "Error al registrar el prospecto";
                    }
                } else {
                    $error = "Error al crear el usuario";
                }
            }
        } elseif ($tipo_registro == 'empresa') {
            // Validaciones específicas para empresa
            $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
            $ciudad = mysqli_real_escape_string($conexion, $_POST['ciudad']);
            $calle = mysqli_real_escape_string($conexion, $_POST['calle']);
            $numeroCalle = intval($_POST['numeroCalle']);
            $colonia = mysqli_real_escape_string($conexion, $_POST['colonia']);
            $codigoPostal = preg_replace('/[^0-9]/', '', $_POST['codigoPostal']);
            $nombreCont = trim($_POST['nombreCont']);
            $primerApellidoCont = preg_replace('/[^a-zA-Z]/', '', $_POST['primerApellidoCont']);
            $segundoApellidoCont = preg_replace('/[^a-zA-Z]/', '', $_POST['segundoApellidoCont']);

            if (strlen($codigoPostal) != 5) {
                $error = "El código postal debe tener 5 dígitos.";
            }

            if (strlen($nombreCont) < 2 || strlen($primerApellidoCont) < 2) {
                $error = "El nombre del contacto debe tener al menos 2 caracteres y el primer apellido al menos 2 letras.";
            }

            if (empty($error)) {
                // Insertar en la tabla Usuario
                $query_usuario = "INSERT INTO Usuario (correo, contrasenia, rol) VALUES (?, ?, 'EMP')";
                $stmt_usuario = mysqli_prepare($conexion, $query_usuario);
                mysqli_stmt_bind_param($stmt_usuario, "ss", $correo, $contrasenia);
                
                if (mysqli_stmt_execute($stmt_usuario)) {
                    $id_usuario = mysqli_insert_id($conexion);
                    
                    // Insertar en la tabla Empresa
                    $query_empresa = "INSERT INTO Empresa (nombre, ciudad, calle, numeroCalle, colonia, codigoPostal, nombreCont, primerApellidoCont, segundoApellidoCont, numTel, usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt_empresa = mysqli_prepare($conexion, $query_empresa);
                    mysqli_stmt_bind_param($stmt_empresa, "sssissssssi", $nombre, $ciudad, $calle, $numeroCalle, $colonia, $codigoPostal, $nombreCont, $primerApellidoCont, $segundoApellidoCont, $numTel, $id_usuario);
                    
                    if (mysqli_stmt_execute($stmt_empresa)) {
                        $success = "Registro de empresa exitoso";
                    } else {
                        $error = "Error al registrar la empresa";
                    }
                } else {
                    $error = "Error al crear el usuario";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - TalentBridge</title>
    <link rel="stylesheet" href="css/registro.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <button class="back-button" onclick="window.location.href='index.php'">← Volver</button>
        <div class="register-section">
            <div class="register-form">
                <h2>Registro en TalentBridge</h2>
                
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php else: ?>
                
                <form id="tipoRegistroForm">
                    <div class="form-group">
                        <label for="tipo_registro">Seleccione el tipo de registro:</label>
                        <select id="tipo_registro" name="tipo_registro">
                            <option value="">Seleccione una opción</option>
                            <option value="prospecto">Prospecto</option>
                            <option value="empresa">Empresa</option>
                        </select>
                    </div>
                </form>

                <form id="registroProspectoForm" style="display:none;" method="POST">
                    <input type="hidden" name="tipo_registro" value="prospecto">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" required minlength="2">
                    </div>
                    
                    <div class="form-group">
                        <label for="primerApellido">Primer Apellido:</label>
                        <input type="text" id="primerApellido" name="primerApellido" required pattern="[A-Za-z]{2,}" title="Ingrese al menos 2 letras, sin espacios">
                    </div>
                    
                    <div class="form-group">
                        <label for="segundoApellido">Segundo Apellido:</label>
                        <input type="text" id="segundoApellido" name="segundoApellido" pattern="[A-Za-z]*" title="Solo se permiten letras, sin espacios">
                    </div>
                    
                    <div class="form-group">
                        <label for="fechaNacimiento">Fecha de Nacimiento:</label>
                        <input type="date" id="fechaNacimiento" name="fechaNacimiento" required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="resumen">Resumen Profesional:</label>
                        
                        <textarea id="resumen" name="resumen" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasenia">Contraseña:</label>
                        <input type="password" id="contrasenia" name="contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasenia">Confirmar Contraseña:</label>
                        <input type="password" id="confirmar_contrasenia" name="confirmar_contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="numTel">Número de Teléfono:</label>
                        <input type="tel" id="numTel" name="numTel" required pattern="[0-9]{10}" title="Ingrese un número de 10 dígitos sin espacios ni guiones">
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" value="Registrarse como Prospecto">
                    </div>
                </form>

                <form id="registroEmpresaForm" style="display:none;" method="POST">
                    <input type="hidden" name="tipo_registro" value="empresa">
                    <div class="form-group">
                        <label for="nombre">Nombre de la Empresa:</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ciudad">Ciudad:</label>
                        <input type="text" id="ciudad" name="ciudad" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="calle">Calle:</label>
                        <input type="text" id="calle" name="calle" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="numeroCalle">Número de Calle:</label>
                        <input type="number" id="numeroCalle" name="numeroCalle" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="colonia">Colonia:</label>
                        <input type="text" id="colonia" name="colonia" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="codigoPostal">Código Postal:</label>
                        <input type="text" id="codigoPostal" name="codigoPostal" required pattern="[0-9]{5}" title="El código postal debe tener 5 dígitos">
                    </div>
                    
                    <div class="form-group">
                        <label for="nombreCont">Nombre del Contacto:</label>
                        <input type="text" id="nombreCont" name="nombreCont" required minlength="2">
                    </div>
                    
                    <div class="form-group">
                        <label for="primerApellidoCont">Primer Apellido del Contacto:</label>
                        <input type="text" id="primerApellidoCont" name="primerApellidoCont" required pattern="[A-Za-z]{2,}" title="Ingrese al menos 2 letras, sin espacios">
                    </div>
                    
                    <div class="form-group">
                        <label for="segundoApellidoCont">Segundo Apellido del Contacto:</label>
                        <input type="text" id="segundoApellidoCont" name="segundoApellidoCont" pattern="[A-Za-z]*" title="Solo se permiten letras, sin espacios">
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasenia">Contraseña:</label>
                        <input type="password" id="contrasenia" name="contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_contrasenia">Confirmar Contraseña:</label>
                        <input type="password" id="confirmar_contrasenia" name="confirmar_contrasenia" required minlength="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="numTel">Número de Teléfono:</label>
                        <input type="tel" id="numTel" name="numTel" required pattern="[0-9]{10}" title="Ingrese un número de 10 dígitos sin espacios ni guiones">
                    </div>
                    
                    <div class="form-group">
                        <input type="submit" value="Registrarse como Empresa">
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
        <div class="features-section">
            <div class="feature-container">
                <div class="feature active">
                    <h3>Conecta con el Talento</h3>
                    <p>Encuentra los mejores profesionales para tu empresa con nuestra plataforma de reclutamiento avanzada.</p>
                </div>
                <div class="feature">
                    <h3>Gestión de Vacantes</h3>
                    <p>Publica y administra tus ofertas de trabajo de manera eficiente y sencilla.</p>
                </div>
                <div class="feature">
                    <h3>Seguimiento de Candidatos</h3>
                    <p>Mantén un registro detallado de los candidatos y su progreso en el proceso de selección.</p>
                </div>
                <div class="feature">
                    <h3>Análisis y Reportes</h3>
                    <p>Obtén insights valiosos sobre tus procesos de reclutamiento con nuestras herramientas de análisis.</p>
                </div>
            </div>
            <div class="feature-nav">
                <span class="feature-nav-dot active" data-index="0"></span>
                <span class="feature-nav-dot" data-index="1"></span>
                <span class="feature-nav-dot" data-index="2"></span>
                <span class="feature-nav-dot" data-index="3"></span>
            </div>
            <div class="progress-bar">
                <div class="progress" id="registrationProgress"></div>
            </div>
        </div>
    </div>

    <script>
        const features = document.querySelectorAll('.feature');
        const dots = document.querySelectorAll('.feature-nav-dot');
        let currentFeature = 0;

        function showFeature(index) {
            features[currentFeature].classList.remove('active');
            dots[currentFeature].classList.remove('active');
            features[index].classList.add('active');
            dots[index].classList.add('active');
            currentFeature = index;
        }

        function nextFeature() {
            let next = (currentFeature + 1) % features.length;
            showFeature(next);
        }

        setInterval(nextFeature, 5000);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showFeature(index));
        });

        document.getElementById('tipo_registro').addEventListener('change', function() {
            var tipoRegistro = this.value;
            document.getElementById('registroProspectoForm').style.display = 'none';
            document.getElementById('registroEmpresaForm').style.display = 'none';
            if (tipoRegistro === 'prospecto') {
                document.getElementById('registroProspectoForm').style.display = 'block';
                updateProgress('registroProspectoForm');
            } else if (tipoRegistro === 'empresa') {
                document.getElementById('registroEmpresaForm').style.display = 'block';
                updateProgress('registroEmpresaForm');
            }
        });

        function updateProgress(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input:not([type="submit"]), select, textarea');
            const progressBar = document.getElementById('registrationProgress');

            function calculateProgress() {
                let filledInputs = 0;
                inputs.forEach(inp => {
                    if (inp.value.trim() !== '') {
                        filledInputs++;
                    }
                });
                const progress = (filledInputs / inputs.length) * 100;
                progressBar.style.width = `${progress}%`;
            }

            inputs.forEach(input => {
                input.addEventListener('input', calculateProgress);
            });

            calculateProgress(); // Calcula el progreso inicial
        }
    </script>
</body>
</html>