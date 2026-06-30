<?php
header('Content-Type: application/json');
// Conexi贸n a la base de datos
try {
    include 'conn.php';
    mysqli_set_charset($conn, "utf8");
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}
$noEmpleado_cookie = isset($_COOKIE['noEmpleado']) ? $_COOKIE['noEmpleado'] : null;
$opcion = isset($_GET["opcion"]) ? $_GET["opcion"] : '';
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// Consulta de las solicitudes de vacaciones aprobadas
if ($opcion == "rrhh") {
    
    $sql = "SELECT s.empleado, s.fesolicitud, s.feinicio, s.fefin, COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombres, u.apellidos)), ''), u.nombre) AS nombre
            FROM solicitudes s
            INNER JOIN usuarios u ON s.empleado = u.noEmpleado
            WHERE s.estatus = 2 AND s.autorizaRH = 2 AND u.estatus = 1"; // Filtrar solo las aprobadas
    
    $result = $conn->query($sql);
    
    $events = array();
    
    while ($row = $result->fetch_assoc()) {
        $events[] = array(
            'title' => $row['nombre'], // Mostrar el nombre del empleado
            'start' => $row['feinicio'],
            'end' => $row['fefin'],
            'nombre' => $row['nombre']
        );
    }
    
    // Devolver los eventos en formato JSON
    header('Content-Type: application/json');
    echo json_encode($events);
}

if ($opcion == "jefes") {
    if($noEmpleado_cookie == 177 || $noEmpleado_cookie == 489){
        $noEmpleado_cookie = 45;
    }
    $sqlJefes = "SELECT s.empleado, s.fesolicitud, s.feinicio, DATE_ADD(s.fefin, INTERVAL 1 DAY) as fefin, COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombres, u.apellidos)), ''), u.nombre) AS nombre, u.jefe
                FROM solicitudes s
                INNER JOIN usuarios u ON s.empleado = u.noEmpleado
                WHERE s.estatus = 2 AND s.autorizaRH = 2 AND u.jefe = $noEmpleado_cookie AND u.estatus = 1";
    
    $resultJefes = $conn->query($sqlJefes);
    
    $events = array();
    
    while ($row = $resultJefes->fetch_assoc()) {
        $events[] = array(
            'title' => $row['nombre'], // Mostrar el nombre del empleado
            'start' => $row['feinicio'],
            'end' => $row['fefin'],
            'nombre' => $row['nombre']
        );
    }
    
    // Devolver los eventos en formato JSON
    header('Content-Type: application/json');
    echo json_encode($events);
}

if ($accion == 'ActividadesCalendarioPlaneadasSCOT') {

    // Si 'area' es un multiselect, se recibirá como un array
    // Se usa un array vacío por defecto si no se selecciona nada
    $areas = isset($_POST['area']) && is_array($_POST['area']) ? $_POST['area'] : [];
    $ingeniero = isset($_POST['ing']) ? $_POST['ing'] : '';

    // Consultar las actividades planeadas del usuario actual
    $fechaHoy = date('Y-m-d');
    $fechaInicio = date('Y-m-d', strtotime($fechaHoy . ' -50 days'));

    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate
            FROM servicios_planeados ot
            WHERE DATE(ot.start_date) >= ? AND ot.tipo_ot = 'SiteServiceOrder'";

    $whereClauses = [];
    $params = [$fechaInicio]; // Array para los parámetros de la consulta preparada
    $param_types = "s";       // String para los tipos de los parámetros (s = string)

    // Manejo de múltiples áreas seleccionadas
    if (!empty($areas)) {
        // Construye un array de placeholders para la cláusula IN (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($areas), '?'));
        $whereClauses[] = "REPLACE(SUBSTRING_INDEX(ot.order_code, '-', 1), '25', '') IN ($placeholders)";

        // Añade cada área al array de parámetros
        foreach ($areas as $area_item) {
            $params[] = $area_item;
            $param_types .= "s"; // Todas las áreas son strings
        }
    }

    // Manejo del ingeniero
    if (!empty($ingeniero)) {
        $whereClauses[] = "ot.engineer LIKE ?";
        $params[] = "%" . $ingeniero . "%"; // Añade comodines para LIKE
        $param_types .= "s"; // El ingeniero es un string
    }

    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(' AND ', $whereClauses);
    }

    // Preparar la consulta
    if ($stmt = $conn->prepare($sql)) {
        // Enlazar los parámetros dinámicamente
        // La sintaxis '...' es para desempaquetar el array $params en argumentos individuales
        $stmt->bind_param($param_types, ...$params);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $actividades = [];
            while ($row = $result->fetch_assoc()) {
                $actividades[] = $row;
            }
            echo json_encode(['status' => 'success', 'actividades' => $actividades]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron actividades planeadas o error en la consulta.']);
        }

        $stmt->close(); // Cerrar el statement
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al preparar la consulta: ' . $conn->error]);
    }
}

if ($accion == 'ActividadesCalendarioPlaneadas') {

    try {
    $areas = isset($_POST['area']) && is_array($_POST['area']) ? $_POST['area'] : [];

    $ingeniero = isset($_POST['ing']) && is_array($_POST['ing']) ? $_POST['ing'] : [];

    $ciudad = isset($_POST['ciudad']) && is_array($_POST['ciudad']) ? $_POST['ciudad'] : [];

    $estatus = isset($_POST['estatus']) && is_array($_POST['estatus']) ? $_POST['estatus'] : [];

    $region = isset($_POST['region']) && is_array($_POST['region']) ? $_POST['region'] : [];


    // Consultar las actividades planeadas del usuario actual
    $fechaHoy = date('Y-m-d');
    $fechaInicio = date('Y-m-d', strtotime($fechaHoy . ' -50 days'));
    // --- 1. Consulta Base ---
    // LEFT JOIN en el ingeniero para que los pre-registros de Ventas (sin ingeniero asignado) también se muestren
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, IFNULL(COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombres, u.apellidos)), ''), u.nombre),'') AS nombre, IFNULL(COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u2.nombres, u2.apellidos)), ''), u2.nombre),'') AS nombre2,
                    IFNULL(COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u3.nombres, u3.apellidos)), ''), u3.nombre),'') AS nombre3, IFNULL(ot.comment,'Sin comentarios') AS comment,
                    estatus_logistic, comment_logistic, IFNULL(ot.travelhr, '0') AS travelhr, IFNULL(ot.durationhr, '0') AS durationhr,
                    ot.capturado_por
            FROM servicios_planeados_mess ot
            LEFT JOIN usuarios u ON ot.engineer = u.id_usuario
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario
            WHERE ot.estatus NOT IN ('Cancelada', 'Cerrada') AND DATE(ot.start_date) >= ?";

    $whereClauses = [];
    $params = [$fechaInicio];
    $param_types = "s";

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

        for ($i = 0; $i < 3; $i++) {
            foreach ($ingeniero as $ingeniero_item) {
                $params[] = $ingeniero_item;
                $param_types .= "s";
            }
        }
    }

    // --- 5. Manejo del estatus
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

        for ($i = 0; $i < 3; $i++) {
            foreach ($region as $region_item) {
                $params[] = $region_item;
                $param_types .= "s";
            }
        }
    }

    // --- 7. Construcción Final
    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(' AND ', $whereClauses);
    }

    $sql .= " ORDER BY ot.id DESC";

    // --- 8. Ejecución de la Consulta Preparada ---
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $actividades = [];
        while ($row = $result->fetch_assoc()) {
            $actividades[] = $row;
        }
        echo json_encode(['status' => 'success', 'actividades' => $actividades]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se encontraron actividades planeadas.']);
    }

    $stmt->close();

    } catch (Exception $e) {
        http_response_code(200);
        echo json_encode(['status' => 'error', 'message' => 'Error en la consulta: ' . $e->getMessage()]);
    }
}
// Endpoint: disponibilidad de ingenieros (cuadrícula de consulta)
// Resuelve por ingeniero/día un estatus derivado de 3 fuentes + default 'disponible'.
// Prioridad: vacaciones (3) > capacitacion/enlaboratorio (2) > servicio (1) > disponible (0).
if ($accion == 'disponibilidadIngenieros') {
    try {
        $fechaInicio    = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : date('Y-m-d');
        $fechaFin       = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : date('Y-m-d', strtotime($fechaInicio . ' +6 days'));
        $departamentoId = isset($_POST['departamento']) ? intval($_POST['departamento']) : 0;
        $ingenieroF     = isset($_POST['ingeniero']) && is_array($_POST['ingeniero']) ? $_POST['ingeniero'] : [];
        $regionF        = isset($_POST['region']) && is_array($_POST['region']) ? $_POST['region'] : [];

        // Marca una celda solo si la nueva prioridad es mayor o igual a la existente.
        $setCelda = function (&$celdas, $idu, $fecha, $estatus, $detalle, $p) {
            if (!isset($celdas[$idu])) $celdas[$idu] = [];
            if (!isset($celdas[$idu][$fecha]) || $celdas[$idu][$fecha]['p'] < $p) {
                $celdas[$idu][$fecha] = ['estatus' => $estatus, 'detalle' => $detalle, 'p' => $p];
            }
        };

        // 1. Ingenieros activos (puestos 34/38) con filtros opcionales (área/lab, ingeniero, región)
        $sqlIngs = "SELECT id_usuario, noEmpleado, region,
                           COALESCE(NULLIF(TRIM(CONCAT_WS(' ', nombres, apellidos)), ''), nombre) AS nombre
                    FROM usuarios
                    WHERE estatus = 1 AND puesto IN (34,38)";
        $params = []; $types = '';
        if ($departamentoId > 0) { $sqlIngs .= " AND departamento = ?"; $params[] = $departamentoId; $types .= 'i'; }
        if (!empty($ingenieroF)) {
            $ph = implode(',', array_fill(0, count($ingenieroF), '?'));
            $sqlIngs .= " AND id_usuario IN ($ph)";
            foreach ($ingenieroF as $v) { $params[] = intval($v); $types .= 'i'; }
        }
        if (!empty($regionF)) {
            $ph = implode(',', array_fill(0, count($regionF), '?'));
            $sqlIngs .= " AND region IN ($ph)";
            foreach ($regionF as $v) { $params[] = intval($v); $types .= 'i'; }
        }
        $sqlIngs .= " ORDER BY nombre";
        $stmt = $conn->prepare($sqlIngs);
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $ingenieros = [];
        $idsIngs = [];
        $noEmpToId = [];
        while ($row = $res->fetch_assoc()) {
            $ingenieros[] = ['id_usuario' => $row['id_usuario'], 'nombre' => $row['nombre']];
            $idsIngs[] = $row['id_usuario'];
            if ($row['noEmpleado'] !== null && $row['noEmpleado'] !== '') {
                $noEmpToId[$row['noEmpleado']] = $row['id_usuario'];
            }
        }
        $stmt->close();

        if (empty($idsIngs)) {
            echo json_encode(['status' => 'success', 'ingenieros' => [], 'celdas' => (object)[]]);
            exit;
        }

        $celdas = [];
        $ph = implode(',', array_fill(0, count($idsIngs), '?'));

        // 2. Servicios planeados -> 'servicio' (prioridad 1)
        $sqlServ = "SELECT engineer, engineer2, engineer3, ds_cliente, city, area, DATE(start_date) AS fecha
                    FROM servicios_planeados_mess
                    WHERE (engineer IN ($ph) OR engineer2 IN ($ph) OR engineer3 IN ($ph))
                      AND DATE(start_date) BETWEEN ? AND ?
                      AND estatus NOT IN ('Cancelada','CanceladaV','CanceladaLab','Cerrada','Solicitadoventas')";
        $stmt = $conn->prepare($sqlServ);
        $typesS = str_repeat('i', count($idsIngs) * 3) . 'ss';
        $paramsS = array_merge($idsIngs, $idsIngs, $idsIngs, [$fechaInicio, $fechaFin]);
        $stmt->bind_param($typesS, ...$paramsS);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $detalle = trim(($row['ds_cliente'] ?: 'S/C') . ($row['city'] ? ' · ' . $row['city'] : ''));
            foreach (['engineer', 'engineer2', 'engineer3'] as $c) {
                $idu = $row[$c];
                if ($idu && in_array($idu, $idsIngs)) {
                    $setCelda($celdas, $idu, $row['fecha'], 'servicio', $detalle, 1);
                }
            }
        }
        $stmt->close();

        // 3. Lab / capacitación (tabla nueva) -> prioridad 2
        $sqlMan = "SELECT id_usuario, fecha, estatus, area, comentario
                   FROM planeacion_disponibilidad_ingenieros
                   WHERE id_usuario IN ($ph) AND fecha BETWEEN ? AND ?";
        $stmt = $conn->prepare($sqlMan);
        $typesM = str_repeat('i', count($idsIngs)) . 'ss';
        $paramsM = array_merge($idsIngs, [$fechaInicio, $fechaFin]);
        $stmt->bind_param($typesM, ...$paramsM);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $est = (stripos($row['estatus'], 'capac') !== false) ? 'capacitacion' : 'enlaboratorio';
            $detalle = trim(($row['area'] ?: '') . ($row['comentario'] ? ' · ' . $row['comentario'] : ''));
            $setCelda($celdas, $row['id_usuario'], $row['fecha'], $est, $detalle, 2);
        }
        $stmt->close();

        // 4. Vacaciones / ausencias autorizadas por el jefe (solicitudes.estatus = 2) -> prioridad 3
        $noEmps = array_keys($noEmpToId);
        if (!empty($noEmps)) {
            $phN = implode(',', array_fill(0, count($noEmps), '?'));
            // Solape con el rango: feinicio <= fechaFin AND fefin >= fechaInicio
            $sqlVac = "SELECT empleado, feinicio, fefin
                       FROM solicitudes
                       WHERE empleado IN ($phN) AND estatus = 2
                         AND feinicio <= ? AND fefin >= ?";
            $stmt = $conn->prepare($sqlVac);
            $typesV = str_repeat('i', count($noEmps)) . 'ss';
            $paramsV = array_merge(array_map('intval', $noEmps), [$fechaFin, $fechaInicio]);
            $stmt->bind_param($typesV, ...$paramsV);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $idu = isset($noEmpToId[$row['empleado']]) ? $noEmpToId[$row['empleado']] : null;
                if (!$idu) continue;
                $f   = ($row['feinicio'] > $fechaInicio) ? $row['feinicio'] : $fechaInicio;
                $end = ($row['fefin'] < $fechaFin) ? $row['fefin'] : $fechaFin;
                while ($f <= $end) {
                    $setCelda($celdas, $idu, $f, 'vacaciones', 'Ausencia autorizada', 3);
                    $f = date('Y-m-d', strtotime($f . ' +1 day'));
                }
            }
            $stmt->close();
        }

        echo json_encode(['status' => 'success', 'ingenieros' => $ingenieros, 'celdas' => empty($celdas) ? (object)[] : $celdas]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Endpoint: lista de departamentos que son áreas/labs
if ($accion == 'departamentosLab') {
    $ids = [4,14,15,17,18,19,20,22,25,32,38,48,50];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id, departamento FROM departamento WHERE id IN ($placeholders) ORDER BY departamento";
    $stmt = $conn->prepare($sql);
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $deptos = [];
    while ($row = $result->fetch_assoc()) {
        $deptos[] = $row;
    }
    $stmt->close();
    echo json_encode(['status' => 'success', 'departamentos' => $deptos]);
}

// Cerrar la conexión
$conn->close();
?>
