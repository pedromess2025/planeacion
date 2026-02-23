<?php
    include 'conn.php';

    header('Content-Type: text/html; charset=utf-8');
    $deAsunto = "Alerta: Equipos sin Ingeniero Asignado - MESS";

    require("PHPMailer-master/src/Exception.php");
    require("PHPMailer-master/src/PHPMailer.php");
    require("PHPMailer-master/src/SMTP.php");
    
    // Buscar entradas sin ingenieros asignados después de 2 días
        $sqlBuscaRegistros="SELECT 
                        er.id_registro,
                        er.cliente,
                        er.marca,
                        er.modelo,
                        er.no_serie,
                        er.area,
                        er.fecha_registro
                    FROM entrada_registros er
                    WHERE DATEDIFF(CURDATE(), er.fecha_registro) >= 2
                    AND NOT EXISTS (
                        SELECT 1
                        FROM entrada_log_ingenieros eli
                        WHERE eli.id_registro = er.id_registro
                            AND eli.estatus = 'ASIGNADO'
                            AND eli.id_ing IS NOT NULL)";
    $resEquipoSinIng = $conn->query($sqlBuscaRegistros);
    
    if ($resEquipoSinIng->num_rows == 0) {
        exit; // No hay entradas sin asignar
    }

    while ($rowEquipoSinIng = $resEquipoSinIng->fetch_assoc()) {
        $cliente = $rowEquipoSinIng["cliente"];
        $marca = $rowEquipoSinIng["marca"];
        $modelo = $rowEquipoSinIng["modelo"];
        $serie = $rowEquipoSinIng["no_serie"];
        $area = $rowEquipoSinIng["area"];
        $fechaRegistro = $rowEquipoSinIng["fecha_registro"];
        $diasPasados = floor((strtotime(date('Y-m-d')) - strtotime($fechaRegistro)) / 86400);

        // Obtener correos de administradores (No. Empleado) 
        $adminIds = array(45, 177, 555, 523);
        $adminIdList = implode(',', array_map('intval', $adminIds));
        $adminCorreos = array();

        if (!empty($adminIdList)) {
            $sqlAdmins = "SELECT correo FROM usuarios WHERE noEmpleado IN ($adminIdList)";
            $resAdmins = $conn->query($sqlAdmins);
            if ($resAdmins) {
                while ($rowAdmin = $resAdmins->fetch_assoc()) {
                    if (!empty($rowAdmin['correo'])) {
                        $adminCorreos[] = $rowAdmin['correo'];
                    }
                }
            }
        }

        if (empty($adminCorreos)) {
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
            background-color: #DC3545;
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
        🚨 Alerta: Equipo Sin Ingeniero Asignado
    </div>
        
    <center>
    <h2>
        El siguiente equipo lleva '.$diasPasados.' días registrado y aún no tiene ingeniero asignado.<br>
        <b>Por favor asigna un ingeniero a la brevedad.</b><br>
        <br>
        <b>Detalles del Equipo:</b><br>
        Cliente: '.$cliente.'<br>
        Marca: '.$marca.'<br>
        Modelo: '.$modelo.'<br>
        Serie: '.$serie.'<br>
        Área: '.$area.'<br>
        <br>
        <span style="color: #DC3545; font-size: 18px; font-weight: bold;">
            📅 Fecha de Registro: '.date('d/m/Y', strtotime($fechaRegistro)).'<br>
            ⏱️ Días sin asignar: '.$diasPasados.' días
        </span>
        <br><br>
        <a href="https://messbook.com.mx/planeacion/entradaDetalleEntradas.php?id='.$rowEquipoSinIng["id_registro"].'" class="btn btn-outline-danger btn-block">
            <i class="fas fa-user-plus fa-lg"></i><br>Asignar Ingeniero
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

        // Envío de correo solo a administradores
        foreach ($adminCorreos as $correoAdmin) {
            if (!empty($correoAdmin)) {
                $mail->addAddress($correoAdmin);
            }
        }

        if(!$mail->send()) {
            error_log("Error al enviar alerta sin asignar: " . $mail->ErrorInfo);
        } else {
            error_log("Alerta enviada para entrada ID: " . $rowEquipoSinIng["id_registro"]);
        }
        
        $mail->clearAddresses();
    }
?>