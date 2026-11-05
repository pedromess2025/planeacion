<?php
require_once 'conn.php';
$conn->set_charset('utf8mb4');

$fecha_get = isset($_GET['semana']) && is_string($_GET['semana']) ? $_GET['semana'] : date('Y-m-d');
$fecha = DateTime::createFromFormat('!Y-m-d', $fecha_get);
$erroresFecha = DateTime::getLastErrors();
if (!$fecha || ($erroresFecha !== false && ($erroresFecha['warning_count'] > 0 || $erroresFecha['error_count'] > 0))) {
    http_response_code(400);
    exit('La fecha de la semana no es válida.');
}

$fecha_inicio = clone $fecha;
$fecha_inicio->modify('monday this week');
$fecha_inicio = $fecha_inicio->format('Y-m-d');
$fecha_fin = date('Y-m-d', strtotime($fecha_inicio . ' + 6 days'));
$dias_solicitados = $_GET['dias'] ?? (isset($_GET['dia']) ? [$_GET['dia']] : []);
if (!is_array($dias_solicitados)) {
    http_response_code(400);
    exit('La selección de días no es válida.');
}
$fecha_minima_dias = date('Y-m-d', strtotime($fecha_inicio . ' -14 days'));
$filtro_dias = [];
foreach ($dias_solicitados as $dia_solicitado) {
    if (!is_string($dia_solicitado)) {
        http_response_code(400);
        exit('La fecha de uno de los días no es válida.');
    }

    $fecha_dia = DateTime::createFromFormat('!Y-m-d', $dia_solicitado);
    $erroresDia = DateTime::getLastErrors();
    if (
        !$fecha_dia ||
        ($erroresDia !== false && ($erroresDia['warning_count'] > 0 || $erroresDia['error_count'] > 0)) ||
        $fecha_dia->format('Y-m-d') !== $dia_solicitado ||
        $dia_solicitado < $fecha_minima_dias ||
        $dia_solicitado > $fecha_fin
    ) {
        http_response_code(400);
        exit('La fecha de uno de los días no es válida.');
    }
    $filtro_dias[] = $dia_solicitado;
}
$filtro_dias = array_values(array_unique($filtro_dias));
$fechas_consulta = $filtro_dias;
if (empty($fechas_consulta)) {
    for ($offset_dia = 0; $offset_dia < 7; $offset_dia++) {
        $fechas_consulta[] = date('Y-m-d', strtotime($fecha_inicio . " +{$offset_dia} days"));
    }
}
$filtro_ingeniero = isset($_GET['ingeniero']) && is_string($_GET['ingeniero'])
    ? trim($_GET['ingeniero'])
    : '';
$filtro_area = isset($_GET['area']) && is_string($_GET['area'])
    ? strtoupper(trim($_GET['area']))
    : '';

$sql_lista_ing = "
    SELECT DISTINCT tecnicos
    FROM seguimiento_ots
    WHERE tecnicos IS NOT NULL AND tecnicos != ''
    ORDER BY tecnicos
";
$resultado_lista_ing = $conn->query($sql_lista_ing);
if (!$resultado_lista_ing) {
    throw new RuntimeException('No fue posible consultar la lista de ingenieros: ' . $conn->error);
}
$lista_ingenieros = array_column($resultado_lista_ing->fetch_all(MYSQLI_ASSOC), 'tecnicos');

$resultado_ots = $conn->query("
    SELECT DISTINCT OT
    FROM seguimiento_ots
    WHERE OT IS NOT NULL AND OT != ''
");
if (!$resultado_ots) {
    throw new RuntimeException('No fue posible consultar la lista de áreas: ' . $conn->error);
}
$lista_areas = [];
foreach ($resultado_ots->fetch_all(MYSQLI_ASSOC) as $fila_ot) {
    $ot = strtoupper(trim((string)$fila_ot['OT']));
    if (preg_match('/^([A-Z]+)\d{2}/', $ot, $coincidencias)) {
        $lista_areas[$coincidencias[1]] = $coincidencias[1];
    }
}
$lista_areas = array_values($lista_areas);
sort($lista_areas);
if (!in_array($filtro_area, $lista_areas, true)) {
    $filtro_area = '';
}
$descripciones_areas = [
    'APP' => 'APLICACIONES',
    'AX' => 'ALTA EXACTITUD',
    'CNMD' => 'CNMD',
    'D' => 'D',
    'DINL' => 'DINL',
    'DISL' => 'DIMENSIONAL SLP',
    'DU' => 'DUREZA',
    'EL' => 'ELECTRICA',
    'ELSL' => 'ELECTRICA SLP',
    'EV' => 'EV',
    'FM' => 'FM',
    'FZ' => 'FUERZA',
    'FZSL' => 'FUERZA SLP',
    'HU' => 'HUMEDAD',
    'LC' => 'CALIBRACION',
    'LD' => 'DIMENSIONAL',
    'LDM' => 'LDM',
    'LE' => 'ELECTRICA',
    'MA' => 'MASA',
    'ME' => 'ME',
    'MG' => 'METALOGRAFIA',
    'MI' => 'MI',
    'MMM' => 'MMM',
    'MMZ' => 'MMZ',
    'MO' => 'MO',
    'MT' => 'MT',
    'MTS' => 'MTS',
    'OPT' => 'OPT',
    'PR' => 'PRESION',
    'PRSL' => 'PRESION SL',
    'PT' => 'PAR T',
    'PTSL' => 'PAR T SLP',
    'SFG' => 'SFG',
    'SL' => 'SL',
    'TE' => 'TEMPERATURA',
    'TF' => 'TIEMPO Y FRE',
    'TI' => 'TIEMPO',
    'VS' => 'VISION',
];

$placeholders_fechas = implode(', ', array_fill(0, count($fechas_consulta), '?'));
$sql_detalles = "
    SELECT tecnicos AS ingeniero, OT, start_date, end_date,
           CASE WHEN DATE(end_date) IN ($placeholders_fechas) THEN 'Terminada' ELSE 'En Proceso' END AS estado_calculado
    FROM seguimiento_ots
    WHERE tecnicos IS NOT NULL AND tecnicos != ''
      AND (DATE(end_date) IN ($placeholders_fechas) OR DATE(start_date) IN ($placeholders_fechas))
";
$tipos_detalles = str_repeat('s', count($fechas_consulta) * 3);
$params_detalles = array_merge($fechas_consulta, $fechas_consulta, $fechas_consulta);
if ($filtro_ingeniero !== '') {
    $sql_detalles .= ' AND tecnicos = ?';
    $tipos_detalles .= 's';
    $params_detalles[] = $filtro_ingeniero;
}
if ($filtro_area !== '') {
    $sql_detalles .= " AND UPPER(TRIM(OT)) REGEXP CONCAT('^', ?, '[0-9]{2}')";
    $tipos_detalles .= 's';
    $params_detalles[] = $filtro_area;
}
$sql_detalles .= ' ORDER BY ingeniero, estado_calculado DESC, start_date ASC';

$stmt_det = $conn->prepare($sql_detalles);
if (!$stmt_det) {
    throw new RuntimeException('No fue posible preparar la consulta de OTs: ' . $conn->error);
}
$stmt_det->bind_param($tipos_detalles, ...$params_detalles);
if (!$stmt_det->execute()) {
    $error = $stmt_det->error;
    $stmt_det->close();
    throw new RuntimeException('No fue posible consultar las OTs: ' . $error);
}
$filas_detalles = $stmt_det->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_det->close();

$datos_ingenieros = [];
$total_terminadas_sem = 0;
$total_proceso_sem = 0;
foreach ($filas_detalles as $row) {
    $ing = $row['ingeniero'];
    if (!isset($datos_ingenieros[$ing])) {
        $datos_ingenieros[$ing] = ['terminadas' => 0, 'en_proceso' => 0, 'detalles' => []];
    }

    if ($row['estado_calculado'] === 'Terminada') {
        $datos_ingenieros[$ing]['terminadas']++;
        $total_terminadas_sem++;
    } else {
        $datos_ingenieros[$ing]['en_proceso']++;
        $total_proceso_sem++;
    }
    $datos_ingenieros[$ing]['detalles'][] = $row;
}

$labels_ing = [];
$data_terminadas = [];
$data_proceso = [];
foreach ($datos_ingenieros as $ing => $datos) {
    $labels_ing[] = $ing;
    $data_terminadas[] = $datos['terminadas'];
    $data_proceso[] = $datos['en_proceso'];
}

$total_asignadas_sem = $total_terminadas_sem + $total_proceso_sem;
$cumplimiento_sem = $total_asignadas_sem > 0
    ? round(($total_terminadas_sem / $total_asignadas_sem) * 100)
    : 0;

$fechas_semana = [];
$params_semana = [];
foreach ($fechas_consulta as $fecha_consulta) {
    $fechas_semana[] = 'SELECT ? AS fecha';
    $params_semana[] = $fecha_consulta;
}

$sql_semana_dias = "
    SELECT fechas.fecha,
           COALESCE(SUM(CASE WHEN DATE(t.end_date) = fechas.fecha THEN 1 ELSE 0 END), 0) AS terminadas,
           COALESCE(SUM(CASE WHEN DATE(t.start_date) = fechas.fecha THEN 1 ELSE 0 END), 0) AS en_proceso
    FROM (" . implode(' UNION ALL ', $fechas_semana) . ") AS fechas
    LEFT JOIN seguimiento_ots t
      ON (DATE(t.start_date) = fechas.fecha OR DATE(t.end_date) = fechas.fecha)
";
$tipos_semana = str_repeat('s', count($fechas_consulta));
if ($filtro_ingeniero !== '') {
    $sql_semana_dias .= ' AND t.tecnicos = ?';
    $tipos_semana .= 's';
    $params_semana[] = $filtro_ingeniero;
}
if ($filtro_area !== '') {
    $sql_semana_dias .= " AND UPPER(TRIM(t.OT)) REGEXP CONCAT('^', ?, '[0-9]{2}')";
    $tipos_semana .= 's';
    $params_semana[] = $filtro_area;
}
$sql_semana_dias .= ' GROUP BY fechas.fecha ORDER BY fechas.fecha';

$stmt_sem = $conn->prepare($sql_semana_dias);
if (!$stmt_sem) {
    throw new RuntimeException('No fue posible preparar el resumen semanal: ' . $conn->error);
}
$stmt_sem->bind_param($tipos_semana, ...$params_semana);
if (!$stmt_sem->execute()) {
    $error = $stmt_sem->error;
    $stmt_sem->close();
    throw new RuntimeException('No fue posible consultar el resumen semanal: ' . $error);
}
$datos_semana = $stmt_sem->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_sem->close();

$labels_dias = [];
$data_dias_terminadas = [];
$data_dias_proceso = [];
$dias_nombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
foreach ($datos_semana as $dia) {
    $num_dia = date('w', strtotime($dia['fecha']));
    $labels_dias[] = $dias_nombres[$num_dia] . ' ' . date('d/m', strtotime($dia['fecha']));
    $data_dias_terminadas[] = $dia['terminadas'];
    $data_dias_proceso[] = $dia['en_proceso'];
}
