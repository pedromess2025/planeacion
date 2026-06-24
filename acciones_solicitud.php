<?php
include 'conn.php';
mysqli_set_charset($conn, "utf8");
$noEmpleado_cookie = isset($_COOKIE['noEmpleado']) ? $_COOKIE['noEmpleado'] : null;
$id_usuario_cookie = isset($_COOKIE['id_usuario']) ? $_COOKIE['id_usuario'] : null;
$opcion = $_POST["opcion"];
$noEmpleadoInc = isset($_POST["noEmpleadoInc"]) ? $_POST["noEmpleadoInc"] : $noEmpleado_cookie;
$areas = isset($_POST['area']) && is_array($_POST['area']) ? $_POST['area'] : [];        
$ingeniero = isset($_POST['ing']) && is_array($_POST['ing']) ? $_POST['ing'] : [];        
$ciudad = isset($_POST['ciudad']) && is_array($_POST['ciudad']) ? $_POST['ciudad'] : [];
$estatus = isset($_POST['estatus']) && is_array($_POST['estatus']) ? $_POST['estatus'] : [];
$fechaHoy = date('Y-m-d');
$fechaInicio = date('Y-m-d', strtotime($fechaHoy . ' -50 days'));

// Verifica si un empleado tiene un acceso especial activo (tabla accesos_especiales)
function tieneAccesoEspecial($conn, $noEmpleado, $sistema, $opcion) {
    $noEmpleado = intval($noEmpleado);
    if ($noEmpleado <= 0) return false;
    $stmt = $conn->prepare("SELECT COUNT(*) AS cuantos FROM accesos_especiales
                            WHERE noEmpleado = ? AND sistema = ? AND opcion = ? AND estatus = 1");
    if (!$stmt) return false;
    $stmt->bind_param("iss", $noEmpleado, $sistema, $opcion);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row && intval($row['cuantos']) > 0;
}

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

        $sqlInsert = "INSERT INTO servicios_planeados_mess(service_order_id, order_code, engineer, start_date, durationhr, city, area, ds_cliente, estatus, vehiculo, fecha_captura,  capturado_por, travelhr, engineer2, engineer3, comment, reprogramado)
                        VALUES ('$ot', '$ot', '$responsable', '$fechaPlaneada', '$duracion', '$ciudad', '$area', '$cliente', '$estatus', '$automovil', '$fecha', '$noEmpleado', '$duracionViaje', '$responsable2', '$responsable3', '$comentarios', 0)";
        //echo $sqlInsert;
        if ($conn->query($sqlInsert) === TRUE) {
            $idActividad = $conn->insert_id;
            $response = array(
                'status' => 'success',
                'message' => 'Incidencia registrada con éxito.',
                'id_actividad' => $idActividad
            );
        } else {
            $response = array('status' => 'error', 'message' => 'Error al registrar la incidencia: ' . $conn->error);
        }
        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($response);
    }

//FUNCION PARA EL PRE-REGISTRO DE VENTAS (solo departamento 40)
    if($opcion == "preRegistroVentas"){
        header('Content-Type: application/json');
        // Las columnas son utf8mb4; igualamos el charset de la conexión para evitar
        // error de collation al insertar áreas/ciudades con acento (p.ej. "Eléctrica").
        mysqli_set_charset($conn, "utf8mb4");

        // Validación server-side: solo el departamento de Ventas (40) puede pre-registrar
        $departamento_cookie = isset($_COOKIE['departamento']) ? $_COOKIE['departamento'] : null;
        if ($departamento_cookie != '40') {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado: solo el departamento de Ventas puede pre-registrar.']);
            exit;
        }

        $cliente      = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
        $ciudad       = isset($_POST['ciudad']) ? $_POST['ciudad'] : '';
        $area         = isset($_POST['area']) ? $_POST['area'] : '';
        $fecha        = isset($_POST['fecha']) ? $_POST['fecha'] : '';
        $ot           = isset($_POST['ot']) ? trim($_POST['ot']) : '';
        $comentarios  = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';
        // Ingeniero sugerido: la celda que eligió Ventas. El Jefe de Lab puede cambiarlo al aprobar.
        $engineer     = isset($_POST['engineer']) ? intval($_POST['engineer']) : 0;
        $engineer     = $engineer > 0 ? (string)$engineer : '';

        // Validación de requeridos (la OV/OT y el comentario son opcionales)
        if ($cliente === '' || $ciudad === '' || $area === '' || $fecha === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios (cliente, ciudad, área y fecha).']);
            exit;
        }

        // Normalizar la fecha del input datetime-local (YYYY-MM-DDTHH:MM) a datetime de MySQL
        $startDate = str_replace('T', ' ', $fecha);
        if (strlen($startDate) === 16) { $startDate .= ':00'; }

        $estatus      = 'Solicitadoventas'; // Estatus de pre-registro pendiente de aprobación del lab
        $origen       = 'ventas';
        $fechaCaptura = date('Y-m-d');
        $capturadoPor = $noEmpleado_cookie;

        // engineer = ingeniero sugerido por Ventas (puede ir vacío). Lo reasigna el Jefe de Lab al aprobar.
        $sqlInsert = "INSERT INTO servicios_planeados_mess
                        (service_order_id, order_code, engineer, start_date, city, area, ds_cliente, estatus, fecha_captura, capturado_por, comment, reprogramado, origen_captura)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";
        if ($stmt = $conn->prepare($sqlInsert)) {
            $stmt->bind_param("sssssssssiss", $ot, $ot, $engineer, $startDate, $ciudad, $area, $cliente, $estatus, $fechaCaptura, $capturadoPor, $comentarios, $origen);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Pre-registro creado con éxito.', 'id' => $stmt->insert_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error al insertar el pre-registro: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        }
        exit;
    }

//FUNCION PARA EDITAR UN PRE-REGISTRO DE VENTAS (solo el autor, solo si sigue en Solicitadoventas)
    if($opcion == "editarPreRegistroVentas"){
        header('Content-Type: application/json');
        // Igualar charset a utf8mb4 (columnas) para áreas/ciudades con acento.
        mysqli_set_charset($conn, "utf8mb4");

        $departamento_cookie = isset($_COOKIE['departamento']) ? $_COOKIE['departamento'] : null;
        if ($departamento_cookie != '40') {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado: solo el departamento de Ventas puede editar pre-registros.']);
            exit;
        }

        $id           = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $cliente      = isset($_POST['cliente']) ? trim($_POST['cliente']) : '';
        $ciudad       = isset($_POST['ciudad']) ? $_POST['ciudad'] : '';
        $area         = isset($_POST['area']) ? $_POST['area'] : '';
        $fecha        = isset($_POST['fecha']) ? $_POST['fecha'] : '';
        $ot           = isset($_POST['ot']) ? trim($_POST['ot']) : '';
        $comentarios  = isset($_POST['comentarios']) ? trim($_POST['comentarios']) : '';

        if ($id <= 0 || $cliente === '' || $ciudad === '' || $area === '' || $fecha === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        $startDate = str_replace('T', ' ', $fecha);
        if (strlen($startDate) === 16) { $startDate .= ':00'; }

        $sqlUpdate = "UPDATE servicios_planeados_mess
                      SET ds_cliente = ?, city = ?, area = ?, start_date = ?, service_order_id = ?, order_code = ?, comment = ?
                      WHERE id = ? AND estatus = 'Solicitadoventas' AND capturado_por = ? AND origen_captura = 'ventas'";
        if ($stmt = $conn->prepare($sqlUpdate)) {
            $stmt->bind_param("sssssssii", $cliente, $ciudad, $area, $startDate, $ot, $ot, $comentarios, $id, $noEmpleado_cookie);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Pre-registro actualizado con éxito.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar. Verifique que el pre-registro le pertenece y siga en estatus Solicitadoventas.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        }
        exit;
    }

//FUNCION PARA CANCELAR UN PRE-REGISTRO DE VENTAS (CanceladaV)
    if($opcion == "cancelarPreRegistroVentas"){
        header('Content-Type: application/json');

        $departamento_cookie = isset($_COOKIE['departamento']) ? $_COOKIE['departamento'] : null;
        if ($departamento_cookie != '40') {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado.']);
            exit;
        }

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID inválido.']);
            exit;
        }

        $sqlCancel = "UPDATE servicios_planeados_mess
                      SET estatus = 'CanceladaV'
                      WHERE id = ? AND estatus = 'Solicitadoventas' AND capturado_por = ? AND origen_captura = 'ventas'";
        if ($stmt = $conn->prepare($sqlCancel)) {
            $stmt->bind_param("ii", $id, $noEmpleado_cookie);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Pre-registro cancelado.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo cancelar. Verifique que el pre-registro le pertenece y siga en estatus Solicitadoventas.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }

//FUNCION PARA NEGAR (RECHAZAR) UN PRE-REGISTRO DE VENTAS (solo el Jefe rol 3 del lab destino) -> CanceladaLab
    if($opcion == "negarPreRegistroVentas"){
        header('Content-Type: application/json');
        mysqli_set_charset($conn, "utf8mb4");

        $id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';

        // Negar pre-registros requiere el acceso especial 'verPreRegistroVentas'
        if (!tieneAccesoEspecial($conn, $noEmpleado_cookie, 'planeacion', 'verPreRegistroVentas')) {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado: no tienes acceso para negar pre-registros de Ventas.']);
            exit;
        }
        if ($id <= 0 || $motivo === '') {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos (id o motivo del rechazo).']);
            exit;
        }

        // Validar que el pre-registro existe y sigue en Solicitadoventas
        $chk = $conn->query("SELECT estatus FROM servicios_planeados_mess WHERE id = " . intval($id) . " LIMIT 1");
        if (!$chk || !($rowChk = $chk->fetch_assoc())) {
            echo json_encode(['status' => 'error', 'message' => 'Pre-registro no encontrado.']);
            exit;
        }
        if ($rowChk['estatus'] !== 'Solicitadoventas') {
            echo json_encode(['status' => 'error', 'message' => 'Solo se pueden negar pre-registros pendientes (Solicitadoventas).']);
            exit;
        }

        $sqlNegar = "UPDATE servicios_planeados_mess
                     SET estatus = 'CanceladaLab', motivo_cancelacion = ?
                     WHERE id = ? AND estatus = 'Solicitadoventas'";
        if ($stmt = $conn->prepare($sqlNegar)) {
            $stmt->bind_param("si", $motivo, $id);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Pre-registro rechazado.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo rechazar el pre-registro.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
        }
        exit;
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
        $reprogramado = $_POST["reprogramado"];
        $cometRepro = $_POST["commentRepro"];
        $cometCancel = $_POST["commentCancel"];
        // Duración (servicio) y duración de viaje: el lab las completa al aprobar un pre-registro
        $duracion = isset($_POST["duracion"]) ? $_POST["duracion"] : '';
        $duracionViaje = isset($_POST["duracionViaje"]) ? $_POST["duracionViaje"] : '';

        // Validación: aprobar un pre-registro (Solicitadoventas) requiere el acceso especial
        // 'verPreRegistroVentas' (tabla accesos_especiales). No afecta ediciones normales.
        $chkPre = $conn->query("SELECT estatus FROM servicios_planeados_mess WHERE id = " . intval($idActividad) . " LIMIT 1");
        if ($chkPre && ($rowChk = $chkPre->fetch_assoc())) {
            if ($rowChk['estatus'] === 'Solicitadoventas') {
                if (!tieneAccesoEspecial($conn, $noEmpleado_cookie, 'planeacion', 'verPreRegistroVentas')) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'No autorizado: no tienes acceso para aprobar pre-registros de Ventas.']);
                    exit;
                }
            }
        }

        $sqlUpdate = "UPDATE servicios_planeados_mess
                        SET engineer = '$ingeniero',
                            engineer2 = '$ingeniero2',
                            engineer3 = '$ingeniero3',
                            service_order_id = '$ot',
                            order_code = '$ot',
                            start_date = '$fechaActividad',
                            vehiculo = '$automovil',
                            durationhr = '$duracion',
                            travelhr = '$duracionViaje',
                            estatus = '$estatus',
                            comment = '$comment',
                            reprogramado = $reprogramado,
                            motivo_reprogramacion = '$cometRepro',
                            motivo_cancelacion = '$cometCancel' 
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
    $region = isset($_POST['region']) && is_array($_POST['region']) ? $_POST['region'] : [];
    // Filtro por origen de captura: '' (todos), 'ventas' o 'lab'
    $origen = isset($_POST['origen']) ? $_POST['origen'] : '';

    $fechaHoy = date('Y-m-d');
    $fechaInicio = date('Y-m-d', strtotime($fechaHoy . ' -50 days'));

    // Consulta base
    // LEFT JOIN en el ingeniero para que los pre-registros de Ventas (sin ingeniero asignado) también aparezcan
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, IFNULL(u.nombre,'') AS nombre, IFNULL(u2.nombre,'') AS nombre2, IFNULL(u3.nombre,'') AS nombre3,
                    IF(ot.capturado_por = ?, 'SI', 'NO') AS capturo, comment_logistic, estatus_logistic,
                    (SELECT departamento FROM usuarios WHERE noEmpleado = ot.capturado_por) as depto,
                    reprogramado, motivo_reprogramacion, motivo_cancelacion, fecha_captura
            FROM servicios_planeados_mess ot
            LEFT join usuarios u on ot.engineer = u.id_usuario
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario
            -- Filtramos por el estatus Abierto (asumo Estatus 1 o 2) y la fecha.
            WHERE ot.start_date >= ?"; 
    
    // --- 1. Inicialización de arrays para cláusulas WHERE y parámetros ---
    $whereClauses = [];
    $params = [$noEmpleado_cookie, $fechaInicio]; // $noEmpleado_cookie (para IF) y $fechaInicio (para WHERE) son los 2 primeros
    $param_types = "ss";                       // El IF y la fecha son strings
    
    // --- 2. Manejo de múltiples áreas seleccionadas
    if (!empty($areas)) {
        $placeholders = implode(',', array_fill(0, count($areas), '?'));
        $whereClauses[] = "ot.area IN ($placeholders)"; 

        foreach ($areas as $area_item) {
            $params[] = $area_item;
            $param_types .= "s";
        }
    } 

    // --- 3. Manejo de la ciudad
    if (!empty($ciudad)) {
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

        // Añadir la lista de ingenieros al array de parámetros TRES VECES 
        for ($i = 0; $i < 3; $i++) {
            foreach ($ingeniero as $ingeniero_item) {
                $params[] = $ingeniero_item;
                $param_types .= "s"; 
            }
        }
    }

    // --- 5. Manejo del estatus (No es necesario si ya se filtra en el WHERE base)
    // Si el filtro inicial no es suficiente, se añade aquí:
    if (!empty($estatus)) {
        $placeholders = implode(',', array_fill(0, count($estatus), '?'));
        $whereClauses[] = "ot.estatus IN ($placeholders)";

        foreach ($estatus as $estatus_item) {
            $params[] = $estatus_item;
            $param_types .= "s";
        }
    }

    // --- 6. Manejo de la región
    if (!empty($region)) {
        $count = count($region);
        $placeholders = implode(',', array_fill(0, $count, '?'));
        $whereClauses[] = "(u.region IN ($placeholders) OR u2.region IN ($placeholders) OR u3.region IN ($placeholders))";

        // Añadir la lista de regiones al array de parámetros TRES VECES (NECESARIO)
        for ($i = 0; $i < 3; $i++) {
            foreach ($region as $region_item) {
                $params[] = $region_item;
                $param_types .= "s";
            }
        }
    }

    // --- 6b. Manejo del origen de captura (ventas / lab)
    if ($origen === 'ventas' || $origen === 'lab') {
        $whereClauses[] = "ot.origen_captura = ?";
        $params[] = $origen;
        $param_types .= "s";
    }

    // --- 7. Construcción Final
    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(' AND ', $whereClauses);
    }
    $sql .= " GROUP BY ot.id
            ORDER BY ot.id DESC";
    
    // El 'echo $sql;' es útil para debug, pero debe ser eliminado en producción
    //echo $sql; 
    
    // --- 8. Ejecución de la Consulta Preparada ---
    if ($stmt = $conn->prepare($sql)) {
        
        // Enlazar los parámetros dinámicamente
        // Se usa call_user_func_array para bind_param ya que se pasa un array variable.
        // PHP 5.6+ permite usar el operador '...' para desempaquetar el array.
        $stmt->bind_param($param_types, ...$params); 
        
        $stmt->execute();
        $result = $stmt->get_result();

        // ... El resto del manejo de resultados está bien ...
        if ($result && $result->num_rows > 0) {
            $actividades = [];
            while ($row = $result->fetch_assoc()) {
                $actividades[] = $row;
            }
            // Asegurar que la respuesta JSON es lo único que se imprime
            header('Content-Type: application/json');
            echo json_encode($actividades);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron actividades planeadas.']);
        }
        $stmt->close();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta.']);
    }
}

//FUNCION PARA MOSTRAR LAS SOLICITUDES PENDIENTES
if ($opcion == "solicitudesPendientes") {
    // Consulta base
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, u.nombre, IFNULL(u2.nombre,'') AS nombre2, IFNULL(u3.nombre,'') AS nombre3, 
                    IF(ot.capturado_por = $noEmpleado_cookie, 'SI', 'NO') AS capturo, comment_logistic, estatus_logistic
            FROM servicios_planeados_mess ot
            inner join usuarios u on ot.engineer = u.id_usuario 
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario 
            WHERE ot.estatus IN ('Fechareservadasininformación', 'Pendientedeinformacion') AND
                ot.start_date
            BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 DAY)
            UNION
            SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, u.nombre, IFNULL(u2.nombre,'') AS nombre2, IFNULL(u3.nombre,'') AS nombre3, 
                    IF(ot.capturado_por = $noEmpleado_cookie, 'SI', 'NO') AS capturo, comment_logistic, estatus_logistic
            FROM servicios_planeados_mess ot
            inner join usuarios u on ot.engineer = u.id_usuario 
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario 
            WHERE ot.estatus IN ('Fechareservadasininformación', 'Pendientedeinformacion') AND
                ot.start_date
            BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 DAY)";

        // --- 1. Inicialización de arrays para cláusulas WHERE y parámetros ---
        $whereClauses = [];
        
        // --- 2. Construcción Final
        if (!empty($whereClauses)) {
            $sql .= " AND " . implode(' AND ', $whereClauses);
        }

        // --- 3. Ejecución de la Consulta Preparada ---
        if ($stmt = $conn->prepare($sql)) {
            // Enlazar los parámetros dinámicamente        
          
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $actividades = [];
                while ($row = $result->fetch_assoc()) {
                    $actividades[] = $row;
                }
                echo json_encode($actividades);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se encontraron actividades planeadas o error en la consulta.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        }
}

//FUNCION PARA MOSTRAR LAS SOLICITUDES ABIERTAS
if ($opcion == "SolicitudesLogistica") {
    $areas = isset($_POST['area']) && is_array($_POST['area']) ? $_POST['area'] : [];        
    $ingeniero = isset($_POST['ing']) && is_array($_POST['ing']) ? $_POST['ing'] : [];        
    $ciudad = isset($_POST['ciudad']) && is_array($_POST['ciudad']) ? $_POST['ciudad'] : [];
    $estatus = isset($_POST['estatus']) && is_array($_POST['estatus']) ? $_POST['estatus'] : [];
    $fechaHoy = date('Y-m-d');
    $fechaInicio = date('Y-m-d', strtotime($fechaHoy . ' -50 days'));
    // Consulta base
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, u.nombre, IFNULL(u2.nombre,'') AS nombre2, IFNULL(u3.nombre,'') AS nombre3, 
                    IF(ot.capturado_por = $noEmpleado_cookie, 'SI', 'NO') AS capturo, comment_logistic, estatus_logistic, reprogramado
            FROM servicios_planeados_mess ot
            inner join usuarios u on ot.engineer = u.id_usuario 
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario 
            WHERE ot.estatus_logistic IN ('Solicitado', 'aceptada', 'rechazada') AND ot.start_date >= ?"; // 1=Programada, 2=En Proceso, 3=Completada, 4=Cancelada    

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
                echo json_encode(['status' => 'error', 'message' => 'No se encontraron actividades planeadas o error en la consulta.']);
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

if ($opcion == "consultarRegiones") {
    // Consulta para obtener las regiones
    $sql = "SELECT DISTINCT id, region FROM region WHERE id IN (select region from usuarios WHERE id_usuario in (SELECT engineer from servicios_planeados_mess))";
    $result = $conn->query($sql);
    $regiones = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $regiones[] = $row;
        }
    }
    echo json_encode($regiones);
    $conn->close();
}
?>  