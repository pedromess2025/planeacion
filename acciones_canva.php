<?php
header('Content-Type: application/json');
include 'conn.php';
exigeAccesoEspecialJson($conn, 'planeacion', 'verSegEntradas');

$accion = $_POST['accion'] ?? 'cargar_tablero';

if ($accion === 'cargar_tablero') {
    $fecha_pivot = $_POST['fecha_pivot'] ?? date('Y-m-d');
    $laboratorio = $_POST['laboratorio'] ?? 'TODOS';
    $hoy = date('Y-m-d');

    // Si es a FUTURO, solo trae la carga planeada del día sin rezagos (EXCLUYE CANCELADAS)
    if ($fecha_pivot > $hoy) {
        $sql = "SELECT 
                    orden_venta, ot, folio_registro, laboratorio, valor_ov_usd, status_ot,
                    DATE(fecha_recepcion) as fecha_entrada,
                    DATE(fprogramada) as fecha_prog,
                    fecha_transferencia, fecha_asignacion_ot, fecha_termino_ot, fecha_real_cierre_ot
                FROM sabana_operativa
                WHERE (DATE(fecha_recepcion) = ? OR DATE(fprogramada) = ?)
                  AND (status_ot IS NULL OR status_ot != 'Cancelada')";
        
        if ($laboratorio !== 'TODOS') {
            $sql .= " AND laboratorio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $fecha_pivot, $fecha_pivot, $laboratorio);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $fecha_pivot, $fecha_pivot);
        }
    } 
    // Si es HOY o PASADO, trae rezagos excluyendo los Terminados, Cerrados y CANCELADOS
    else {
        $sql = "SELECT 
                    orden_venta, ot, folio_registro, laboratorio, valor_ov_usd, status_ot,
                    DATE(fecha_recepcion) as fecha_entrada,
                    DATE(fprogramada) as fecha_prog,
                    fecha_transferencia, fecha_asignacion_ot, fecha_termino_ot, fecha_real_cierre_ot
                FROM sabana_operativa
                WHERE (
                        (DATE(fecha_recepcion) = ?) 
                     OR (DATE(fecha_recepcion) < ? AND fecha_termino_ot IS NULL AND fecha_real_cierre_ot IS NULL)
                     OR (DATE(fprogramada) = ?)
                     OR (DATE(fprogramada) < ? AND fecha_termino_ot IS NULL AND fecha_real_cierre_ot IS NULL)
                )
                AND (status_ot IS NULL OR status_ot != 'Cancelada')";
        
        if ($laboratorio !== 'TODOS') {
            $sql .= " AND laboratorio = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $fecha_pivot, $fecha_pivot, $fecha_pivot, $fecha_pivot, $laboratorio);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $fecha_pivot, $fecha_pivot, $fecha_pivot, $fecha_pivot);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $registros = [];

    while ($row = $result->fetch_assoc()) {
        $columna_destino = 'RECEPCION';
        $fecha_etapa_actual = $row['fecha_entrada'] ?: 'N/D';
        
        if (!empty($row['fecha_real_cierre_ot'])) {
            $columna_destino = 'CERRADO';
            $fecha_etapa_actual = date('Y-m-d', strtotime($row['fecha_real_cierre_ot']));
        } elseif (!empty($row['fecha_termino_ot'])) {
            $columna_destino = 'TERMINADO';
            $fecha_etapa_actual = date('Y-m-d', strtotime($row['fecha_termino_ot']));
        } elseif (!empty($row['fecha_asignacion_ot'])) {
            $columna_destino = 'LABORATORIO';
            $fecha_etapa_actual = date('Y-m-d', strtotime($row['fecha_asignacion_ot']));
        } elseif (!empty($row['fecha_transferencia'])) {
            $columna_destino = 'TRANSFERENCIA';
            $fecha_etapa_actual = date('Y-m-d', strtotime($row['fecha_transferencia']));
        }

        $fecha_rec = $row['fecha_entrada'] ?: '';
        $fecha_prog = $row['fecha_prog'] ?: '';

        // -- TARJETA REAL --
        $es_del_dia_rec = ($fecha_rec == $fecha_pivot);
        $es_rezagado_rec = ($fecha_rec != '' && $fecha_rec < $fecha_pivot && empty($row['fecha_termino_ot']) && empty($row['fecha_real_cierre_ot']));
        
        if ($es_del_dia_rec || $es_rezagado_rec) {
            $registros[] = [
                'folio'              => $row['folio_registro'],
                'orden_venta'        => $row['orden_venta'],
                'ot'                 => $row['ot'],
                'status_ot'          => $row['status_ot'],
                'laboratorio'        => $row['laboratorio'],
                'valor_usd'          => (float)$row['valor_ov_usd'],
                'columna'            => $columna_destino,
                'es_planeada_card'   => false,
                'fecha_origen'       => $fecha_rec,
                'fecha_etapa_actual' => $fecha_etapa_actual
            ];
        }

        // -- TARJETA PLANEADA --
        $es_del_dia_prog = ($fecha_prog == $fecha_pivot);
        $es_rezagado_prog = ($fecha_prog != '' && $fecha_prog < $fecha_pivot && empty($row['fecha_termino_ot']) && empty($row['fecha_real_cierre_ot']));
        
        if ($es_del_dia_prog || $es_rezagado_prog) {
            if (empty($row['fecha_termino_ot']) && empty($row['fecha_real_cierre_ot'])) {
                $registros[] = [
                    'folio'              => $row['folio_registro'],
                    'orden_venta'        => $row['orden_venta'],
                    'ot'                 => $row['ot'],
                    'status_ot'          => $row['status_ot'],
                    'laboratorio'        => $row['laboratorio'],
                    'valor_usd'          => (float)$row['valor_ov_usd'],
                    'columna'            => $columna_destino,
                    'es_planeada_card'   => true,
                    'fecha_origen'       => $fecha_prog,
                    'fecha_etapa_actual' => $fecha_etapa_actual
                ];
            }
        }
    }

    echo json_encode(["status" => "success", "data" => $registros]);
    exit;
}

if ($accion === 'obtener_laboratorios') {
    $sql = "SELECT DISTINCT laboratorio FROM sabana_operativa WHERE laboratorio IS NOT NULL AND laboratorio != '' ORDER BY laboratorio ASC";
    $result = $conn->query($sql);
    
    $laboratorios = [];
    while ($row = $result->fetch_assoc()) {
        $laboratorios[] = $row['laboratorio'];
    }
    echo json_encode(["status" => "success", "data" => $laboratorios]);
    exit;
}

if ($accion === 'obtener_detalle_rezagos') {
    $fecha_inicio = $_POST['fecha_inicio'] ?? date('Y-m-d');
    $laboratorio  = $_POST['laboratorio'] ?? 'TODOS';

    // EXCLUYE LAS CANCELADAS TAMBIÉN DEL MODAL DE REZAGOS
    $sql = "SELECT 
                folio_registro, orden_venta, ot, cliente, laboratorio, 
                fecha_recepcion, fecha_transferencia, fecha_asignacion_ot,
                fecha_termino_ot, fecha_limite_cierre_ot, status, estuvo_cuarentena, valor_ov_usd,
                DATEDIFF(NOW(), fecha_recepcion) as dias_transcurridos
            FROM sabana_operativa
            WHERE fecha_real_cierre_ot IS NULL 
              AND fecha_termino_ot IS NULL
              AND (
                  DATE(fecha_recepcion) < ? 
                  OR (fecha_limite_cierre_ot IS NOT NULL AND DATE(fecha_limite_cierre_ot) < ?)
              )
              AND (status_ot IS NULL OR status_ot != 'CANCELLED')";

    if ($laboratorio !== 'TODOS') {
        $sql .= " AND laboratorio = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $fecha_inicio, $fecha_inicio, $laboratorio);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fecha_inicio, $fecha_inicio);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $dias = intval($row['dias_transcurridos']);
        if ($dias > 3) {
            $row['texto_tipo'] = 'Rezagado';
            $row['clase_tipo'] = 'bg-danger';
        } else {
            $row['texto_tipo'] = 'Atrasado';
            $row['clase_tipo'] = 'bg-warning text-dark';
        }

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

        $row['es_cuarentena'] = (strtoupper(trim($row['estuvo_cuarentena'] ?? '')) === 'SI');
        $registros[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $registros]);
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
        echo json_encode(["status" => "success", "data" => $row]);
    } else {
        echo json_encode(["status" => "error", "message" => "No se encontró ningún registro con el folio: " . $folio_re]);
    }
    exit;
}
?>