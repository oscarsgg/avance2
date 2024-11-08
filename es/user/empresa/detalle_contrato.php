<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . '/Outsourcing/config.php');

// Verificar si el usuario está logueado y tiene permiso para ver este contrato
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'PRO' && $_SESSION['user_role'] !== 'EMP')) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del contrato de la URL
$contrato_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($contrato_id === 0) {
    die("ID de contrato no válido");
}

// Obtener los detalles del contrato
$query = "SELECT c.*, v.titulo AS vacante_titulo, v.es_directo, v.empresa AS empresa_id,
                 e.nombre AS empresa_nombre, e.ciudad AS empresa_ciudad, e.calle AS empresa_calle,
                 e.numeroCalle AS empresa_numeroCalle, e.colonia AS empresa_colonia,
                 e.codigoPostal AS empresa_codigoPostal, e.nombreCont AS empresa_nombreCont,
                 e.primerApellidoCont AS empresa_primerApellidoCont, e.segundoApellidoCont AS empresa_segundoApellidoCont,
                 p.nombre AS prospecto_nombre, p.primerApellido AS prospecto_primerApellido,
                 p.segundoApellido AS prospecto_segundoApellido, tc.nombre AS tipo_contrato_nombre
          FROM Contrato c
          JOIN Vacante v ON c.vacante = v.numero
          JOIN Empresa e ON v.empresa = e.numero
          JOIN Prospecto p ON c.prospecto = p.numero
          JOIN Tipo_Contrato tc ON c.tipo_contrato = tc.codigo
          WHERE c.numero = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $contrato_id);
$stmt->execute();
$resultado = $stmt->get_result();
$contrato = $resultado->fetch_assoc();

if (!$contrato) {
    die("Contrato no encontrado");
}

// Obtener los requerimientos de la vacante
$query_req = "SELECT descripcion FROM Requerimiento WHERE vacante = ?";
$stmt_req = $conexion->prepare($query_req);
$stmt_req->bind_param("i", $contrato['vacante']);
$stmt_req->execute();
$resultado_req = $stmt_req->get_result();
$requerimientos = $resultado_req->fetch_all(MYSQLI_ASSOC);

// Función para formatear la fecha
function formatearFecha($fecha) {
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $fechaObj = new DateTime($fecha);
    $dia = $fechaObj->format('j');
    $mes = $meses[(int)$fechaObj->format('n') - 1];
    $año = $fechaObj->format('Y');
    return "$dia de $mes de $año";
}

// Generar el contenido del contrato
$contenido_contrato = "
<h1 style='text-align: center;'>CONTRATO INDIVIDUAL DE TRABAJO</h1>

<p style='text-align: right;'>{$contrato['empresa_ciudad']}, a " . formatearFecha($contrato['fechaInicio']) . "</p>

<p>CONTRATO QUE CELEBRAN POR UNA PARTE</p>
";

if (!$contrato['es_directo']) {
    $contenido_contrato .= "
    <p>TalentBridge, persona moral de nacionalidad MEXICANA con domicilio en:
    Carretera Libre Tijuana-Tecate Km 10 Fracc. El Refugio, 22253 Redondo, Tijuana.</p>

    <p>Representada por:
    Christopher Anahel Gonzalez Leyva, persona mayor de edad, con las facultades necesarias para la firma de este contrato.</p>

    <p>Y</p>
    ";
}

$contenido_contrato .= "
<p>{$contrato['empresa_nombre']}, persona moral de nacionalidad MEXICANA que cuenta con domicilio basado en:</p>
<p>{$contrato['empresa_calle']} {$contrato['empresa_numeroCalle']}, {$contrato['empresa_colonia']}, {$contrato['empresa_ciudad']}, {$contrato['empresa_codigoPostal']}</p>

<p>Actuando en este contrato a través de su representante {$contrato['empresa_nombreCont']} {$contrato['empresa_primerApellidoCont']} {$contrato['empresa_segundoApellidoCont']}, persona mayor de edad que cuenta con las facultades suficientes y necesarias para celebrar el presente contrato.</p>

<p>Y POR LA OTRA</p>

<p>{$contrato['prospecto_nombre']} {$contrato['prospecto_primerApellido']} {$contrato['prospecto_segundoApellido']} persona fisica mayor de edad.</p>

<p>QUIENES SE RECONOCEN EXPRESA Y RECÍPROCAMENTE CON CAPACIDAD PLENA Y SUFICIENTE PARA CELEBRAR EL PRESENTE CONTRATO INDIVIDUAL DE TRABAJO, Y PARA TAL EFECTO, {$contrato['empresa_nombre']} SERÁ IDENTIFICADO EN EL PRESENTE CONTRATO COMO \"PATRÓN\" Y {$contrato['prospecto_nombre']} {$contrato['prospecto_primerApellido']} SERÁ IDENTIFICADO COMO \"TRABAJADOR\"; ADEMÁS SE PODRÁ HACER REFERENCIA A ELLAS DE MANERA CONJUNTA COMO \"LAS PARTES\"; EN ESTE SENTIDO, LAS PARTES MANIFIESTAN EN PRIMER LUGAR LAS SIGUIENTES:</p>

<h2 style='text-align: center;'>DECLARACIONES</h2>

<p>I. LAS PARTES manifiestan que reúnen los requisitos legales exigidos para la celebración del presente contrato.</p>

<p>II. EL PATRÓN manifiesta que tiene interés en contratar los servicios DEL TRABAJADOR</p>

<p>III. EL TRABAJADOR manifiesta que tiene la capacitación y aptitudes para desarrollar las actividades que le encomiende EL PATRÓN en términos del presente contrato</p>

<p>IV. EL TRABAJADOR señala además que está de acuerdo en desempeñar los requerimientos DEL PATRON y ajustarse a las condiciones generales de trabajo sobre las cuales prestará SUS servicios.</p>

<p>V. Habiendo llegado las Partes, libre y espontáneamente, a una coincidencia mutua de sus voluntades, formalizan el presente CONTRATO INDIVIDUAL DE TRABAJO, en adelante únicamente el \"Contrato\" o el \"Contrato de Trabajo\", el cual tiene por objeto el establecimiento de una relación laboral entre LAS PARTES, que se regirá por las siguientes:</p>

<h2 style='text-align: center;'>CLAUSULAS</h2>

<p>PRIMERA. DEL TRABAJO A DESEMPEÑAR</p>
";

switch ($contrato['tipo_contrato_nombre']) {
    case 'Contrato permanente':
        $contenido_contrato .= "<p>La prestación de los servicios DEL TRABAJADOR será con el puesto de {$contrato['vacante_titulo']}, el cual será con carácter indefinido a partir del día " . formatearFecha($contrato['fechaInicio']) . " y las labores principales que deberá desempeñar EL TRABAJADOR consistirán en:</p>";
        break;
    case 'Contrato definido (plazo fijo)':
        $contenido_contrato .= "<p>La prestación de los servicios DEL TRABAJADOR será con el puesto de " . strtoupper($contrato['vacante_titulo']) . ", a partir de " . formatearFecha($contrato['fechaInicio']) . " y hasta el " . formatearFecha($contrato['fechaCierre']) . " y las labores principales que deberá desempeñar EL TRABAJADOR consistirán en:</p>";
        break;
    case 'Contrato por obra o proyecto':
        $contenido_contrato .= "<p>La prestación de los servicios DEL TRABAJADOR será con el puesto de {$contrato['vacante_titulo']}, bajo la modalidad de obra o proyecto. La duración del contrato estará determinada por el tiempo necesario para la finalización de las actividades asignadas, comenzando a partir del día " . formatearFecha($contrato['fechaInicio']) . ". Las labores principales que deberá desempeñar el TRABAJADOR consistirán en:</p>";
        break;
}

$contenido_contrato .= "<ul>";
foreach ($requerimientos as $req) {
    $contenido_contrato .= "<li>{$req['descripcion']}</li>";
}
$contenido_contrato .= "</ul>";

$contenido_contrato .= "
<p>SEGUNDA. DEL LUGAR DE TRABAJO.</p>
<p>EL TRABAJADOR prestará sus servicios en el centro de trabajo ubicado en: {$contrato['empresa_calle']} {$contrato['empresa_numeroCalle']}, {$contrato['empresa_colonia']}, {$contrato['empresa_ciudad']}, {$contrato['empresa_codigoPostal']}. No obstante lo anterior, EL PATRÓN se reserva el derecho de modificar el lugar de trabajo DEL TRABAJADOR, respetando los derechos establecidos en favor de este; comunicando en todo caso la modificación del lugar de trabajo de manera oportuna y siempre que dicho cambio se encuentre justificado por razones económicas, técnicas, organizativas o de producción.</p>

<p>TERCERA. DE LA JORNADA DE TRABAJO.</p>
<p>La duración de la jornada será de {$contrato['horasDiarias']} horas por día, las cuales serán prestadas conforme al siguiente horario: {$contrato['horario']}.</p>

<p>Cuando por circunstancias extraordinarias la jornada de trabajo llegue a prolongarse, los servicios prestados durante el tiempo excedente se considerarán como extraordinarios y se pagarán a razón del cien por ciento adicional al salario establecido para las horas de trabajo normal.</p>

<p>Las horas de trabajo extraordinario no podrán exceder de tres horas diarias ni de tres veces en una misma semana. En este sentido, EL TRABAJADOR en ningún caso podrá labor, por tiempo extraordinario, salvo que EL PATRÓN lo autorice o lo requiera expresamente.</p>

<p>Cuando la prolongación del tiempo extraordinario exceda de nueve horas a la semana, EL PATRÓN estará obligado a pagar AL TRABAJADOR el tiempo excedente a razón de un doscientos por ciento más del salario que le corresponda a las horas de la jornada establecida.</p>

<p>CUARTA. DEL SALARIO</p>
<p>EL TRABAJADOR percibirá, por la prestación de los servicios a que se refiere el presente contrato, un salario de {$contrato['salario']} mensuales, al cual se aplicará la parte proporcional correspondiente a los descansos semanales.</p>

<p>Una vez recibido el salario por parte DEL TRABAJADOR, este se encontrará obligado a firmar las constancias de pago respectivas.</p>

<p>QUINTA. DE LAS OBLIGACIONES DEL TRABAJADOR</p>
<p>EL TRABAJADOR tendrá, durante el tiempo que se encuentre vigente el presente contrato, las siguientes obligaciones:</p>
<p>I. Estará obligado a prestar los servicios personales que se especifican en la cláusula primera del presente contrato, subordinado jurídicamente AL PATRÓN. Dichos servicios deberán ser proporcionados con esmero, dedicación y eficacia.</p>
<p>II. Acatará en el desempeño de su trabajo todas las órdenes, circulares y disposiciones que dicte EL PATRÓN y aquellas que se encuentren establecidas en los ordenamientos legales que le sean aplicables.</p>
<p>III. Se someterá a los exámenes médicos que periódicamente establezca EL PATRÓN, en los términos del artículo 134 de la Ley Federal del Trabajo, a fin de mantener en forma óptima sus facultades físicas e intelectuales, para el mejor desempeño de sus funciones. El médico que practique los reconocimientos será designado y retribuido por EL PATRÓN.</p>
<p>IV. Deberá realizar el trabajo que se le encomiende observando las normas de calidad y fabricación que EL PATRÓN le indique.</p>

<p>SEXTA. DE LA TERMINACION DEL CONTRATO</p>
<p>Al finalizar el contrato, EL TRABAJADOR recibirá sin excepción alguna: los salarios que se encuentren pendientes de pago, y el pago de las partes proporcionales que correspondan al aguinaldo, vacaciones y prima vacacional. Lo anterior, además de las cantidades e indemnizaciones que le correspondan con motivo de su antigüedad.</p>

<p>SEPTIMA. DE LA INTEGRIDAD DEL ACUERDO</p>
<p>LAS PARTES reconocen y aceptan que este Contrato y sus adiciones constituyen un acuerdo total entre ellas, por lo que desde el momento de su firma quedarán sin efecto cualquier acuerdo o negociación previa, prevaleciendo lo dispuesto en este instrumento respecto de cualquier otro contrato o convenio.</p>

<p>Asimismo, las Partes reconocen que, en caso de existir, documentos Anexos y/o adjuntos al presente Contrato de trabajo, estos forman parte o integran el mismo, para todos los efectos legales.</p>

<p>Además, si alguna de las cláusulas resultara nula en virtud de la aplicación, interpretación o modificación de la legislación laboral, esta se tendrá por no puesta, manteniendo su vigencia el resto del Contrato. Llegado este caso, LAS PARTES se comprometen, a adaptar el texto de las cláusulas o partes del Contrato afectadas, a la aplicación, interpretación o modificaciones legales.</p>

<p>OCTAVA. DE LA LEGISLACIÓN Y JURISDICCIÓN APLICABLE.</p>
<p>Respecto a las obligaciones y derechos que mutuamente les corresponden y que no hayan sido motivo de cláusula expresa en el presente contrato, LAS PARTES se sujetarán a las disposiciones de la Ley Federal del Trabajo.</p>

<p>Para todo lo relativo a la interpretación y cumplimiento de las obligaciones derivadas del presente contrato, las partes acuerdan someterse a la jurisdicción y competencia de la junta local que conforme a derecho deba conocer el asunto en razón del lugar en el que se desempeña el trabajo, con renuncia a su propio fuero en caso que este les aplique y sea procedente por razón de domicilio, vecindad, o por cualquier otra naturaleza.</p>

<p>Leído que fue el presente instrumento y enteradas las partes de su contenido y alcance, lo firman de conformidad en el lugar y fecha indicados al inicio del  documento.</p>
";

// Verificar si se ha enviado una firma
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['firma_empresa']) && $_SESSION['user_role'] === 'EMP') {
        $firma_empresa = $_POST['firma_empresa'];
        // Guardar la firma de la empresa en la base de datos
        $query = "UPDATE Contrato SET firma_empresa = ? WHERE numero = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("si", $firma_empresa, $contrato_id);
        $stmt->execute();
    } elseif (isset($_POST['firma_prospecto']) && $_SESSION['user_role'] === 'PRO') {
        $firma_prospecto = $_POST['firma_prospecto'];
        // Guardar la firma del prospecto en la base de datos
        $query = "UPDATE Contrato SET firma_prospecto = ? WHERE numero = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("si", $firma_prospecto, $contrato_id);
        $stmt->execute();
    }
}

// Obtener las firmas guardadas
$query = "SELECT firma_empresa, firma_prospecto FROM Contrato WHERE numero = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $contrato_id);
$stmt->execute();
$resultado = $stmt->get_result();
$firmas = $resultado->fetch_assoc();

// Inicializar las variables de firma si no existen
$firma_empresa = isset($firmas['firma_empresa']) ? $firmas['firma_empresa'] : null;
$firma_prospecto = isset($firmas['firma_prospecto']) ? $firmas['firma_prospecto'] : null;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Trabajo - <?php echo $contrato['vacante_titulo']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #000;
        }
        ul {
            padding-left: 20px;
        }
        .signature-container {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid black;
            width: 200px;
            height: 50px;
            margin: 0 auto;
        }
        #signatureCanvas {
            border: 1px solid #000;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body>
    <?php echo $contenido_contrato; ?>

    <div style='display: flex; justify-content: space-between; margin-top: 50px;'>
        <div style='text-align: center;'>
            <?php if (!empty($firma_empresa)): ?>
                <img src="<?php echo htmlspecialchars($firma_empresa); ?>" alt="Firma del PATRÓN" style="max-width: 200px; max-height: 100px;">
            <?php else: ?>
                <p>Firma pendiente</p>
            <?php endif; ?>
            <div style='border-bottom: 1px solid black; width: 200px;'></div>
            <p>Representante de <?php echo $contrato['empresa_nombre']; ?></p>
        </div>
        <div style='text-align: center;'>
            <?php if (!empty($firma_prospecto)): ?>
                <img src="<?php echo htmlspecialchars($firma_prospecto); ?>" alt="Firma del TRABAJADOR" style="max-width: 200px; max-height: 100px;">
            <?php else: ?>
                <p>Firma pendiente</p>
            <?php endif; ?>
            <div style='border-bottom: 1px solid black; width: 200px;'></div>
            <p><?php echo $contrato['prospecto_nombre'] . " " . $contrato['prospecto_primerApellido'] . " ".  $contrato['prospecto_segundoApellido']; ?></p>
        </div>
    </div>

    <!-- <h2>Firmas</h2>
    <div class="signature-container">
        <div class="signature-box">
            <h3>Firma del PATRÓN</h3>
            
        </div>
        <div class="signature-box">
            <h3>Firma del TRABAJADOR</h3>
            
        </div>
    </div> -->

    <?php if ($_SESSION['user_role'] === 'PRO' && empty($firma_prospecto)): ?>
    <h2>Firma del TRABAJADOR</h2>
    <canvas id="signatureCanvas" width="400" height="200"></canvas>
    <br>
    <button id="clearButton">Limpiar firma</button>
    <button id="saveButton">Guardar firma</button>
    <form id="signatureForm" method="POST">
        <input type="hidden" name="firma_prospecto" id="firma_prospecto">
    </form>
    <?php endif; ?>

    <script>
        var canvas = document.getElementById('signatureCanvas');
        var signaturePad = new SignaturePad(canvas);

        document.getElementById('clearButton').addEventListener('click', function() {
            signaturePad.clear();
        });

        document.getElementById('saveButton').addEventListener('click', function() {
            if (signaturePad.isEmpty()) {
                alert('Por favor, proporcione una firma antes de guardar.');
            } else {
                var signatureData = signaturePad.toDataURL();
                document.getElementById('<?php echo $_SESSION['user_role'] === 'EMP' ? "firma_empresa" : "firma_prospecto"; ?>').value = signatureData;
                document.getElementById('signatureForm').submit();
            }
        });
    </script>
</body>
</html>