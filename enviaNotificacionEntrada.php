<?php
    include 'conn.php';

    header('Content-Type: text/html; charset=utf-8');
    $deAsunto = "Asignación de Equipo - MESS";

    require("PHPMailer-master/src/Exception.php");
    require("PHPMailer-master/src/PHPMailer.php");
    require("PHPMailer-master/src/SMTP.php");
    
    // Crear carpeta de logs si no existe
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0777, true);
    }
    
    $logFile = __DIR__ . '/logs/notificacion_entrada.log';
    
    function logMessage($msg) {
        global $logFile;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $msg\n", FILE_APPEND);
    }
    
    // Obtener ID de entrada
    $id_entrada = isset($_POST['id_entrada']) ? intval($_POST['id_entrada']) : 0;
    
    if ($id_entrada <= 0) {
        exit;
    }

    // Obtener datos del equipo y correos de ingenieros asignados
    $sqlCorreo = "SELECT er.*, GROUP_CONCAT(u.correo SEPARATOR ',') as correos_ing, GROUP_CONCAT(u.nombre SEPARATOR ',') as nombres_ing
                  FROM entrada_registros er
                  LEFT JOIN entrada_log_ingenieros eli ON er.id_registro = eli.id_registro
                  LEFT JOIN usuarios u ON eli.id_ing = u.id_usuario
                  WHERE er.id_registro = $id_entrada AND eli.estatus = 'ASIGNADO'
                  GROUP BY er.id_registro";
    $resCorreo = $conn->query($sqlCorreo);

    $rowCorreo = $resCorreo->fetch_assoc();
    $correoResponsable = $rowCorreo["correos_ing"];
    $cliente = $rowCorreo["cliente"];
    $marca = $rowCorreo["marca"];
    $modelo = $rowCorreo["modelo"];
    $serie = $rowCorreo["no_serie"];
    $area = $rowCorreo["area"];

    if (empty($correoResponsable)) {
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
    
    $mail->SetFrom("mess.metrologia@gmail.com", "Sistema de Planeación MESS");
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
        Nueva Asignación de Equipo
    </div>
        
    <center>
    <h2>
        Se te ha asignado un nuevo equipo.<br>
        <b>Por favor ingresa al sistema de ENTRADAS DE EQUIPO para darle seguimiento.</b><br>
        <br>
        Cliente: '.$cliente.'<br>
        Marca: '.$marca.'<br>
        Modelo: '.$modelo.'<br>
        Serie: '.$serie.'<br>
        Área: '.$area.'<br>
        <br>
        <a href="https://messbook.com.mx/planeacion" class="btn btn-outline-primary btn-block">
            <i class="fas fa-list fa-lg"></i><br>Revisar
        </a>
    </h2>
    </center> 
    
    <center>
    <p>Este es un mensaje automático, por favor no responda a este correo.</p>
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

    // Envío de correo
    $Arraycorreos = explode(",", $correoResponsable);
    
    foreach ($Arraycorreos as $correo) {
        $correo = trim($correo);
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($correo);
        }
    }
    
    $mail->send();
?>