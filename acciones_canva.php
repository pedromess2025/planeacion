<?php
header('Content-Type: application/json');

include 'conn.php';  

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
?>