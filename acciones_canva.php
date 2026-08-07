<?php
header('Content-Type: application/json');

include 'conn.php';

// Acceso: mismo permiso que canva.php (planeacion/verSegEntradas). Este endpoint no pedía ni
// sesión: con la URL cualquiera sacaba el tablero completo de entradas a laboratorio.
exigeAccesoEspecialJson($conn, 'planeacion', 'verSegEntradas');

$accion = $_POST['accion'] ?? 'cargar_tablero';

if ($accion === 'cargar_tablero') {
    $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
    $fecha_fin    = $_POST['fecha_fin'] ?? date('Y-m-d', strtotime('+6 days'));
    $laboratorio  = $_POST['laboratorio'] ?? 'TODOS';
    // Filtramos por la fecha en la que ENTRÓ a la empresa (fecha_recepcion)
    $sql = "SELECT 
                orden_venta, 
                ot,
                folio_registro, 
                laboratorio, 
                valor_ov_usd, 
                DATE(fecha_recepcion) as fecha_entrada,
                fecha_transferencia,
                fecha_asignacion_ot,
                fecha_termino_ot,
                fecha_real_cierre_ot
            FROM sabana_operativa
            WHERE DATE(fecha_recepcion) BETWEEN ? AND ?";

    if ($laboratorio !== 'TODOS') {
        $sql .= " AND laboratorio = ?";
    }

    $sql .= " ORDER BY fecha_recepcion ASC, folio_registro";
            
    $stmt = $conn->prepare($sql);
    
    if ($laboratorio !== 'TODOS') {
        $stmt->bind_param("sss", $fecha_inicio, $fecha_fin, $laboratorio);
    } else {
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data_kanban = [];

    while ($row = $result->fetch_assoc()) {
        $fecha = $row['fecha_entrada'] ?: 'SIN_FECHA';
        
        if (!isset($data_kanban[$fecha])) {
            $data_kanban[$fecha] = [
                'fecha_recepcion' => $fecha,
                'registros' => []
            ];
        }

        // Determinar la etapa de vida del equipo evaluando sus fechas de fin a inicio
        $columna_destino = '';
        if (!empty($row['fecha_real_cierre_ot'])) {
            $columna_destino = 'CERRADO';
        } elseif (!empty($row['fecha_termino_ot'])) {
            $columna_destino = 'TERMINADO';
        } elseif (!empty($row['fecha_asignacion_ot'])) {
            $columna_destino = 'LABORATORIO';
        } elseif (!empty($row['fecha_transferencia'])) {
            $columna_destino = 'TRANSFERENCIA';
        } else {
            $columna_destino = 'RECEPCION';
        }

        $data_kanban[$fecha]['registros'][] = [
            'folio'       => $row['folio_registro'],
            'orden_venta' => $row['orden_venta'],
            'ot'          => $row['ot'],
            'laboratorio' => $row['laboratorio'],
            'valor_usd'   => (float)$row['valor_ov_usd'],
            'columna'     => $columna_destino
        ];
    }

    echo json_encode(["status" => "success", "data" => array_values($data_kanban)]);
    exit;
}

if ($accion === 'obtener_laboratorios') {
    $sql = "SELECT DISTINCT laboratorio FROM sabana_operativa WHERE laboratorio IS NOT NULL AND laboratorio != '' ORDER BY laboratorio ASC";
    $result = $conn->query($sql);
    
    $laboratorios = [];
    while ($row = $result->fetch_assoc()) {
        $laboratorios[] = $row['laboratorio'];
    }

    echo json_encode([
        "status" => "success",
        "data"   => $laboratorios
    ]);
    exit;
}

if ($accion === 'obtener_detalle_rezagos') {
    $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
    $laboratorio  = $_POST['laboratorio'] ?? 'TODOS';

    $sql = "SELECT 
                folio_registro, 
                orden_venta, 
                ot, 
                cliente, 
                laboratorio, 
                fecha_recepcion, 
                fecha_transferencia,
                fecha_asignacion_ot,
                fecha_termino_ot,
                fecha_limite_cierre_ot, 
                status, 
                estuvo_cuarentena,
                valor_ov_usd,
                DATEDIFF(NOW(), fecha_recepcion) as dias_transcurridos
            FROM sabana_operativa
            WHERE fecha_real_cierre_ot IS NULL 
              AND fecha_termino_ot IS NULL
              AND (
                  DATE(fecha_recepcion) < ? 
                  OR (fecha_limite_cierre_ot IS NOT NULL AND DATE(fecha_limite_cierre_ot) < ?)
              )";

    if ($laboratorio !== 'TODOS') {
        $sql .= " AND laboratorio = ?";
    }

    $sql .= " ORDER BY fecha_recepcion ASC";

    $stmt = $conn->prepare($sql);

    if ($laboratorio !== 'TODOS') {
        $stmt->bind_param("sss", $fecha_inicio, $fecha_inicio, $laboratorio);
    } else {
        $stmt->bind_param("ss", $fecha_inicio, $fecha_inicio);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $dias = intval($row['dias_transcurridos']);

        // 1. Rezagado (> 3 días) vs Atrasado (<= 3 días)
        if ($dias > 3) {
            $row['texto_tipo'] = 'Rezagado';
            $row['clase_tipo'] = 'bg-danger';
        } else {
            $row['texto_tipo'] = 'Atrasado';
            $row['clase_tipo'] = 'bg-warning text-dark';
        }

        // 2. Etapa Actual independiente
        if (!empty($row['fecha_asignacion_ot'])) {
            $row['etapa_rezago'] = 'En Laboratorio';
            $row['clase_badge'] = 'bg-warning text-dark';
        } elseif (!empty($row['fecha_transferencia'])) {
            $row['etapa_rezago'] = 'En Transferencia';
            $row['clase_badge'] = 'bg-info text-dark';
        } else {
            $row['etapa_rezago'] = 'En Recepción';
            $row['clase_badge'] = 'bg-danger';
        }

        // 3. Bandera independiente para Cuarentena
        $row['es_cuarentena'] = (strtoupper(trim($row['estuvo_cuarentena'] ?? '')) === 'SI');

        $registros[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data"   => $registros
    ]);
    exit;
}

if ($accion === 'rastrear_equipo_re') {
    $folio_re = trim($_POST['folio_re'] ?? '');

    if ($folio_re === '') {
        echo json_encode(["status" => "error", "message" => "Ingresa un folio válido."]);
        exit;
    }

    $sql = "SELECT * FROM sabana_operativa WHERE folio_registro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $folio_re);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "status" => "success",
            "data"   => $row
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No se encontró ningún registro con el folio: " . $folio_re
        ]);
    }
    exit;
}
?>