<?php
include("conn.php");
header('Content-Type: application/json; charset=utf-8');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$noEmpleado = isset($_COOKIE['noEmpleado']) ? trim($_COOKIE['noEmpleado']) : '';
$idNotificacion = isset($_POST['idNotificacion']) ? intval($_POST['idNotificacion']) : 0;

if ($noEmpleado === '' || !ctype_digit($noEmpleado)) {
    echo json_encode(['success' => false, 'message' => 'Cookie noEmpleado inválida']);
    exit;
}

$idDestino = intval($noEmpleado);

if ($accion === 'contarNotificaciones') {
    $sqlCuentaNoti = "SELECT COUNT(*) AS total
            FROM historial_notificaciones
            WHERE id_usuario_destino = ?
            AND estatus = 'No Leida'";

    $stmt = $conn->prepare($sqlCuentaNoti);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar conteo']);
        exit;
    }

    $stmt->bind_param("i", $idDestino);
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
    $sqlCargarNoti = "SELECT id, accion, sistema, archivo, id_registro_referencia, fecha_creacion, fecha_atencion, recordar, estatus
            FROM historial_notificaciones
            WHERE id_usuario_destino = ?
            and estatus = 'No Leida'
            ORDER BY fecha_creacion DESC";

    $stmt = $conn->prepare($sqlCargarNoti);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar consulta']);
        exit;
    }

    $stmt->bind_param("i", $idDestino);
    $stmt->execute();
    $result = $stmt->get_result();

    $notificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $estatus = isset($row['estatus']) ? $row['estatus'] : 'No Leida';
        $notificaciones[] = [
            'id' => $row['id'],
            'mensaje' => $row['accion'],
            'accion' => $row['accion'],
            'sistema' => $row['sistema'],
            'archivo' => $row['archivo'],
            'id_registro_referencia' => $row['id_registro_referencia'],
            'fecha' => $row['fecha_creacion'],
            'fecha_atencion' => $row['fecha_atencion'],
            'recordar' => $row['recordar'],
            'estatus' => $estatus,
            'leida' => strcasecmp($estatus, 'Leida') === 0 ? 1 : 0
        ];
    }

    $stmt->close();
    echo json_encode(['success' => true, 'notificaciones' => $notificaciones], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($accion === 'marcarLeida') {
    if ($idNotificacion <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de notificación no válido']);
        exit;
    }

    $sql = "UPDATE historial_notificaciones
            SET estatus = 'Leida', fecha_atencion = NOW()
            WHERE id = ? AND id_usuario_destino = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar actualización']);
        exit;
    }

    $stmt->bind_param("ii", $idNotificacion, $idDestino);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $ok, 'message' => $ok ? 'OK' : 'Error al actualizar la notificación']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no soportada']);

