<?php
    include 'conn.php';

    header('Content-Type: text/html; charset=utf-8');
    $deAsunto = "Asignacion de Actividad - MESS";

    require("PHPMailer-master/src/Exception.php");
    require("PHPMailer-master/src/PHPMailer.php");
    require("PHPMailer-master/src/SMTP.php");

    // Crear carpeta de logs si no existe
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0777, true);
    }

    $logFile = __DIR__ . '/logs/notificacion_actividad.log';

    function logMessage($msg) {
        global $logFile;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $msg\n", FILE_APPEND);
    }

    // Obtener ID de actividad
    $id_actividad = isset($_POST['id_actividad']) ? intval($_POST['id_actividad']) : 0;

    if ($id_actividad <= 0) {
        exit;
    }

    // Obtener datos de la actividad y correos de ingenieros asignados
    $sqlCorreo = "SELECT s.*, 
                        GROUP_CONCAT(DISTINCT u.correo SEPARATOR ',') AS correos_ing,
                        GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ',') AS nombres_ing
                  FROM servicios_planeados_mess s
                  LEFT JOIN usuarios u 
                        ON u.id_usuario IN (s.engineer, s.engineer2, s.engineer3)
                  WHERE s.id = $id_actividad
                  GROUP BY s.id";
    $resCorreo = $conn->query($sqlCorreo);

    if (!$resCorreo || $resCorreo->num_rows === 0) {
        logMessage("No se encontro actividad para id: $id_actividad");
        exit;
    }

    $rowCorreo = $resCorreo->fetch_assoc();
    $correoResponsable = $rowCorreo["correos_ing"];
    $nombresIng = $rowCorreo["nombres_ing"];
    $cliente = $rowCorreo["ds_cliente"];
    $ciudad = $rowCorreo["city"];
    $area = $rowCorreo["area"];
    $ot = $rowCorreo["order_code"];
    $fechaPlaneada = $rowCorreo["start_date"];
    $duracion = $rowCorreo["durationhr"];
    $duracionViaje = $rowCorreo["travelhr"];

    if (empty($correoResponsable)) {
        logMessage("Actividad sin correos asignados. id: $id_actividad");
        exit;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer();

    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'ssl';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 465;
    $mail->IsHTML(true);
    $mail->CharSet = 'UTF-8';

    $mail->Username = "mess.metrologia@gmail.com";
    $mail->Password = "hglidvwsxcbbefhe";

    $mail->SetFrom("mess.metrologia@gmail.com", "Sistema de Planeacion MESS");
    $mail->Subject = $deAsunto;
    $mail->Body = ' 
<html lang="es">
<head>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <center> 
        <img src="https://messbook.com.mx/mess_logooficial.jpg" style = "width: 20%">
    </center>
    
    <meta charset="UTF-8">
    <style>
        .header {
            background-color: #007BFF;
            color: #ffffff;
            padding: 10px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
        }
    </style>
</head>
<body>
    <div class="header">
        Nueva asignacion de actividad
    </div>
        
    <center>
    <h2>
        Se te ha asignado una nueva actividad.<br>
        <b>Por favor ingresa al sistema de PLANEACION para darle seguimiento.</b><br>
        <br>
        OT: ' . $ot . '<br>
        Fecha planeada: ' . $fechaPlaneada . '<br>
        Ciudad: ' . $ciudad . '<br>
        Cliente: ' . $cliente . '<br>
        <br>
        <a href="https://messbook.com.mx/planeacion/index" class="btn btn-outline-primary btn-block">
            <i class="fas fa-list fa-lg"></i><br>Revisar
        </a>
    </h2>
    </center> 
    
    <center>
    <p>Este es un mensaje automatico, por favor no responda a este correo.</p>
    </center>
    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src = "vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src = "vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src = "js/sb-admin-2.min.js"></script>
</body>
</html>';

    // Envio de correo
    $Arraycorreos = explode(",", $correoResponsable);

    foreach ($Arraycorreos as $correo) {
        $correo = trim($correo);
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($correo);
        }
    }

    $mail->send();
?>