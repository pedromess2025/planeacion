<?php
header('Content-Type: application/json');
// Endpoints de los módulos de Disponibilidad (Ingenieros y Vehículos).
// Vista de consulta (read-only): cuadrículas semana × recurso con estatus derivados.
// Conexión principal: mess_rrhh (para ingenieros/departamentos). Los endpoints de vehículos
// abren su propia conexión a mess_control_vehicular ($connCV).
try {
    include 'conn.php';
    mysqli_set_charset($conn, "utf8");
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit;
}
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// Vehículos COMODÍN (carga: estaquitas/vans). No hay distintivo en la BD -> lista por id_vehiculo.
// ⚠ EDITAR AQUÍ si cambia la flota de carga. Placas: PH5707A,SX2202B,SV9452D,SU2914C,SV6353B,SV6343B,SX2244B,SV9819E
$COMODINES_VEH = [13, 68, 8, 55, 58, 59, 80, 97];

// Conexión a la BD de vehículos (mess_control_vehicular). Devuelve mysqli o corta con JSON de error.
function conexionVehiculos() {
    $c = new mysqli("localhost", "mess_incidencias", "Pipmytrade123", "mess_control_vehicular");
    if ($c->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Error de conexión (vehículos): ' . $c->connect_error]);
        exit;
    }
    mysqli_set_charset($c, "utf8mb4");
    return $c;
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

// Endpoint: disponibilidad de vehículos (cuadrícula de consulta, read-only)
// Filas = vehículos activos asignados a ingenieros (mess_rrhh.usuarios.tipo_usr IN ('ING','JEFE')).
// Estatus por celda día×vehículo, prioridad: mantenimiento (2) > préstamo (1) > disponible (0).
if ($accion == 'disponibilidadVehiculos') {
    try {
        $fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : date('Y-m-d');
        $fechaFin    = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : date('Y-m-d', strtotime($fechaInicio . ' +6 days'));
        $areaF       = isset($_POST['area']) ? trim($_POST['area']) : '';
        $vehiculoF   = isset($_POST['vehiculo']) && is_array($_POST['vehiculo']) ? $_POST['vehiculo'] : [];
        $deptoF      = isset($_POST['departamento']) && is_array($_POST['departamento']) ? $_POST['departamento'] : [];
        $ingenieroF  = isset($_POST['ingeniero']) && is_array($_POST['ingeniero']) ? $_POST['ingeniero'] : [];

        // Conexión dedicada a la BD de vehículos (para no pisar $conn = mess_rrhh)
        $connCV = conexionVehiculos();

        // Marca una celda solo si la nueva prioridad es mayor o igual a la existente.
        // Prioridad: mantenimiento (3) > servicio (2) > préstamo (1) > disponible (0).
        $setCelda = function (&$celdas, $idv, $fecha, $estatus, $detalle, $titulo, $p) {
            if (!isset($celdas[$idv])) $celdas[$idv] = [];
            if (!isset($celdas[$idv][$fecha]) || $celdas[$idv][$fecha]['p'] < $p) {
                $celdas[$idv][$fecha] = ['estatus' => $estatus, 'detalle' => $detalle, 'titulo' => $titulo, 'p' => $p];
            }
        };

        // Comodines: lista global $COMODINES_VEH (definida arriba). Se incluyen siempre y van primero.
        $comodinList = implode(',', array_map('intval', $COMODINES_VEH));
        if ($comodinList === '') $comodinList = '0';

        // 1. Vehículos del grid = flota de ingenieros (ING/JEFE) + comodines (siempre). LEFT JOIN por si un
        //    comodín no tiene usuario. Filtros opcionales. Los comodines se marcan y se ordenan primero.
        // DISTINCT: un id_usuario puede casar con >1 fila en usuarios (duplicados) y multiplicar el vehículo;
        // como solo seleccionamos columnas de inventario, DISTINCT colapsa esos duplicados sin riesgo.
        $sqlVeh = "SELECT DISTINCT inv.id_vehiculo, inv.placa, inv.marca, inv.modelo, inv.usuario, inv.area,
                          (inv.id_vehiculo IN ($comodinList)) AS comodin
                   FROM inventario inv
                   LEFT JOIN mess_rrhh.usuarios u ON inv.id_usuario = u.id_usuario
                   WHERE inv.estatus = 'Activo'
                     AND (u.tipo_usr IN ('ING','JEFE') OR inv.id_vehiculo IN ($comodinList))";
        $params = []; $types = '';
        if ($areaF !== '') { $sqlVeh .= " AND inv.area = ?"; $params[] = $areaF; $types .= 's'; }
        if (!empty($deptoF)) {
            $phD = implode(',', array_fill(0, count($deptoF), '?'));
            $sqlVeh .= " AND u.departamento IN ($phD)";
            foreach ($deptoF as $v) { $params[] = intval($v); $types .= 'i'; }
        }
        if (!empty($ingenieroF)) {
            $phI = implode(',', array_fill(0, count($ingenieroF), '?'));
            $sqlVeh .= " AND inv.id_usuario IN ($phI)";
            foreach ($ingenieroF as $v) { $params[] = intval($v); $types .= 'i'; }
        }
        if (!empty($vehiculoF)) {
            $ph = implode(',', array_fill(0, count($vehiculoF), '?'));
            $sqlVeh .= " AND inv.id_vehiculo IN ($ph)";
            foreach ($vehiculoF as $v) { $params[] = intval($v); $types .= 'i'; }
        }
        $sqlVeh .= " ORDER BY (inv.id_vehiculo IN ($comodinList)) DESC, inv.usuario, inv.placa";  // comodines primero, luego por ingeniero
        $stmt = $connCV->prepare($sqlVeh);
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $vehiculos = [];
        $idsVeh = [];
        while ($row = $res->fetch_assoc()) {
            $vehiculos[] = $row;
            $idsVeh[] = $row['id_vehiculo'];
        }
        $stmt->close();

        if (empty($idsVeh)) {
            echo json_encode(['status' => 'success', 'vehiculos' => [], 'celdas' => (object)[]]);
            $connCV->close();
            exit;
        }

        $celdas = [];
        $ph = implode(',', array_fill(0, count($idsVeh), '?'));

        // 2. Préstamos AUTORIZADO/EN CURSO -> 'prestamo' (prioridad 1). Solape con el rango de la semana.
        $sqlP = "SELECT id_vehiculo, DATE(fecha_inc_prestamo) AS ini, DATE(fecha_fin_prestamo) AS fin, Destino, motivo_us
                 FROM prestamos
                 WHERE id_vehiculo IN ($ph)
                   AND estatus IN ('AUTORIZADO','EN CURSO')
                   AND DATE(fecha_inc_prestamo) <= ? AND DATE(fecha_fin_prestamo) >= ?";
        $stmt = $connCV->prepare($sqlP);
        $typesP = str_repeat('i', count($idsVeh)) . 'ss';
        $paramsP = array_merge(array_map('intval', $idsVeh), [$fechaFin, $fechaInicio]);
        $stmt->bind_param($typesP, ...$paramsP);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $detalle = trim($row['Destino'] ?? '');
            if ($detalle === '') $detalle = trim($row['motivo_us'] ?? '');
            if ($detalle === '') $detalle = 'Préstamo';
            $f   = ($row['ini'] > $fechaInicio) ? $row['ini'] : $fechaInicio;
            $end = ($row['fin'] < $fechaFin) ? $row['fin'] : $fechaFin;
            while ($f <= $end) {
                $setCelda($celdas, $row['id_vehiculo'], $f, 'prestamo', $detalle, $detalle, 1);
                $f = date('Y-m-d', strtotime($f . ' +1 day'));
            }
        }
        $stmt->close();

        // 3. Mantenimiento autorizado y programado -> 'mantenimiento' (prioridad 3, gana sobre servicio/préstamo)
        $sqlM = "SELECT id_vehiculo, fecha_programada, tipo_mantenimiento
                 FROM mantenimientos
                 WHERE id_vehiculo IN ($ph)
                   AND VoBo_jefe = 'AUTORIZADO'
                   AND fecha_programada BETWEEN ? AND ?";
        $stmt = $connCV->prepare($sqlM);
        $typesM = str_repeat('i', count($idsVeh)) . 'ss';
        $paramsM = array_merge(array_map('intval', $idsVeh), [$fechaInicio, $fechaFin]);
        $stmt->bind_param($typesM, ...$paramsM);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $detalle = trim($row['tipo_mantenimiento'] ?? '');
            if ($detalle === '') $detalle = 'Mantenimiento';
            $setCelda($celdas, $row['id_vehiculo'], $row['fecha_programada'], 'mantenimiento', $detalle, $detalle, 3);
        }
        $stmt->close();

        // 4. Servicios planeados (mess_rrhh.servicios_planeados_mess, ligado por placa) -> 'servicio' (prioridad 2)
        //    Info tipo "Actividades planeadas": ing / área / OT / cliente / ciudad.
        $placaToId = [];
        foreach ($vehiculos as $vv) {
            if ($vv['placa'] !== null && $vv['placa'] !== '') $placaToId[$vv['placa']] = $vv['id_vehiculo'];
        }
        $placas = array_keys($placaToId);
        if (!empty($placas)) {
            $phS = implode(',', array_fill(0, count($placas), '?'));
            $sqlS = "SELECT sp.vehiculo AS placa, sp.order_code, sp.ds_cliente, sp.city, sp.area,
                            DATE(sp.start_date) AS fecha,
                            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', ue.nombres, ue.apellidos)), ''), ue.nombre) AS ing
                     FROM servicios_planeados_mess sp
                     LEFT JOIN usuarios ue ON sp.engineer = ue.id_usuario
                     WHERE sp.vehiculo IN ($phS)
                       AND DATE(sp.start_date) BETWEEN ? AND ?
                       AND sp.estatus NOT IN ('Cancelada','CanceladaV','CanceladaLab','Cerrada','Solicitadoventas')";
            // servicios_planeados_mess y usuarios viven en mess_rrhh ($conn), no en $connCV
            $stmt = $conn->prepare($sqlS);
            $typesS = str_repeat('s', count($placas)) . 'ss';
            $paramsS = array_merge($placas, [$fechaInicio, $fechaFin]);
            $stmt->bind_param($typesS, ...$paramsS);
            $stmt->execute();
            $res = $stmt->get_result();
            $esc = function ($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); };
            while ($row = $res->fetch_assoc()) {
                $idv = isset($placaToId[$row['placa']]) ? $placaToId[$row['placa']] : null;
                if (!$idv) continue;
                $corto = trim(($row['ds_cliente'] ?: 'S/C') . ($row['city'] ? ' · ' . $row['city'] : ''));
                // Tooltip HTML estilo "Actividades planeadas" (ing / área / OT / cliente / ciudad)
                $full = "<i class='fas fa-user'></i> <b>" . $esc($row['ing'] ?: 'S/Ing') . "</b><br>"
                      . "Área: " . $esc($row['area'] ?: '-') . "<br>"
                      . "OT: " . $esc($row['order_code'] ?: 's/OT') . "<br>"
                      . "Cliente: " . $esc($row['ds_cliente'] ?: 'S/C') . "<br>"
                      . "Ciudad: " . $esc($row['city'] ?: '-');
                $setCelda($celdas, $idv, $row['fecha'], 'servicio', $corto, $full, 2);
            }
            $stmt->close();
        }

        $connCV->close();
        echo json_encode(['status' => 'success', 'vehiculos' => $vehiculos, 'celdas' => empty($celdas) ? (object)[] : $celdas]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Endpoint: áreas de vehículos (para el filtro del módulo de disponibilidad de vehículos)
if ($accion == 'areasVehiculos') {
    try {
        $connCV = conexionVehiculos();
        $res = $connCV->query("SELECT DISTINCT area FROM inventario WHERE estatus = 'Activo' AND area <> '' ORDER BY area");
        $areas = [];
        while ($row = $res->fetch_assoc()) { $areas[] = $row['area']; }
        $connCV->close();
        echo json_encode(['status' => 'success', 'areas' => $areas]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Endpoint: laboratorios/departamentos de vehículos (para el filtro del módulo de vehículos).
// Se DERIVA de la población real (departamento de los ingenieros con vehículo), no de una lista
// hardcodeada -> env-independiente y siempre consistente con el grid.
if ($accion == 'laboratoriosVehiculos') {
    try {
        $connCV = conexionVehiculos();
        $sql = "SELECT DISTINCT u.departamento AS id, d.departamento AS nombre
                FROM inventario inv
                JOIN mess_rrhh.usuarios u ON inv.id_usuario = u.id_usuario
                LEFT JOIN mess_rrhh.departamento d ON u.departamento = d.id
                WHERE inv.estatus = 'Activo' AND u.tipo_usr IN ('ING','JEFE')
                  AND u.departamento IS NOT NULL AND u.departamento <> 0
                ORDER BY nombre";
        $res = $connCV->query($sql);
        $labs = [];
        while ($row = $res->fetch_assoc()) {
            $labs[] = ['id' => $row['id'], 'nombre' => $row['nombre'] !== null ? $row['nombre'] : ('Depto ' . $row['id'])];
        }
        $connCV->close();
        echo json_encode(['status' => 'success', 'laboratorios' => $labs]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Endpoint: ingenieros responsables de la flota (para el filtro del módulo de vehículos).
// Se DERIVA de la población real (mismos vehículos del grid: ING/JEFE + comodines).
if ($accion == 'ingenierosVehiculos') {
    try {
        $connCV = conexionVehiculos();
        $comodinList = implode(',', array_map('intval', $COMODINES_VEH));
        if ($comodinList === '') $comodinList = '0';
        $sql = "SELECT DISTINCT u.id_usuario AS id,
                       COALESCE(NULLIF(TRIM(CONCAT_WS(' ', u.nombres, u.apellidos)), ''), u.nombre) AS nombre
                FROM inventario inv
                JOIN mess_rrhh.usuarios u ON inv.id_usuario = u.id_usuario
                WHERE inv.estatus = 'Activo' AND (u.tipo_usr IN ('ING','JEFE') OR inv.id_vehiculo IN ($comodinList))
                ORDER BY nombre";
        $res = $connCV->query($sql);
        $ings = [];
        while ($row = $res->fetch_assoc()) { $ings[] = ['id' => $row['id'], 'nombre' => $row['nombre']]; }
        $connCV->close();
        echo json_encode(['status' => 'success', 'ingenieros' => $ings]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Cerrar la conexión
$conn->close();
?>
