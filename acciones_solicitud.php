<?php
include 'conn.php';
mysqli_set_charset($conn, "utf8");
$noEmpleado_cookie = isset($_COOKIE['noEmpleado']) ? $_COOKIE['noEmpleado'] : null;
$opcion = $_POST["opcion"];
$noEmpleadoInc = isset($_POST["noEmpleadoInc"]) ? $_POST["noEmpleadoInc"] : $noEmpleado_cookie;
//FUNCION PARA MOSTRAR LOS EMPLEADOS
    if ($opcion == "empleados") {        
        $sql = "SELECT * from usuarios WHERE estatus = 1 ORDER BY nombre";            
        $result = $conn->query($sql);        
        $usuarios = array();
        
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = array(
                'nombre' => $row['nombre'],
                'noEmpleado' => $row['id_usuario']            
            );
        }
        // Devolver los eventos en formato JSON
        echo json_encode($usuarios);
        
    }

//FUNCION PARA GENERAR LA SOLICITUD
    if($opcion == "generarSolicitud"){
        $responsable = $_POST["responsable"];
        $responsable2 = $_POST["responsable2"];
        $responsable3 = $_POST["responsable3"];
        $area = $_POST["area"];
        $ciudad = $_POST["ciudad"];
        $cliente = $_POST["cliente"];
        $ot = $_POST["ot"];
        $fechaPlaneada = $_POST["fechaPlaneada"];
        $duracion = $_POST["duracion"];
        $duracionViaje = $_POST["duracionViaje"];
        $automovil = $_POST["automovil"];
        $estatus = $_POST["estatus"];
        $comentarios = $_POST["comentarios"];
        $fecha = date('Y-m-d H:i:s');
        $noEmpleado = $noEmpleado_cookie;

        $sqlInsert = "INSERT INTO servicios_planeados_mess(service_order_id, order_code, engineer, start_date, durationhr, city, area, ds_cliente, estatus, vehiculo, fecha_captura,  capturado_por, travelhr, engineer2, engineer3, comment)
                                            VALUES ('$ot', '$ot', '$responsable', '$fechaPlaneada', '$duracion', '$ciudad', '$area', '$cliente', '$estatus', '$automovil', '$fecha', '$noEmpleado', '$duracionViaje', '$responsable2', '$responsable3', '$comentarios')";
        //echo $sqlInsert;
        if ($conn->query($sqlInsert) === TRUE) {
            $response = array('status' => 'success', 'message' => 'Incidencia registrada con éxito.');
        } else {
            $response = array('status' => 'error', 'message' => 'Error al registrar la incidencia: ' . $conn->error);
        }
        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }

//FUNCION PARA ACTUALIZAR LA ACTIVIDAD
    if($opcion == "actualizarActividad"){
        $ingeniero = $_POST["ingeniero"];
        $ingeniero2 = $_POST["ingeniero2"];
        $ingeniero3 = $_POST["ingeniero3"];
        $ot = $_POST["ot"];
        $automovil = $_POST["automovil"];
        $fechaActividad = $_POST["fechaActividad"];
        $idActividad = $_POST["idActividad"];
        $estatus = $_POST["estatus"];
        $comment = $_POST["comment"];
        
        $sqlUpdate = "UPDATE servicios_planeados_mess 
                        SET engineer = '$ingeniero',
                            engineer2 = '$ingeniero2',
                            engineer3 = '$ingeniero3',
                            service_order_id = '$ot', 
                            order_code = '$ot', 
                            start_date = '$fechaActividad', 
                            vehiculo = '$automovil',
                            estatus = '$estatus',
                            comment = '$comment'
                        WHERE id = $idActividad";
        //echo $sqlUpdate;
        
        if ($conn->query($sqlUpdate) === TRUE) {
            $response = array('status' => 'success', 'message' => 'Actividad actualizada con éxito.');
        } else {
            $response = array('status' => 'error', 'message' => 'Error al actualizar la actividad: ' . $conn->error);
        }
        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }

//FUNCION PARA MOSTRAR EL INVENTARIO DE VEHICULOS
if ($opcion == "consultarInventarioGeneral") {
    // Conexion a la base de datos
    include '../ControlVehicular/conn.php';
    $rol = $_COOKIE['rol'] ?? $_COOKIE['rolL'];
    $id_usuario = $_COOKIE['id_usuarioL'];
    
    $sqlConsultaVehiculosG = "";
    if ($rol == '3' || $rol == '4'  || $rol == '2') { // 3: Gerente, 4: Administrador
        $sqlConsultaVehiculosG ="SELECT inv.id_vehiculo, inv.placa, inv.modelo, inv.marca, inv.color, inv.anio, inv.usuario, inv.id_usuario, 'AREA' as tipo
                            FROM inventario inv
                            WHERE id_usuario = $id_usuario  OR inv.id_usuario = $id_usuario
                            UNION
                            SELECT inv.id_vehiculo, inv.placa, inv.modelo, inv.marca, inv.color, inv.anio, inv.usuario, inv.id_usuario, 'EXTERNO' as tipo
                            FROM inventario inv
                            WHERE inv.id_usuario != $id_usuario";
    } 
    if ($rol == '1') { 
        $sqlConsultaVehiculosG ="(SELECT inv.id_vehiculo, inv.placa, inv.modelo, inv.marca, inv.color, inv.anio, inv.usuario, inv.id_usuario, 'AREA' as tipo
                            FROM inventario inv
                            INNER JOIN usuarios u ON $id_usuario = u.id_usuario
                            WHERE inv.id_usuario = $id_usuario
                            OR inv.id_usuario IN (SELECT id_usuario FROM usuarios WHERE jefe = u.jefe UNION ALL SELECT id_usuario FROM usuarios WHERE noEmpleado =  u.jefe) ORDER BY inv.usuario)
                            UNION
                            (SELECT inv.id_vehiculo, inv.placa, inv.modelo, inv.marca, inv.color, inv.anio, inv.usuario, inv.id_usuario, 'EXTERNO' as tipo
                            FROM inventario inv
                            WHERE inv.id_usuario != $id_usuario ORDER BY inv.usuario)";
    }
    $result = $conn->query($sqlConsultaVehiculosG);
    $vehiculos = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $vehiculos[] = $row;
        }
    }
    echo json_encode($vehiculos);
    $conn->close();
}

//FUNCION PARA MOSTRAR LAS SOLICITUDES ABIERTAS
if ($opcion == "solicitudesAbiertas") {
    $areas = isset($_POST['area']) && is_array($_POST['area']) ? $_POST['area'] : [];        
    $ingeniero = isset($_POST['ing']) && is_array($_POST['ing']) ? $_POST['ing'] : [];        
    $ciudad = isset($_POST['ciudad']) && is_array($_POST['ciudad']) ? $_POST['ciudad'] : [];
    $estatus = isset($_POST['estatus']) && is_array($_POST['estatus']) ? $_POST['estatus'] : [];
    $fechaHoy = date('Y-m-d');
    $fechaInicio = date('Y-m-d', strtotime($fechaHoy . ' -50 days'));
    // Consulta base
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, u.nombre, IFNULL(u2.nombre,'') AS nombre2, IFNULL(u3.nombre,'') AS nombre3, 
                    IF(ot.capturado_por = $noEmpleado_cookie, 'SI', 'NO') AS capturo, comment_logistic, estatus_logistic
            FROM servicios_planeados_mess ot
            inner join usuarios u on ot.engineer = u.id_usuario 
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario 
            WHERE ot.start_date >= ?"; // 1=Programada, 2=En Proceso, 3=Completada, 4=Cancelada    

        // --- 1. Inicialización de arrays para cláusulas WHERE y parámetros ---
        $whereClauses = [];
        $params = [$fechaInicio]; // Array para los parámetros de la consulta preparada. $fechaInicio es el primer parámetro para el WHERE.
        $param_types = "s";       // String para los tipos de los parámetros (s = string);

        // --- 2. Manejo de múltiples áreas seleccionadas
        if (!empty($areas)) {
            $placeholders = implode(',', array_fill(0, count($areas), '?'));
            // Nota: Asumo que el campo en la BD es 'ot.area' y no el código OT más complejo.
            $whereClauses[] = "ot.area IN ($placeholders)"; 

            foreach ($areas as $area_item) {
                $params[] = $area_item;
                $param_types .= "s";
            }
        } 

        // --- 3. Manejo de la ciudad
        if (!empty($ciudad)) {
            // CORRECCIÓN 3: Si $ciudad es un array, se maneja correctamente con IN
            $placeholders = implode(',', array_fill(0, count($ciudad), '?'));
            $whereClauses[] = "ot.city IN ($placeholders)";

            foreach ($ciudad as $ciudad_item) {
                $params[] = $ciudad_item;
                $param_types .= "s";
            }
        }

        // --- 4. Manejo del ingeniero
        if (!empty($ingeniero)) {
            $count = count($ingeniero);
            $placeholders = implode(',', array_fill(0, $count, '?'));
            $whereClauses[] = "(ot.engineer IN ($placeholders) OR ot.engineer2 IN ($placeholders) OR ot.engineer3 IN ($placeholders))";

            // Añadir la lista de ingenieros al array de parámetros TRES VECES (Correcto para los 3 IN)
            for ($i = 0; $i < 3; $i++) {
                foreach ($ingeniero as $ingeniero_item) {
                    $params[] = $ingeniero_item;
                    $param_types .= "s"; 
                }
            }
        }

        // --- 5. Manejo del estatus
        if (!empty($_POST['estatus']) && is_array($_POST['estatus'])) {
            $estatus = $_POST['estatus'];
            $placeholders = implode(',', array_fill(0, count($estatus), '?'));
            $whereClauses[] = "ot.estatus IN ($placeholders)";

            foreach ($estatus as $estatus_item) {
                $params[] = $estatus_item;
                $param_types .= "s";
            }
        }
        
        // --- 6. Construcción Final
        if (!empty($whereClauses)) {
            $sql .= " AND " . implode(' AND ', $whereClauses);
        }
        $sql .= " GROUP BY ot.id
            ORDER BY ot.id DESC";

        // --- 6. Ejecución de la Consulta Preparada ---
        if ($stmt = $conn->prepare($sql)) {
            // Enlazar los parámetros dinámicamente        
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $actividades = [];
                while ($row = $result->fetch_assoc()) {
                    $actividades[] = $row;
                }
                echo json_encode($actividades);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se encontraron actividades planeadas o error en la consulta.', 'sql' => $sql, 'params' => $params]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        }
}

//FUNCION PARA MOSTRAR LAS CIUDADES
if($opcion == "consultarCiudades"){
    $sql = "SELECT * FROM ciudades_mexico ORDER BY estado, ciudad";
    $result = $conn->query($sql);
    $ciudades = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ciudades[] = $row;
        }
    }
    echo json_encode($ciudades);
    $conn->close();
}

if ($opcion == "solicitudApoyoLogistica") {
    $order_code = $_POST["order_code"];
    $sql = "UPDATE servicios_planeados_mess 
            SET estatus_logistic = 'Solicitado'
            WHERE id = $order_code";

    if ($conn->query($sql) === TRUE) {  
        $solicitud = array('status' => 'success', 'message' => 'Solicitud de apoyo logístico enviada con éxito.');
    } else {
        $solicitud = array('status' => 'error', 'message' => 'Error al enviar la solicitud de apoyo logístico: ' . $conn->error);
    }
    echo json_encode($solicitud);
    $conn->close();
}


if($opcion == "responderSolicitudLogistica"){
    $idActividad = $_POST["idActividad"];
    $comentarioLogistica = $_POST["commentLogistica"];
    $estatus = $_POST["accion"]; // 'aceptada' o 'rechazada'
    
    $sqlUpdate = "UPDATE servicios_planeados_mess 
                    SET comment_logistic = '$comentarioLogistica',
                        estatus_logistic = '$estatus'
                    WHERE id = $idActividad";
    
    if ($conn->query($sqlUpdate) === TRUE) {
        $response = array('status' => 'success', 'message' => 'Comentario guardado con éxito.');
    } else {
        $response = array('status' => 'error', 'message' => 'Error al guardar el comentario: ' . $conn->error);
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>  