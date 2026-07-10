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
// Cerrar la conexión
$conn->close();
?>
