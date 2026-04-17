<?php
include("conn.php");
header('Content-Type: application/json; charset=utf-8');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$noEmpleado = isset($_COOKIE['noEmpleado']) ? trim($_COOKIE['noEmpleado']) : '';
$idNotificacion = isset($_POST['idNotificacion']) ? intval($_POST['idNotificacion']) : 0;
$idRegistroReferencia = isset($_POST['id_registro_referencia']) ? intval($_POST['id_registro_referencia']) : 0;
$solicita = isset($_POST['solicita']) ? trim((string)$_POST['solicita']) : '';
$sistema = isset($_POST['sistema']) ? trim($_POST['sistema']) : '';
$id_usuario_Destino = intval($noEmpleado);

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
// Notifica a: capturado_por + ingenieros asignados + admins (45, 177, 555, 523)
if ($accion === 'registrarNotificacionEntrada') {
    $idRegistroReferencia = isset($_POST['id_registro_referencia']) ? intval($_POST['id_registro_referencia']) : 0;
    $idUsuarioActualiza   = intval($noEmpleado);
    $accionNotificacion   = isset($_POST['accion_notificacion']) ? trim($_POST['accion_notificacion']) : 'ActualizacionEstatus';
    $sistemaLocal         = 'entradasEq';
    $archivoLocal         = 'entradaTareas';
    $recordarLocal        = 'Actualizacion de estatus';

    if ($noEmpleado === '' || !ctype_digit($noEmpleado)) {
        echo json_encode(['success' => false, 'message' => 'Tu sesión expiró, por favor inicia sesión nuevamente']);
        exit;
    }
    if ($idRegistroReferencia <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de registro inválido']);
        exit;
    }

    // Obtener todos los destinatarios: capturado_por UNION ingenieros asignados UNION admins
    $sqlDestinos = "SELECT DISTINCT u.noEmpleado AS destinatario
                    FROM usuarios u
                    WHERE u.noEmpleado = (
                        SELECT capturado_por FROM entrada_registros WHERE id_registro = ? LIMIT 1
                    )
                    UNION
                    SELECT DISTINCT u2.noEmpleado
                    FROM entrada_log_ingenieros eli
                    INNER JOIN usuarios u2 ON eli.id_ing = u2.id_usuario
                    WHERE eli.id_registro = ?
                      AND eli.estatus = 'ASIGNADO'
                    UNION
                    SELECT noEmpleado
                    FROM usuarios
                    WHERE noEmpleado IN (45, 177, 555, 523)
                      AND estatus = 1";

    $stmtDestinos = $conn->prepare($sqlDestinos);
    if (!$stmtDestinos) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar destinos']);
        exit;
    }

    $stmtDestinos->bind_param("ii", $idRegistroReferencia, $idRegistroReferencia);
    $stmtDestinos->execute();
    $resultDestinos = $stmtDestinos->get_result();
    $stmtDestinos->close();

    if ($resultDestinos->num_rows === 0) {
        echo json_encode(['success' => true, 'insertados' => 0, 'message' => 'Sin destinatarios']);
        exit;
    }

    $sqlInsert = "INSERT INTO notificacion_historial
        (id_usuario_actualiza, id_usuario_destino, accion, sistema, archivo, id_registro_referencia, fecha_creacion, fecha_atencion, recordar, estatus)
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, ?, 'NoLeida')";

    $stmtInsert = $conn->prepare($sqlInsert);
    if (!$stmtInsert) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar inserción']);
        exit;
    }

    $conn->begin_transaction();
    $insertados = 0;
    $errorTransaccion = false;

    while ($row = $resultDestinos->fetch_assoc()) {
        $destinatario = intval($row['destinatario']);
        $stmtInsert->bind_param("iissiis",
            $idUsuarioActualiza, $destinatario, $accionNotificacion,
            $sistemaLocal, $archivoLocal, $idRegistroReferencia, $recordarLocal
        );
        if ($stmtInsert->execute()) {
            $insertados++;
        } else {
            $errorTransaccion = true;
            break;
        }
    }

    if ($errorTransaccion) {
        $conn->rollback();
        $stmtInsert->close();
        echo json_encode(['success' => false, 'message' => 'Error al insertar, se revirtió la transacción']);
        exit;
    }

    $conn->commit();
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

// Generar Notificaciones Internas de Planeación
if ($accion === 'registrarNotificacionPlaneacion') {

    // Buscar servicios por vencer en los próximos 2 días del usuario en sesión
    $sqlServicios = "SELECT s.id, s.capturado_por, s.start_date, s.ds_cliente, s.estatus
                     FROM servicios_planeados_mess s
                     WHERE s.estatus IN ('Fechareservadasininformación', 'Pendientedeinformacion')
                       AND s.start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 DAY)
                       AND s.capturado_por = ?";

    $stmtServicios = $conn->prepare($sqlServicios);
    if (!$stmtServicios) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar consulta de servicios']);
        exit;
    }
    $stmtServicios->bind_param("i", $id_usuario_Destino);
    $stmtServicios->execute();
    $resultServicios = $stmtServicios->get_result();
    $stmtServicios->close();

    if ($resultServicios->num_rows === 0) {
        echo json_encode(['success' => true, 'insertados' => 0, 'message' => '0 notificaciones generadas'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validar duplicidad: id_usuario_destino + sistema + id_registro_referencia
    $sqlExiste = "SELECT COUNT(*) AS existe
                  FROM notificacion_historial
                  WHERE id_usuario_destino     = ?
                    AND sistema                = 'planeacion'
                    AND id_registro_referencia = ?";

    $stmtExiste = $conn->prepare($sqlExiste);
    if (!$stmtExiste) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar validación de duplicidad']);
        exit;
    }

    // Preparar INSERT de notificación (id_usuario_actualiza = 0 representa al sistema)
    $sqlInsert = "INSERT INTO notificacion_historial
                    (id_usuario_actualiza, id_usuario_destino, accion, sistema,
                     archivo, id_registro_referencia, fecha_creacion, recordar, estatus)
                  VALUES
                    (0, ?, 'ServicioPorVencer', 'planeacion',
                     'seguimiento_actividades', ?, NOW(), 'Servicio por vencer', 'NoLeida')";

    $stmtInsert = $conn->prepare($sqlInsert);
    if (!$stmtInsert) {
        $stmtExiste->close();
        echo json_encode(['success' => false, 'message' => 'Error al preparar inserción de notificación']);
        exit;
    }

    $conn->begin_transaction();
    $insertados = 0;
    $errorTransaccion = false;

    while ($servicio = $resultServicios->fetch_assoc()) {
        $idServicio = intval($servicio['id']);

        // Verificar si ya existe notificación para este servicio
        $stmtExiste->bind_param("ii", $id_usuario_Destino, $idServicio);
        $stmtExiste->execute();
        $resExiste = $stmtExiste->get_result();
        $rowExiste = $resExiste->fetch_assoc();

        if (intval($rowExiste['existe']) > 0) {
            continue; // Ya notificado, saltar
        }

        // Insertar nueva notificación
        $stmtInsert->bind_param("ii", $id_usuario_Destino, $idServicio);
        if ($stmtInsert->execute()) {
            $insertados++;
        } else {
            $errorTransaccion = true;
            break;
        }
    }

    if ($errorTransaccion) {
        $conn->rollback();
        $stmtExiste->close();
        $stmtInsert->close();
        echo json_encode(['success' => false, 'message' => 'Error al insertar notificación, se revirtió la transacción']);
        exit;
    }

    $conn->commit();
    $stmtExiste->close();
    $stmtInsert->close();

    echo json_encode([
        'success'   => true,
        'insertados' => $insertados,
        'message'   => $insertados . ' notificaciones generadas'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Generar Notificaciones Internas de EntradasEq
// con entrega próxima (3 días) o vencida. Dedup por usuario+sistema+registro+accion.
if ($accion === 'registrarNotificacionesEntradas') {

    $sqlEntradas = "SELECT DISTINCT er.id_registro, er.estatus, er.fecha_promesa_entrega
                    FROM entrada_registros er
                    LEFT JOIN entrada_log_ingenieros eli
                        ON er.id_registro = eli.id_registro AND eli.estatus = 'ASIGNADO'
                    LEFT JOIN usuarios u_ing ON eli.id_ing = u_ing.id_usuario
                    WHERE er.fecha_promesa_entrega IS NOT NULL
                      AND (
                          er.capturado_por = ?
                          OR u_ing.noEmpleado = ?
                          OR ? IN (45, 177, 555, 523)
                      )
                      AND (
                          (er.estatus NOT IN ('Terminado', 'Entregado')
                              AND er.fecha_promesa_entrega BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY))
                          OR
                          (er.estatus NOT IN ('Terminado')
                              AND DATE(er.fecha_promesa_entrega) < CURDATE())
                      )";

    $stmtEntradas = $conn->prepare($sqlEntradas);
    if (!$stmtEntradas) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar consulta de entradas']);
        exit;
    }
    $stmtEntradas->bind_param("iii", $id_usuario_Destino, $id_usuario_Destino, $id_usuario_Destino);
    $stmtEntradas->execute();
    $resultEntradas = $stmtEntradas->get_result();
    $stmtEntradas->close();

    if ($resultEntradas->num_rows === 0) {
        echo json_encode(['success' => true, 'insertados' => 0, 'message' => '0 notificaciones generadas'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Dedup: id_usuario_destino + sistema + id_registro_referencia + accion
    $sqlExiste = "SELECT COUNT(*) AS existe
                  FROM notificacion_historial
                  WHERE id_usuario_destino     = ?
                    AND sistema                = 'entradasEq'
                    AND id_registro_referencia = ?
                    AND accion                 = ?";

    $stmtExiste = $conn->prepare($sqlExiste);
    if (!$stmtExiste) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar validación de duplicidad']);
        exit;
    }

    $sqlInsert = "INSERT INTO notificacion_historial
                    (id_usuario_actualiza, id_usuario_destino, accion, sistema,
                     archivo, id_registro_referencia, fecha_creacion, recordar, estatus)
                  VALUES
                    (523, ?, ?, 'entradasEq',
                     'entradaDetalleEntradas', ?, NOW(), ?, 'NoLeida')";

    $stmtInsert = $conn->prepare($sqlInsert);
    if (!$stmtInsert) {
        $stmtExiste->close();
        echo json_encode(['success' => false, 'message' => 'Error al preparar inserción']);
        exit;
    }

    $conn->begin_transaction();
    $insertados = 0;
    $errorTransaccion = false;
    $hoy = date('Y-m-d');

    while ($entrada = $resultEntradas->fetch_assoc()) {
        $idRegistro = intval($entrada['id_registro']);
        $fechaPromesa = $entrada['fecha_promesa_entrega'];

        if ($fechaPromesa < $hoy) {
            $accionNoti = 'EntregaVencida';
            $recordar   = 'Equipo con retraso en entrega';
        } else {
            $accionNoti = 'EntregaProxima';
            $recordar   = 'Equipo próximo a vencer';
        }

        // Verificar duplicado
        $stmtExiste->bind_param("iis", $id_usuario_Destino, $idRegistro, $accionNoti);
        $stmtExiste->execute();
        $resExiste = $stmtExiste->get_result();
        $rowExiste = $resExiste->fetch_assoc();

        if (intval($rowExiste['existe']) > 0) {
            continue;
        }

        $stmtInsert->bind_param("isis", $id_usuario_Destino, $accionNoti, $idRegistro, $recordar);
        if ($stmtInsert->execute()) {
            $insertados++;
        } else {
            $errorTransaccion = true;
            break;
        }
    }

    if ($errorTransaccion) {
        $conn->rollback();
        $stmtExiste->close();
        $stmtInsert->close();
        echo json_encode(['success' => false, 'message' => 'Error al insertar, se revirtió la transacción']);
        exit;
    }

    $conn->commit();
    $stmtExiste->close();
    $stmtInsert->close();

    echo json_encode([
        'success'    => true,
        'insertados' => $insertados,
        'message'    => $insertados . ' notificaciones generadas'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Cargar Registros de Notificaciones
if ($accion === 'cargarNotificaciones') {
    $sqlCargarNoti = "  SELECT nh.*, us.nombre AS nombre_actualiza
                        FROM notificacion_historial nh
                        LEFT JOIN usuarios us ON us.noEmpleado = nh.id_usuario_actualiza
                        WHERE nh.id_usuario_destino = ?
                            AND nh.estatus = 'NoLeida'
                            AND (? = '' OR nh.sistema = ?)
                        ORDER BY nh.fecha_creacion DESC";

    $stmt = $conn->prepare($sqlCargarNoti);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar consulta']);
        exit;
    }

    $stmt->bind_param("iss", $id_usuario_Destino, $sistema, $sistema);
    $stmt->execute();
    $result = $stmt->get_result();

    $notificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $estatus = isset($row['estatus']) ? $row['estatus'] : 'NoLeida';
        $nota = trim((string)($row['nota'] ?? ''));
        $fechaActualizacion = isset($row['fecha_actualizacion']) ? formatearFechaCorta($row['fecha_actualizacion']) : '';
        $fechaCreacion = isset($row['fecha_creacion']) ? formatearFechaCorta($row['fecha_creacion']) : '';
        
        $notificaciones[] = [
            'id' => $row['id'],
            'mensaje' => $nota !== '' ? $nota : $row['accion'],
            'accion' => $row['accion'],
            'sistema' => $row['sistema'],
            'archivo' => $row['archivo'],
            'id_registro_referencia' => $row['id_registro_referencia'],
            'id_usuario_actualiza' => isset($row['id_usuario_actualiza']) ? intval($row['id_usuario_actualiza']) : 0,
            'usuario_actualiza_nombre' => isset($row['nombre_actualiza']) ? trim((string)$row['nombre_actualiza']) : '',
            'fecha' => $fechaCreacion,
            'fecha_actualizacion' => $fechaActualizacion,
            'fecha_atencion' => $row['fecha_atencion'],
            'recordar' => $row['recordar'],
            'id_usuario_nota' => isset($row['id_usuario_nota']) ? intval($row['id_usuario_nota']) : 0,
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

