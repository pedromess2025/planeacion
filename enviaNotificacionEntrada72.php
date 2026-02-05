<?php
    include 'conn.php';

    header('Content-Type: text/html; charset=utf-8');
    $deAsunto = "Recordatorio: Entrega próxima de Equipo - MESS";

    require("PHPMailer-master/src/Exception.php");
    require("PHPMailer-master/src/PHPMailer.php");
    require("PHPMailer-master/src/SMTP.php");
    
    // Buscar entradas que vencen en 3 días y tienen ingenieros asignados
    $sqlCorreo = "SELECT 
                    er.id_registro,
                    er.cliente,
                    er.marca,
                    er.modelo,
                    er.no_serie,
                    er.area,
                    er.fecha_promesa_entrega,
                    GROUP_CONCAT(DISTINCT u.correo SEPARATOR ',') as correos_ing,
                    GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ', ') as nombres_ing
                  FROM entrada_registros er
                  INNER JOIN entrada_log_ingenieros eli ON er.id_registro = eli.id_registro
                  INNER JOIN usuarios u ON eli.id_ing = u.id_usuario
                  WHERE eli.estatus = 'ASIGNADO' 
                    AND er.estatus NOT IN ('Terminado', 'Entregado')
                    AND er.fecha_promesa_entrega BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                  GROUP BY er.id_registro";
    
    $resCorreo = $conn->query($sqlCorreo);
    
    if ($resCorreo->num_rows == 0) {
        exit; // No hay entradas por vencer
    }

    while ($rowCorreo = $resCorreo->fetch_assoc()) {
        $correoResponsable = $rowCorreo["correos_ing"];
        $nombreResponsable = $rowCorreo["nombres_ing"];
        $cliente = $rowCorreo["cliente"];
        $marca = $rowCorreo["marca"];
        $modelo = $rowCorreo["modelo"];
        $serie = $rowCorreo["no_serie"];
        $area = $rowCorreo["area"];
        $fechaPromesa = $rowCorreo["fecha_promesa_entrega"];
        
        if (empty($correoResponsable)) {
            continue; // Si no hay correos, pasar al siguiente
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
        
        $mail->SetFrom("mess.metrologia@gmail.com", "Sistema de Entrada de Equipos MESS");
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
            background-color: #FF6B35;
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
        ⚠️ Recordatorio: Entrega Próxima de Equipo
    </div>
        
    <center>
    <h2>
        Tienes un equipo con fecha de entrega próxima.<br>
        <b>Por favor revisa el estado y completa el trabajo a tiempo.</b><br>
        <br>
        <b>Detalles del Equipo:</b><br>
        Cliente: '.$cliente.'<br>
        Marca: '.$marca.'<br>
        Modelo: '.$modelo.'<br>
        Serie: '.$serie.'<br>
        Área: '.$area.'<br>
        <br>
        <span style="color: #FF6B35; font-size: 18px; font-weight: bold;">
            📅 Fecha de Entrega Estimada: '.date('d/m/Y', strtotime($fechaPromesa)).'
        </span>
        <br><br>
        <a href="https://messbook.com.mx/planeacion/entradaDetalleEntradas" class="btn btn-outline-primary btn-block">
            <i class="fas fa-list fa-lg"></i><br>Revisar Sistema
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

        // También enviar copia a supervisores (opcional)
        //$mail->addAddress('...@mess.com.mx');
        
        foreach ($Arraycorreos as $correo) {
            $correo = trim($correo);
            if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($correo);
            }
        }
        
        if(!$mail->send()) {
            error_log("Error al enviar recordatorio de entrada: " . $mail->ErrorInfo);
        } else {
            error_log("Recordatorio enviado para entrada ID: " . $rowCorreo["id_registro"]);
        }
        
        // Limpiar destinatarios para el siguiente correo
        $mail->clearAddresses();
    }
?>