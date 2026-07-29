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
        $zonaF          = isset($_POST['zona']) ? trim($_POST['zona']) : '';
        $deptoF         = isset($_POST['departamento']) ? trim($_POST['departamento']) : ''; // 2º filtro (cascada Lab Hugo)
        $ingenieroF     = isset($_POST['ingeniero']) && is_array($_POST['ingeniero']) ? $_POST['ingeniero'] : [];

        // Marca una celda solo si la nueva prioridad es mayor o igual a la existente.
        // $titulo (opcional) = HTML del popup (tooltip on-hover); solo lo usa 'servicio'.
        $setCelda = function (&$celdas, $idu, $fecha, $estatus, $detalle, $p, $titulo = null) {
            if (!isset($celdas[$idu])) $celdas[$idu] = [];
            if (!isset($celdas[$idu][$fecha]) || $celdas[$idu][$fecha]['p'] < $p) {
                $celdas[$idu][$fecha] = ['estatus' => $estatus, 'detalle' => $detalle, 'titulo' => $titulo, 'p' => $p];
            }
        };

        // 1. Ingenieros / jefes activos, con filtros opcionales (área/zona, ingeniero, región).
        //    Se identifican por `usuarios.tipo_usr` (ING / JEFE_ENCARGADO / JEFE_LAB) — campo indep. del entorno,
        //    a diferencia del catálogo `puesto` (cuyos ids difieren entre LOCAL y PRODUCCIÓN).
        //    El filtro de Área/Laboratorio usa la columna `usuarios.zona` (texto). El 2º filtro opcional
        //    (cascada "Lab Hugo") acota por `usuarios.departamento`.
        //    GROUP BY id_usuario: la PK de `usuarios` es `id`, no `id_usuario`, así que un mismo
        //    id_usuario puede tener filas duplicadas; se colapsan para no repetir renglones en el grid.
        //    Con ONLY_FULL_GROUP_BY (PROD) las columnas no agrupadas se agregan con MAX().
        //    LEFT JOIN departamento -> nombre del lab del ingeniero (se muestra en el grid).
        $sqlIngs = "SELECT u.id_usuario,
                           MAX(u.noEmpleado) AS noEmpleado,
                           COALESCE(NULLIF(TRIM(CONCAT_WS(' ', MAX(u.nombres), MAX(u.apellidos))), ''), MAX(u.nombre)) AS nombre,
                           MAX(d.departamento) AS lab
                    FROM usuarios u
                    LEFT JOIN departamento d ON u.departamento = d.id
                    WHERE u.estatus = 1 AND u.tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')";
        $params = []; $types = '';
        if ($zonaF !== '') { $sqlIngs .= " AND u.zona = ?"; $params[] = $zonaF; $types .= 's'; }
        if ($deptoF !== '') { $sqlIngs .= " AND u.departamento = ?"; $params[] = intval($deptoF); $types .= 'i'; }
        if (!empty($ingenieroF)) {
            $ph = implode(',', array_fill(0, count($ingenieroF), '?'));
            $sqlIngs .= " AND u.id_usuario IN ($ph)";
            foreach ($ingenieroF as $v) { $params[] = intval($v); $types .= 'i'; }
        }
        $sqlIngs .= " GROUP BY u.id_usuario ORDER BY nombre";
        $stmt = $conn->prepare($sqlIngs);
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $ingenieros = [];
        $idsIngs = [];
        $noEmpToId = [];
        while ($row = $res->fetch_assoc()) {
            $ingenieros[] = ['id_usuario' => $row['id_usuario'], 'nombre' => $row['nombre'], 'lab' => $row['lab']];
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

        // 1b. Asignación -> estatus BASE por ingeniero (default de la celda, prioridad 0)
        //     'Servicio en sitio 100' -> disponible ; Laboratorio/Jefatura/Administración -> enlaboratorio
        //     La tabla se liga por `noEmpleado` (más estable que id_usuario, que puede ser NULL/duplicado);
        //     se traduce a id_usuario con $noEmpToId para colgar el dato en cada ingeniero del grid.
        //     Resiliente: si la tabla aún no existe (migración pendiente), degrada a base 'disponible'.
        $baseByIng = [];
        $asigByIng = [];
        if (!empty($noEmpToId)) {
            try {
                $noEmpsA = array_keys($noEmpToId);
                $phA = implode(',', array_fill(0, count($noEmpsA), '?'));
                $sqlAsig = "SELECT noEmpleado, asignacion FROM planeacion_asignacion_ingenieros WHERE noEmpleado IN ($phA)";
                $stmt = $conn->prepare($sqlAsig);
                $stmt->bind_param(str_repeat('i', count($noEmpsA)), ...$noEmpsA);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $idU = isset($noEmpToId[$row['noEmpleado']]) ? $noEmpToId[$row['noEmpleado']] : null;
                    if ($idU === null) continue;
                    $asigByIng[$idU] = $row['asignacion'];
                    $baseByIng[$idU] = (stripos($row['asignacion'], 'Servicio') !== false) ? 'disponible' : 'enlaboratorio';
                }
                $stmt->close();
            } catch (Throwable $e) {
                $baseByIng = [];
                $asigByIng = [];
            }
        }

        // 1c. Enlace personalizado de PowerBI por ingeniero (tabla `usuarios_enlace_planeacion`, ligada por NoEmpleado).
        //     Resiliente: si la tabla no existe, se degrada a "sin enlace".
        //     Un mismo NoEmpleado puede aparecer duplicado -> se prefiere la fila con `pageName` (link más específico).
        $enlaceByIng = [];
        if (!empty($noEmpToId)) {
            try {
                $noEmps = array_keys($noEmpToId);
                $phE = implode(',', array_fill(0, count($noEmps), '?'));
                $sqlEnl = "SELECT NoEmpleado, Enlace FROM usuarios_enlace_planeacion WHERE NoEmpleado IN ($phE)";
                $stmt = $conn->prepare($sqlEnl);
                $stmt->bind_param(str_repeat('i', count($noEmps)), ...$noEmps);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($row = $res->fetch_assoc()) {
                    $idU = $noEmpToId[$row['NoEmpleado']];
                    $url = $row['Enlace'];
                    if (!isset($enlaceByIng[$idU]) ||
                        (stripos($url, 'pageName') !== false && stripos($enlaceByIng[$idU], 'pageName') === false)) {
                        $enlaceByIng[$idU] = $url;
                    }
                }
                $stmt->close();
            } catch (Throwable $e) {
                $enlaceByIng = [];
            }
        }

        // 2. Servicios planeados -> 'servicio' (prioridad 1)
        //    Incluye los CERRADOS (estatus 'Cerrada'): se muestran IGUAL que uno activo (mismo color café);
        //    el estatus real va en el popup. Solo se excluyen cancelados y el marcador de captura-Ventas.
        //    Popup (tooltip HTML on-hover, estilo Disponibilidad de Vehículos): cliente / fecha compromiso /
        //    hora / estatus / ciudad / OT.
        $escServ = function ($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); };
        $sqlServ = "SELECT engineer, engineer2, engineer3, ds_cliente, city, area, order_code, estatus,
                           DATE(start_date) AS fecha, TIME(start_date) AS hora
                    FROM servicios_planeados_mess
                    WHERE (engineer IN ($ph) OR engineer2 IN ($ph) OR engineer3 IN ($ph))
                      AND DATE(start_date) BETWEEN ? AND ?
                      AND estatus NOT IN ('Cancelada','CanceladaV','CanceladaLab','Solicitadoventas')";
        $stmt = $conn->prepare($sqlServ);
        $typesS = str_repeat('i', count($idsIngs) * 3) . 'ss';
        $paramsS = array_merge($idsIngs, $idsIngs, $idsIngs, [$fechaInicio, $fechaFin]);
        $stmt->bind_param($typesS, ...$paramsS);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $detalle = trim(($row['ds_cliente'] ?: 'S/C') . ($row['city'] ? ' · ' . $row['city'] : ''));
            $hora = (!empty($row['hora']) && $row['hora'] !== '00:00:00') ? substr($row['hora'], 0, 5) : '—';
            $titulo = "<i class='fas fa-briefcase'></i> <b>" . $escServ($row['ds_cliente'] ?: 'S/C') . "</b><br>"
                    . "Fecha compromiso: " . $escServ($row['fecha']) . "<br>"
                    . "Hora: " . $escServ($hora) . "<br>"
                    . "Estatus: " . $escServ($row['estatus'] ?: '-') . "<br>"
                    . "Ciudad: " . $escServ($row['city'] ?: '-') . "<br>"
                    . "OT: " . $escServ($row['order_code'] ?: 's/OT');
            foreach (['engineer', 'engineer2', 'engineer3'] as $c) {
                $idu = $row[$c];
                if ($idu && in_array($idu, $idsIngs)) {
                    $setCelda($celdas, $idu, $row['fecha'], 'servicio', $detalle, 1, $titulo);
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
            if (stripos($row['estatus'], 'capac') !== false)        $est = 'capacitacion';
            elseif (stripos($row['estatus'], 'interna') !== false)  $est = 'otinterna';   // OT interna
            else                                                    $est = 'enlaboratorio';
            $detalle = trim(($row['area'] ?: '') . ($row['comentario'] ? ' · ' . $row['comentario'] : ''));
            $setCelda($celdas, $row['id_usuario'], $row['fecha'], $est, $detalle, 2);
        }
        $stmt->close();

        // 3b. Servicios de LABORATORIO planeados en SCOT (servicios_planeados, tipo_ot = 'LaboratoryServiceOrder')
        //     -> 'enlaboratorio' (prioridad 2). Se ligan al ingeniero por `id_usr` = usuarios.id_usuario.
        //     Marca cada día del rango start_date..end_date que caiga dentro de la ventana consultada.
        //     Se evita el literal '0000-00-00 00:00:00' (lo rechaza el sql_mode estricto/NO_ZERO_DATE de PROD);
        //     GREATEST(start_date, IFNULL(end_date, start_date)) da el fin real y degrada a start_date si
        //     end_date es NULL o fecha cero, sin usar literales de fecha inválidos.
        $sqlLabScot = "SELECT id_usr, ds_cliente, region, order_code,
                              DATE(start_date) AS f_ini,
                              DATE(GREATEST(start_date, IFNULL(end_date, start_date))) AS f_fin
                       FROM servicios_planeados
                       WHERE tipo_ot = 'LaboratoryServiceOrder'
                         AND id_usr IN ($ph)
                         AND DATE(start_date) <= ?
                         AND DATE(GREATEST(start_date, IFNULL(end_date, start_date))) >= ?";
        $stmt = $conn->prepare($sqlLabScot);
        $typesL = str_repeat('i', count($idsIngs)) . 'ss';
        $paramsL = array_merge($idsIngs, [$fechaFin, $fechaInicio]);
        $stmt->bind_param($typesL, ...$paramsL);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $detalle = trim($row['ds_cliente'] ?: ($row['order_code'] ?: 'Laboratorio'));
            $ini = ($row['f_ini'] > $fechaInicio) ? $row['f_ini'] : $fechaInicio;
            $fin = ($row['f_fin'] < $fechaFin)   ? $row['f_fin'] : $fechaFin;
            $d = $ini;
            while ($d <= $fin) {
                $setCelda($celdas, $row['id_usr'], $d, 'enlaboratorio', $detalle, 2);
                $d = date('Y-m-d', strtotime($d . ' +1 day'));
            }
        }
        $stmt->close();

        // 3c. OT internas (reporte cargado en `planeacion_ot_interna`) -> 'otinterna' (prioridad 2).
        //     La columna `Engineers` guarda NOMBRE(S) en texto (no id) -> se casa por nombre normalizado
        //     (sin acentos/mayúsculas, tolerando el orden "Apellido Nombre") contra los ingenieros del grid.
        //     Un registro puede listar varios ingenieros (separados por , ; / & o " y "). Pinta cada día del
        //     rango startDate..endDate. Status: TODAS. Resiliente: si la tabla no existe aún, se omite sin romper.
        try {
            $norm = function ($s) {
                $s = mb_strtolower(trim((string)$s), 'UTF-8');
                $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n',
                                'à'=>'a','è'=>'e','ì'=>'i','ò'=>'o','ù'=>'u']);
                $s = preg_replace('/[^a-z0-9 ]+/', ' ', $s);
                return trim(preg_replace('/\s+/', ' ', $s));
            };
            // Mapa nombre-normalizado -> id_usuario (varias variantes para maximizar el match por nombre)
            $nombreToId = [];
            $sqlNom = "SELECT id_usuario, nombres, apellidos, nombre FROM usuarios WHERE id_usuario IN ($ph)";
            $stmt = $conn->prepare($sqlNom);
            $stmt->bind_param(str_repeat('i', count($idsIngs)), ...$idsIngs);
            $stmt->execute();
            $resN = $stmt->get_result();
            while ($r = $resN->fetch_assoc()) {
                foreach ([trim($r['nombres'] . ' ' . $r['apellidos']),
                          trim($r['apellidos'] . ' ' . $r['nombres']),
                          $r['nombre']] as $cand) {
                    $k = $norm($cand);
                    if ($k !== '' && !isset($nombreToId[$k])) $nombreToId[$k] = $r['id_usuario'];
                }
            }
            $stmt->close();

            $sqlOt = "SELECT codigo_ot, estatus, areas_calidad, ingenieros,
                             DATE(fecha_inicio) AS f_ini, DATE(fecha_fin) AS f_fin
                      FROM planeacion_ot_interna
                      WHERE fecha_inicio IS NOT NULL
                        AND DATE(fecha_inicio) <= ?
                        AND DATE(IFNULL(fecha_fin, fecha_inicio)) >= ?";
            $stmt = $conn->prepare($sqlOt);
            $stmt->bind_param('ss', $fechaFin, $fechaInicio);
            $stmt->execute();
            $resO = $stmt->get_result();
            while ($row = $resO->fetch_assoc()) {
                $ini = ($row['f_ini'] > $fechaInicio) ? $row['f_ini'] : $fechaInicio;
                $finReal = !empty($row['f_fin']) ? $row['f_fin'] : $row['f_ini'];
                $fin = ($finReal < $fechaFin) ? $finReal : $fechaFin;
                $detalle = trim(($row['codigo_ot'] ?: 'OT interna') . ($row['areas_calidad'] ? ' · ' . $row['areas_calidad'] : ''));
                $titulo = "<i class='fas fa-clipboard-list'></i> <b>" . $escServ($row['codigo_ot'] ?: 'OT interna') . "</b><br>"
                        . "Áreas: " . $escServ($row['areas_calidad'] ?: '-') . "<br>"
                        . "Estatus: " . $escServ($row['estatus'] ?: '-') . "<br>"
                        . "Del " . $escServ($row['f_ini']) . " al " . $escServ($finReal);
                $partes = preg_split('/[,;\/&]| y /u', (string)$row['ingenieros']);
                $vistos = [];
                foreach ($partes as $p1) {
                    $k = $norm($p1);
                    if ($k === '' || !isset($nombreToId[$k])) continue;
                    $idu = $nombreToId[$k];
                    if (isset($vistos[$idu])) continue;   // no repetir si un nombre aparece 2 veces
                    $vistos[$idu] = true;
                    $d = $ini;
                    while ($d <= $fin) {
                        $setCelda($celdas, $idu, $d, 'otinterna', $detalle, 2, $titulo);
                        $d = date('Y-m-d', strtotime($d . ' +1 day'));
                    }
                }
            }
            $stmt->close();
        } catch (Throwable $e) {
            // tabla ausente o error -> se omiten las OT internas del reporte
        }

        // 4. Vacaciones / ausencias -> prioridad 3
        //    Cuenta desde que el empleado hace la solicitud; solo se EXCLUYE si fue RECHAZADA.
        //    Rechazada = jefe (estatus = 3) O RRHH (autorizaRH = 3) — misma regla que la app `incidencias`
        //    (acciones_solicitudempleado.php: canceladas/rechazadas = estatus=3 OR autorizaRH=3).
        //    Se incluyen pendientes (estatus 1 / autorizaRH 1) y autorizadas (estatus=2 AND autorizaRH=2).
        $noEmps = array_keys($noEmpToId);
        if (!empty($noEmps)) {
            $phN = implode(',', array_fill(0, count($noEmps), '?'));
            // Solape con el rango: feinicio <= fechaFin AND fefin >= fechaInicio
            $sqlVac = "SELECT empleado, feinicio, fefin, estatus, autorizaRH
                       FROM solicitudes
                       WHERE empleado IN ($phN)
                         AND estatus <> 3 AND autorizaRH <> 3
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
                // Autorizada = jefe (2) Y RRHH (2); en otro caso sigue pendiente de autorización.
                $autorizada = ($row['estatus'] == 2 && $row['autorizaRH'] == 2);
                $detalle = $autorizada ? 'Ausencia autorizada' : 'Ausencia solicitada (por autorizar)';
                $f   = ($row['feinicio'] > $fechaInicio) ? $row['feinicio'] : $fechaInicio;
                $end = ($row['fefin'] < $fechaFin) ? $row['fefin'] : $fechaFin;
                while ($f <= $end) {
                    $setCelda($celdas, $idu, $f, 'vacaciones', $detalle, 3);
                    $f = date('Y-m-d', strtotime($f . ' +1 day'));
                }
            }
            $stmt->close();
        }

        // Adjuntar el estatus base, el texto de la Asignación y el enlace personalizado a cada ingeniero
        foreach ($ingenieros as &$ing) {
            $ing['base'] = isset($baseByIng[$ing['id_usuario']]) ? $baseByIng[$ing['id_usuario']] : 'disponible';
            $ing['asignacion'] = isset($asigByIng[$ing['id_usuario']]) ? $asigByIng[$ing['id_usuario']] : '';
            $ing['enlace'] = isset($enlaceByIng[$ing['id_usuario']]) ? $enlaceByIng[$ing['id_usuario']] : '';
        }
        unset($ing);

        echo json_encode(['status' => 'success', 'ingenieros' => $ingenieros, 'celdas' => empty($celdas) ? (object)[] : $celdas]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

// Endpoint: zonas (área / laboratorio) para el filtro del grid.
// Cada valor distinto de `usuarios.zona` entre los ING/JEFE activos es una opción.
// Se DERIVA de la población real del grid -> siempre consistente con lo que se muestra.
if ($accion == 'zonasLab') {
    $sql = "SELECT DISTINCT zona
            FROM usuarios
            WHERE estatus = 1 AND tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')
              AND zona IS NOT NULL AND TRIM(zona) <> ''
            ORDER BY zona";
    $zonas = [];
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) { $zonas[] = $row['zona']; }
    }
    echo json_encode(['status' => 'success', 'zonas' => $zonas]);
}

// Endpoint: departamentos de una zona (2º filtro en cascada; hoy lo usa "Lab Hugo").
// Lista los DISTINCT departamento de los ING/JEFE activos de esa zona -> se DERIVA de la
// población real, así que si el usuario re-etiqueta zonas o departamentos, el filtro se ajusta solo.
if ($accion == 'departamentosZona') {
    $zonaF = isset($_POST['zona']) ? trim($_POST['zona']) : '';
    $labs = [];
    if ($zonaF !== '') {
        $sql = "SELECT DISTINCT u.departamento AS id, d.departamento AS nombre
                FROM usuarios u
                LEFT JOIN departamento d ON u.departamento = d.id
                WHERE u.estatus = 1 AND u.tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')
                  AND u.zona = ? AND u.departamento IS NOT NULL AND u.departamento <> 0
                ORDER BY nombre";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $zonaF);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $labs[] = ['id' => $row['id'], 'nombre' => $row['nombre'] !== null ? $row['nombre'] : ('Depto ' . $row['id'])];
        }
        $stmt->close();
    }
    echo json_encode(['status' => 'success', 'departamentos' => $labs]);
}


// Endpoint: disponibilidad de vehículos (cuadrícula de consulta, read-only)
// Filas = vehículos activos asignados a ingenieros (mess_rrhh.usuarios.tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')).
// Estatus por celda día×vehículo, prioridad: mantenimiento (2) > préstamo (1) > disponible (0).
if ($accion == 'disponibilidadVehiculos') {
    try {
        $fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : date('Y-m-d');
        $fechaFin    = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : date('Y-m-d', strtotime($fechaInicio . ' +6 days'));
        $zonaF       = isset($_POST['zona']) ? trim($_POST['zona']) : '';
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
        $sqlVeh = "SELECT DISTINCT inv.id_vehiculo, inv.placa, inv.marca, inv.modelo, inv.usuario, inv.area, u.zona,
                          (inv.id_vehiculo IN ($comodinList)) AS comodin
                   FROM inventario inv
                   LEFT JOIN mess_rrhh.usuarios u ON inv.id_usuario = u.id_usuario
                   WHERE inv.estatus = 'Activo'
                     AND (u.tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB') OR inv.id_vehiculo IN ($comodinList))";
        $params = []; $types = '';
        // Filtro de Área/Zona: por la `zona` del ingeniero dueño del vehículo (mess_rrhh.usuarios.zona)
        if ($zonaF !== '') { $sqlVeh .= " AND u.zona = ?"; $params[] = $zonaF; $types .= 's'; }
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

// Endpoint: zonas (área / laboratorio) para el filtro del módulo de vehículos.
// Cada valor distinto de `usuarios.zona` entre los ING/JEFE con vehículo activo es una opción.
// Se DERIVA de la población real del grid -> siempre consistente con lo que se muestra.
if ($accion == 'zonasVehiculos') {
    try {
        $connCV = conexionVehiculos();
        $sql = "SELECT DISTINCT u.zona
                FROM inventario inv
                JOIN mess_rrhh.usuarios u ON inv.id_usuario = u.id_usuario
                WHERE inv.estatus = 'Activo' AND u.tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')
                  AND u.zona IS NOT NULL AND TRIM(u.zona) <> ''
                ORDER BY u.zona";
        $res = $connCV->query($sql);
        $zonas = [];
        while ($row = $res->fetch_assoc()) { $zonas[] = $row['zona']; }
        $connCV->close();
        echo json_encode(['status' => 'success', 'zonas' => $zonas]);
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
                WHERE inv.estatus = 'Activo' AND u.tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')
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
                WHERE inv.estatus = 'Activo' AND (u.tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB') OR inv.id_vehiculo IN ($comodinList))
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
