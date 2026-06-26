<?php
// Endpoints AJAX para la vista de Entregas/Enlaces de Logística.
// Patrón igual al de acciones_solicitud.php: include conn.php, prepared statements, respuesta JSON.
header('Content-Type: application/json');
include 'conn.php';
include 'config_entregas.php';
mysqli_set_charset($conn, "utf8mb4");

$opcion              = $_POST['opcion'] ?? '';
$departamento_cookie = $_COOKIE['departamento'] ?? null;
$noEmpleado_cookie   = $_COOKIE['noEmpleado'] ?? null;

// Gating server-side: solo el departamento de Logística puede gestionar enlaces.
if ((string)$departamento_cookie !== DEPTO_LOGISTICA) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado: solo Logística puede gestionar enlaces.']);
    exit;
}

// Normaliza un valor de <input datetime-local> (YYYY-MM-DDTHH:MM) a datetime de MySQL. Vacío => null.
function normalizarFechaMy($v) {
    $v = trim((string)$v);
    if ($v === '') {
        return null;
    }
    $v = str_replace('T', ' ', $v);
    if (strlen($v) === 16) {
        $v .= ':00';
    }
    return $v;
}

// Genera el siguiente folio con formato LOG-AAAA-NNN (secuencial por año, mínimo 3 dígitos).
function generarFolioEnlace($conn) {
    $prefijo = 'LOG-' . date('Y') . '-';
    $like    = $prefijo . '%';
    $maxnum  = 0;
    if ($stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)) AS maxnum
                                  FROM enlaces_logistica WHERE folio LIKE ?")) {
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $maxnum = ($row && $row['maxnum'] !== null) ? (int)$row['maxnum'] : 0;
        $stmt->close();
    }
    return $prefijo . str_pad($maxnum + 1, 3, '0', STR_PAD_LEFT);
}

// Registra una entrada en el historial de estatus de un enlace.
function registrarHistorialEnlace($conn, $enlaceId, $estatus, $comentario, $noEmpleado) {
    if ((int)$enlaceId <= 0) {
        return;
    }
    if ($stmt = $conn->prepare("INSERT INTO enlaces_estatus_historial (enlace_id, estatus, comentario, capturado_por)
                                VALUES (?, ?, ?, ?)")) {
        $com = ($comentario === '') ? null : $comentario;
        $stmt->bind_param('isss', $enlaceId, $estatus, $com, $noEmpleado);
        $stmt->execute();
        $stmt->close();
    }
}

// ------------------------------------------------------------------
// LISTAR ENLACES (con filtro opcional por estatus)
// ------------------------------------------------------------------
if ($opcion === 'listarEnlaces') {
    $estatus = $_POST['estatus'] ?? '';

    $sql    = "SELECT * FROM enlaces_logistica";
    $params = [];
    $types  = '';
    if ($estatus !== '' && in_array($estatus, ESTATUS_ENLACES, true)) {
        $sql     .= " WHERE estatus = ?";
        $params[] = $estatus;
        $types   .= 's';
    }
    $sql .= " ORDER BY id DESC";

    if ($stmt = $conn->prepare($sql)) {
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $enlaces = [];
        while ($row = $result->fetch_assoc()) {
            $enlaces[] = $row;
        }
        echo json_encode(['status' => 'success', 'enlaces' => $enlaces]);
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo preparar la consulta.']);
    }
    exit;
}

// ------------------------------------------------------------------
// GUARDAR ENLACE (alta si id vacío, edición si id > 0)
// ------------------------------------------------------------------
if ($opcion === 'guardarEnlace') {
    $id          = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : 0;
    $origen      = trim($_POST['origen'] ?? '');
    $destino     = trim($_POST['destino'] ?? '');
    $contenido   = trim($_POST['contenido'] ?? '');
    $responsable = trim($_POST['responsable'] ?? '');
    $fenvio      = normalizarFechaMy($_POST['fecha_envio'] ?? '');
    $fest        = normalizarFechaMy($_POST['fecha_entrega_estimada'] ?? '');
    $estatus     = $_POST['estatus'] ?? 'Pendiente';
    $comentario  = trim($_POST['comentario'] ?? '');

    if ($origen === '' || $destino === '' || $contenido === '') {
        echo json_encode(['status' => 'error', 'message' => 'Origen, destino y contenido son obligatorios.']);
        exit;
    }
    if (!in_array($estatus, ESTATUS_ENLACES, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Estatus inválido.']);
        exit;
    }
    // Estatus que exigen comentario (p.ej. Reprogramado)
    if (in_array($estatus, ESTATUS_REQUIERE_COMENTARIO, true) && $comentario === '') {
        echo json_encode(['status' => 'error', 'message' => 'El estatus "' . $estatus . '" requiere un comentario.']);
        exit;
    }
    $fechaReprog = in_array($estatus, ESTATUS_REQUIERE_COMENTARIO, true) ? date('Y-m-d H:i:s') : null;

    if ($id > 0) {
        // Estatus/comentario previos para decidir si se registra historial (el folio NO se modifica en edición)
        $prevEstatus = null; $prevComentario = null;
        if ($pstmt = $conn->prepare("SELECT estatus, comentario FROM enlaces_logistica WHERE id = ?")) {
            $pstmt->bind_param('i', $id);
            $pstmt->execute();
            $pres = $pstmt->get_result();
            if ($prow = $pres->fetch_assoc()) { $prevEstatus = $prow['estatus']; $prevComentario = $prow['comentario']; }
            $pstmt->close();
        }
        $sql = "UPDATE enlaces_logistica
                   SET origen = ?, destino = ?, contenido = ?, responsable = ?,
                       fecha_envio = ?, fecha_entrega_estimada = ?, estatus = ?, comentario = ?,
                       fecha_reprogramacion = ?
                 WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('sssssssssi', $origen, $destino, $contenido, $responsable,
                              $fenvio, $fest, $estatus, $comentario, $fechaReprog, $id);
        }
    } else {
        $folio = generarFolioEnlace($conn);
        $sql = "INSERT INTO enlaces_logistica
                    (folio, origen, destino, contenido, responsable, fecha_envio, fecha_entrega_estimada,
                     estatus, comentario, fecha_reprogramacion, capturado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('sssssssssss', $folio, $origen, $destino, $contenido, $responsable,
                              $fenvio, $fest, $estatus, $comentario, $fechaReprog, $noEmpleado_cookie);
        }
    }

    if (isset($stmt) && $stmt->execute()) {
        $nuevoId = ($id > 0 ? $id : $stmt->insert_id);
        $stmt->close();
        // Historial: en alta siempre; en edición sólo si cambió el estatus o el comentario
        if ($id === 0) {
            registrarHistorialEnlace($conn, $nuevoId, $estatus, $comentario, $noEmpleado_cookie);
        } elseif ($estatus !== $prevEstatus || $comentario !== (string)$prevComentario) {
            registrarHistorialEnlace($conn, $nuevoId, $estatus, $comentario, $noEmpleado_cookie);
        }
        echo json_encode(['status' => 'success', 'message' => 'Enlace guardado.', 'id' => $nuevoId]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el enlace.']);
    }
    exit;
}

// ------------------------------------------------------------------
// CAMBIAR ESTATUS (con regla de comentario obligatorio)
// ------------------------------------------------------------------
if ($opcion === 'cambiarEstatusEnlace') {
    $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $estatus    = $_POST['estatus'] ?? '';
    $comentario = trim($_POST['comentario'] ?? '');

    if ($id <= 0 || !in_array($estatus, ESTATUS_ENLACES, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
        exit;
    }
    if (in_array($estatus, ESTATUS_REQUIERE_COMENTARIO, true) && $comentario === '') {
        echo json_encode(['status' => 'error', 'message' => 'El estatus "' . $estatus . '" requiere un comentario.']);
        exit;
    }

    if (in_array($estatus, ESTATUS_REQUIERE_COMENTARIO, true)) {
        $fechaReprog = date('Y-m-d H:i:s');
        $sql  = "UPDATE enlaces_logistica SET estatus = ?, comentario = ?, fecha_reprogramacion = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', $estatus, $comentario, $fechaReprog, $id);
    } elseif ($comentario !== '') {
        $sql  = "UPDATE enlaces_logistica SET estatus = ?, comentario = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $estatus, $comentario, $id);
    } else {
        $sql  = "UPDATE enlaces_logistica SET estatus = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $estatus, $id);
    }

    if (isset($stmt) && $stmt->execute()) {
        $stmt->close();
        registrarHistorialEnlace($conn, $id, $estatus, $comentario, $noEmpleado_cookie);
        echo json_encode(['status' => 'success', 'message' => 'Estatus actualizado.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estatus.']);
    }
    exit;
}

// ------------------------------------------------------------------
// HISTORIAL DE ESTATUS de un enlace
// ------------------------------------------------------------------
if ($opcion === 'historialEnlace') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
        exit;
    }
    $historial = [];
    if ($stmt = $conn->prepare("SELECT estatus, comentario, fecha
                                  FROM enlaces_estatus_historial
                                 WHERE enlace_id = ? ORDER BY id DESC")) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $historial[] = $row;
        }
        $stmt->close();
    }
    echo json_encode(['status' => 'success', 'historial' => $historial]);
    exit;
}

// ------------------------------------------------------------------
// EMPLEADOS (para el select de responsable). Nombre compuesto de nombres+apellidos.
// ------------------------------------------------------------------
if ($opcion === 'empleadosEntrega') {
    $empleados = [];
    $sql = "SELECT id_usuario,
                   TRIM(CONCAT_WS(' ', NULLIF(nombres, ''), NULLIF(apellidos, ''))) AS nombre_completo,
                   nombre
              FROM usuarios
             WHERE estatus = 1
             ORDER BY nombre_completo";
    if ($res = $conn->query($sql)) {
        while ($row = $res->fetch_assoc()) {
            $nombre = trim((string)$row['nombre_completo']);
            if ($nombre === '') {
                $nombre = trim((string)($row['nombre'] ?? ''));
            }
            $empleados[] = ['noEmpleado' => $row['id_usuario'], 'nombre' => $nombre];
        }
    }
    echo json_encode(['status' => 'success', 'empleados' => $empleados]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Opción no reconocida.']);
exit;
?>
