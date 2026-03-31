<?php
include("conn.php");
header('Content-Type: application/json; charset=utf-8');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$noEmpleado = isset($_COOKIE['noEmpleado']) ? trim($_COOKIE['noEmpleado']) : '';
$idNotificacion = isset($_POST['idNotificacion']) ? intval($_POST['idNotificacion']) : 0;
$idRegistroReferencia = isset($_POST['id_registro_referencia']) ? intval($_POST['id_registro_referencia']) : 0;
$solicita = isset($_POST['solicita']) ? trim((string)$_POST['solicita']) : '';
$id_usuario_Destino = intval($noEmpleado);

// Función para obtener iniciales de un nombre completo
function obtenerIniciales($nombreCompleto) {
    $nombreCompleto = trim((string)$nombreCompleto);
    if ($nombreCompleto === '') {
        return 'NA';
    }

    $partes = preg_split('/\s+/', $nombreCompleto);
    $partes = array_values(array_filter($partes, function ($parte) {
        return trim($parte) !== '';
    }));

    $totalPartes = count($partes);
    if ($totalPartes >= 3) {
        $primeraInicial = strtoupper(substr($partes[0], 0, 1));
        $apellidoPaternoInicial = strtoupper(substr($partes[$totalPartes - 2], 0, 1));
        $apellidoMaternoInicial = strtoupper(substr($partes[$totalPartes - 1], 0, 1));
        return $primeraInicial . $apellidoPaternoInicial . $apellidoMaternoInicial;
    }

    $iniciales = '';
    foreach ($partes as $parte) {
        $iniciales .= strtoupper(substr($parte, 0, 1));
    }

    return $iniciales !== '' ? $iniciales : 'NA';
}

// Función para formatear fechas en formato corto
function formatearFechaCorta($fecha) {
    $fecha = trim((string)$fecha);
    if ($fecha === '') {
        return '';
    }

    $timestamp = strtotime($fecha);
    if ($timestamp === false) {
        return $fecha;
    }

    return date('d/m/Y H:i', $timestamp);
}

// Registrar Notificación para Entrada de Equipo
if ($accion === 'registrarNotificacionEntrada') {
    $id_seguimiento = isset($_POST['id_seguimiento']) ? intval($_POST['id_seguimiento']) : 0;
    $idRegistroReferencia = isset($_POST['id_registro_referencia']) ? intval($_POST['id_registro_referencia']) : 0;
    $idUsuarioActualiza = intval($noEmpleado);
    $accionNotificacion = 'ActualizacionEstatus';
    $sistema = 'entradasEq';
    $archivo = 'entradaTareas';

    if ($noEmpleado === '' || !ctype_digit($noEmpleado)) {
        echo json_encode(['success' => false, 'message' => 'Tu sesión expiró, por favor inicia sesión nuevamente']);
        exit;
    }
    if ($idRegistroReferencia <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de registro inválido']);
        exit;
    }


    // Obtener el usuario destino a partir del registro de entrada
    $sqlDestinos = "SELECT er.capturado_por
                    FROM entrada_registros er
                    WHERE er.id_registro = ?
                    LIMIT 1";
    $stmtDestinos = $conn->prepare($sqlDestinos);
    if (!$stmtDestinos) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar destinos']);
        exit;
    }

    $stmtDestinos->bind_param("i", $idRegistroReferencia);
    $stmtDestinos->execute();
    $resultDestinos = $stmtDestinos->get_result();

    $id_usuario_Destino = 0;
    if ($row = $resultDestinos->fetch_assoc()) {
        $id_usuario_Destino = intval($row['capturado_por']);
    }
    $stmtDestinos->close();

    if ($id_usuario_Destino <= 0) {
        echo json_encode(['success' => true, 'insertados' => 0, 'message' => 'Sin usuario capturador']);
        exit;
    }

    $sqlInsert = "INSERT INTO notificacion_historial
        (id_usuario_actualiza, id_usuario_destino, accion, sistema, archivo, id_registro_referencia, fecha_creacion, fecha_atencion, recordar, estatus)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, NULL, 'NoLeida')";

    $stmtInsert = $conn->prepare($sqlInsert);
    if (!$stmtInsert) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar inserción']);
        exit;
    }

    $insertados = 0;
    $stmtInsert->bind_param("iisssi", $idUsuarioActualiza, $id_usuario_Destino, $accionNotificacion, $sistema, $archivo, $id_seguimiento);
    if ($stmtInsert->execute()) {
        $insertados++;
    }
    $stmtInsert->close();

    echo json_encode(['success' => true, 'insertados' => $insertados], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validar que noEmpleado es un número entero positivo antes de continuar
if ($noEmpleado === '' || !ctype_digit($noEmpleado)) {
    echo json_encode(['success' => false, 'message' => 'Cookie noEmpleado inválida']);
    exit;
}

// Contar Notificaciones No Leídas
if ($accion === 'contarNotificaciones') {
    $sqlCuentaNoti = "SELECT COUNT(*) AS total
            FROM notificacion_historial
            WHERE id_usuario_destino = ?
            AND estatus = 'NoLeida'";

    $stmt = $conn->prepare($sqlCuentaNoti);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar conteo']);
        exit;
    }

    $stmt->bind_param("i", $id_usuario_Destino);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $total = isset($row['total']) ? intval($row['total']) : 0;
    echo json_encode(['success' => true, 'total' => $total], JSON_UNESCAPED_UNICODE);
    exit;
}

// Cargar Registros de Notificaciones
if ($accion === 'cargarNotificaciones') {
    $sqlCargarNoti = "SELECT nh.id, nh.accion, nh.sistema, nh.archivo, nh.id_registro_referencia,
                            nh.fecha_creacion, nh.fecha_atencion, nh.recordar, nh.estatus,
                            es.id_usuario_nota, es.nota, es.fecha_actualizacion,
                            us.nombre AS nombre_usuario_nota
            FROM notificacion_historial nh
            LEFT JOIN entrada_seguimiento es
                ON es.id_seguimiento = nh.id_registro_referencia
                AND nh.sistema = 'entradasEq'
            LEFT JOIN usuarios us ON us.id_usuario = es.id_usuario_nota
            WHERE nh.id_usuario_destino = ?
            AND nh.estatus = 'NoLeida'
            ORDER BY nh.fecha_creacion DESC";

    $stmt = $conn->prepare($sqlCargarNoti);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar consulta']);
        exit;
    }

    $stmt->bind_param("i", $id_usuario_Destino);
    $stmt->execute();
    $result = $stmt->get_result();

    $notificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $estatus = isset($row['estatus']) ? $row['estatus'] : 'NoLeida';
        $nota = trim((string)($row['nota'] ?? ''));
        $nombreUsuarioNota = (string)($row['nombre_usuario_nota'] ?? '');
        $fechaActualizacion = isset($row['fecha_actualizacion']) ? formatearFechaCorta($row['fecha_actualizacion']) : '';
        $fechaCreacion = isset($row['fecha_creacion']) ? formatearFechaCorta($row['fecha_creacion']) : '';
        
        $notificaciones[] = [
            'id' => $row['id'],
            'mensaje' => $nota !== '' ? $nota : $row['accion'],
            'accion' => $row['accion'],
            'sistema' => $row['sistema'],
            'archivo' => $row['archivo'],
            'id_registro_referencia' => $row['id_registro_referencia'],
            'fecha' => $fechaCreacion,
            'fecha_actualizacion' => $fechaActualizacion,
            'fecha_atencion' => $row['fecha_atencion'],
            'recordar' => $row['recordar'],
            'id_usuario_nota' => isset($row['id_usuario_nota']) ? intval($row['id_usuario_nota']) : 0,
            'iniciales' => obtenerIniciales($nombreUsuarioNota),
            'nota' => $nota,
            'estatus' => $estatus,
            'leida' => strcasecmp($estatus, 'Leida') === 0 ? 1 : 0
        ];
    }

    $stmt->close();
    echo json_encode([
        'success' => true,
        'total' => count($notificaciones),
        'notificaciones' => $notificaciones
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Marcar Notificación como Leída
if ($accion === 'marcarLeida') {
    if ($idNotificacion <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de notificación no válido']);
        exit;
    }

    $sql = "UPDATE notificacion_historial
            SET estatus = 'Leida', fecha_atencion = NOW()
            WHERE id = ? AND id_usuario_destino = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar actualización']);
        exit;
    }

    $stmt->bind_param("ii", $idNotificacion, $id_usuario_Destino);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $ok, 'message' => $ok ? 'OK' : 'Error al actualizar la notificación']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no soportada']);

