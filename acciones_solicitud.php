<?php
// Conexion a la base de datos
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
                'noEmpleado' => $row['noEmpleado']            
            );
        }
        
        // Devolver los eventos en formato JSON
        
        echo json_encode($usuarios);
    }


//FUNCION PARA GENERAR LA SOLICITUD
    if($opcion == "generarSolicitud"){
        $responsable = $_POST["responsable"];
        $area = $_POST["area"];
        $ciudad = $_POST["ciudad"];
        $cliente = $_POST["cliente"];
        $ot = $_POST["ot"];
        $fechaPlaneada = $_POST["fechaPlaneada"];
        $duracion = $_POST["duracion"];
        $automovil = $_POST["automovil"];
        $estatus = $_POST["estatus"];
        
        $fecha = date('Y-m-d H:i:s');
        
        $noEmpleado = $noEmpleado_cookie;

        $sqlInsert = "INSERT INTO servicios_planeados_mess(service_order_id, order_code, engineer, start_date, durationhr, city, area, ds_cliente, estatus, vehiculo, fecha_captura,  capturado_por)
                                            VALUES ('$ot', '$ot', '$responsable', '$fechaPlaneada', '$duracion', '$ciudad', '$area', '$cliente', '$estatus', '$automovil', '$fecha', '$noEmpleado')";
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

    if($opcion == "actualizarActividad"){
        $ingeniero = $_POST["ingeniero"];
        $ot = $_POST["ot"];
        $automovil = $_POST["automovil"];
        $fechaActividad = $_POST["fechaActividad"];
        $idActividad = $_POST["idActividad"];
        
        $sqlUpdate = "UPDATE servicios_planeados_mess 
                        SET engineer = '$ingeniero', 
                            service_order_id = '$ot', 
                            order_code = '$ot', 
                            start_date = '$fechaActividad', 
                            vehiculo = '$automovil' 
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



if ($opcion == "consultarInventarioGeneral") {
    
    // Conexion a la base de datos
    include '../ControlVehicular/conn.php';
    $rol = $_COOKIE['rol'];
    $id_usuario = $_COOKIE['id_usuarioL'];
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

if ($opcion == "solicitudesAbiertas") {

    $fechaHoy = date('Y-m-d');
    $fechaInicio = date('Y-m-d', strtotime($fechaHoy . ' -50 days'));

    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, u.nombre
            FROM servicios_planeados_mess ot
            inner join usuarios u on ot.engineer = u.noEmpleado
            WHERE DATE(ot.start_date) >= $fechaInicio
            ORDER BY ot.fecha_captura DESC";
        
    //echo $sql;

    $result = $conn->query($sql);

    $actividades = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $actividades[] = $row;
        }
    }
    echo json_encode($actividades);
    $conn->close();
}

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


?>  