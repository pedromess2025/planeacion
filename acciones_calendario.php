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
    
    $sql = "SELECT s.empleado, s.fesolicitud, s.feinicio, s.fefin, CONCAT(u.nombres, ' ', u.apellidos) AS nombre
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
    $sqlJefes = "SELECT s.empleado, s.fesolicitud, s.feinicio, DATE_ADD(s.fefin, INTERVAL 1 DAY) as fefin, CONCAT(u.nombres, ' ', u.apellidos) AS nombre, u.jefe
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
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, IFNULL(CONCAT(u.nombres, ' ', u.apellidos),'') AS nombre, IFNULL(CONCAT(u2.nombres, ' ', u2.apellidos),'') AS nombre2,
                    IFNULL(CONCAT(u3.nombres, ' ', u3.apellidos),'') AS nombre3, IFNULL(ot.comment,'Sin comentarios') AS comment,
                    estatus_logistic, comment_logistic, IFNULL(ot.travelhr, '0') AS travelhr, IFNULL(ot.durationhr, '0') AS durationhr,
                    ot.origen_captura AS origen, ot.capturado_por
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
// Endpoint: disponibilidad de ingenieros por departamento (cuadrícula Ventas)
if ($accion == 'disponibilidadVentas') {
    try {
        $departamentoId = isset($_POST['departamento']) ? intval($_POST['departamento']) : 0;
        $fechaInicio    = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : date('Y-m-d');
        $fechaFin       = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : date('Y-m-d', strtotime($fechaInicio . ' +6 days'));

        if ($departamentoId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Departamento inválido.']);
            exit;
        }

        // 1. Obtener ingenieros activos del departamento
        $sqlIngs = "SELECT id_usuario, CONCAT(nombres, ' ', apellidos) AS nombre FROM usuarios WHERE departamento = ? AND estatus = 1 ORDER BY nombres, apellidos";
        $stmtIngs = $conn->prepare($sqlIngs);
        $stmtIngs->bind_param("i", $departamentoId);
        $stmtIngs->execute();
        $resultIngs = $stmtIngs->get_result();
        $ingenieros = [];
        $idsIngs = [];
        while ($row = $resultIngs->fetch_assoc()) {
            $ingenieros[] = $row;
            $idsIngs[] = $row['id_usuario'];
        }
        $stmtIngs->close();

        if (empty($idsIngs)) {
            echo json_encode(['status' => 'success', 'ingenieros' => [], 'servicios' => []]);
            exit;
        }

        // 2. Obtener servicios planeados de esos ingenieros en el rango de fechas
        $placeholders = implode(',', array_fill(0, count($idsIngs), '?'));
        $sqlServ = "SELECT sp.id, sp.engineer, sp.engineer2, sp.engineer3, sp.order_code, sp.ds_cliente, sp.city, sp.area,
                           sp.estatus, sp.durationhr, sp.travelhr, sp.comment, sp.origen_captura, sp.capturado_por,
                           DATE(sp.start_date) as fecha
                    FROM servicios_planeados_mess sp
                    WHERE (sp.engineer IN ($placeholders) OR sp.engineer2 IN ($placeholders) OR sp.engineer3 IN ($placeholders))
                      AND DATE(sp.start_date) BETWEEN ? AND ?
                      AND sp.estatus NOT IN ('Cancelada','CanceladaV','CanceladaLab','Cerrada')
                    ORDER BY sp.start_date";

        $stmtServ = $conn->prepare($sqlServ);
        $types = str_repeat('i', count($idsIngs) * 3) . 'ss';
        $params = array_merge($idsIngs, $idsIngs, $idsIngs, [$fechaInicio, $fechaFin]);
        $stmtServ->bind_param($types, ...$params);
        $stmtServ->execute();
        $resultServ = $stmtServ->get_result();
        $servicios = [];
        while ($row = $resultServ->fetch_assoc()) {
            $servicios[] = $row;
        }
        $stmtServ->close();

        echo json_encode(['status' => 'success', 'ingenieros' => $ingenieros, 'servicios' => $servicios]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Endpoint: lista de departamentos que son áreas/labs
if ($accion == 'departamentosLab') {
    $ids = [5,6,9,13,14,15,16,17,18,19,20,21,22,23,25,26,27,28,31,36];
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
