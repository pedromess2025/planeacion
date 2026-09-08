<?php
// Conexión a la BD
include 'conn.php';

exigeAccesoEspecialJson($conn, 'planeacion', 'verCargaArchivos');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';
$dir_temp = 'uploads/';
if (!is_dir($dir_temp)) mkdir($dir_temp, 0777, true);

function calcularDias($fecha_inicio, $fecha_fin) {
    if (empty($fecha_inicio) || empty($fecha_fin) || $fecha_inicio == "'-" || $fecha_fin == "'-" || $fecha_inicio == 'nan' || $fecha_fin == 'nan') return null;
    $ts_inicio = strtotime($fecha_inicio);
    $ts_fin = strtotime($fecha_fin);
    if ($ts_inicio === false || $ts_fin === false) return null;
    return ($ts_fin - $ts_inicio) / 86400; 
}

// Función robusta para limpiar y formatear fechas de forma segura
function limpiarFechaSegura($valor) {
    $valor = trim($valor ?? '');
    
    if ($valor === '' || $valor === '-' || $valor === '0000-00-00 00:00:00' || strtolower($valor) === 'null') {
        return null;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
        return $valor;
    }
    if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})(?:\s+(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?)?/', $valor, $matches)) {
        $dia  = $matches[1];
        $mes  = $matches[2];
        $anio = $matches[3];
        $hora = $matches[4] ?? '00';
        $min  = $matches[5] ?? '00';
        $seg  = $matches[6] ?? '00';
        
        if (checkdate((int)$mes, (int)$dia, (int)$anio)) {
            return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $anio, $mes, $dia, $hora, $min, $seg);
        }
    }
    $timestamp = strtotime($valor);
    if ($timestamp !== false && $timestamp > 0) {
        return date('Y-m-d H:i:s', $timestamp);
    }
    return null;
}

// ==========================================
// PASO 1: PREPARAR
// ==========================================
if ($accion === 'preparar') {
    if (!isset($_FILES['archivo_reot']) || !isset($_FILES['archivo_info']) || !isset($_FILES['archivo_forecast'])) {
        echo json_encode(["status" => "error", "message" => "Faltan archivos por subir (REOT, INFO o Forecast)."]);
        exit;
    }

    $ruta_reot = $dir_temp . 'temp_reot_sabana.csv';
    move_uploaded_file($_FILES['archivo_reot']['tmp_name'], $ruta_reot);

    $conn->query("TRUNCATE TABLE sabana_operativa");

    // 1. Cargar caché del archivo financiero (INFOOTOVFACT) usando la OV como llave
    $info_cache = [];
    if (($handle = fopen($_FILES['archivo_info']['tmp_name'], "r")) !== FALSE) {
        $headers = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== FALSE) {
            if(is_array($row) && isset($row[0])) {
                $total_headers = count($headers);
                $row = array_pad(array_slice($row, 0, $total_headers), $total_headers, '');
                $row_data = array_combine($headers, $row);
                
                $ov = trim($row_data['OV'] ?? '');
                if ($ov !== '') {
                    $info_cache[$ov] = [
                        'valor_usd'      => $row_data['TOTAL_USD'] ?? null,
                        'factura'        => $row_data['invoice'] ?? 'Sin registro',
                        'estatus_ov'     => $row_data['estatusOV'] ?? 'Sin registro',
                        'cliente_real'   => $row_data['cliente'] ?? 'Sin registro',
                        'region_cliente' => $row_data['regionCliente'] ?? 'Sin registro'
                    ];
                }
            }
        }
        fclose($handle);
    }
    file_put_contents($dir_temp . 'info_cache.json', json_encode($info_cache));

    // 2. Cargar caché del nuevo archivo Forecast (FactForecast) usando la OT (workorder_code) como llave
    $forecast_cache = [];
    if (($handle = fopen($_FILES['archivo_forecast']['tmp_name'], "r")) !== FALSE) {
        $headers = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== FALSE) {
            if(is_array($row) && isset($row[0])) {
                $total_headers = count($headers);
                $row = array_pad(array_slice($row, 0, $total_headers), $total_headers, '');
                $row_data = array_combine($headers, $row);
                
                $workorder_code = trim($row_data['workorder_code'] ?? '');
                if ($workorder_code !== '') {
                    $forecast_cache[$workorder_code] = [
                        'status_ot' => $row_data['workOrderStatus'] ?? 'Sin registro'
                    ];
                }
            }
        }
        fclose($handle);
    }
    file_put_contents($dir_temp . 'forecast_cache.json', json_encode($forecast_cache));

    $lineas_reot = 0;
    if (($handle = fopen($ruta_reot, "r")) !== FALSE) {
        while (fgets($handle) !== false) { $lineas_reot++; }
        fclose($handle);
    }

    echo json_encode(["status" => "success", "total_lineas" => $lineas_reot - 1]);
    exit;
}

// ==========================================
// PASO 2: PROCESAR EN LOTES
// ==========================================
if ($accion === 'procesar') {
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 1; 
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 500;
    $ruta_reot = $dir_temp . 'temp_reot_sabana.csv';

    $info_cache     = json_decode(file_get_contents($dir_temp . 'info_cache.json'), true);
    $forecast_cache = json_decode(file_get_contents($dir_temp . 'forecast_cache.json'), true);

    $sql = "INSERT INTO sabana_operativa (
        orden_venta, ot, valor_ov_usd, factura, status_ov, vendedor, cliente, region_cliente, 
        folio_registro, status, status_ot, laboratorio, estuvo_cuarentena, 
        fecha_recepcion, fprogramada, fecha_transferencia, dias_rece_transferencia, 
        fecha_asignacion_ot, dias_tran_ot, fecha_termino_ot, termino_ot_cierre_ot, 
        fecha_limite_cierre_ot, fecha_real_cierre_ot, tranf_cierre_ot, dias_retraso_cierre_ot
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    $file = new SplFileObject($ruta_reot);
    $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
    $file->seek(0);
    $headers = $file->current();

    $procesados = 0;
    $file->seek($offset);

    while (!$file->eof() && $procesados < $limit) {
        $row = $file->current();
        
        if (is_array($row) && isset($row[0]) && trim($row[0]) !== '') {
            $total_headers = count($headers);
            $row = array_pad(array_slice($row, 0, $total_headers), $total_headers, '');
            $row_data = array_combine($headers, $row);

            $folio = trim($row_data['RE'] ?? '');
            $ot    = !empty($row_data['ot']) ? trim($row_data['ot']) : 'Sin registro';

            // Control anti-duplicados (Llave compuesta RE + OT)
            static $registros_unicos = [];
            $huella = $folio . '_' . $ot;
            if (isset($registros_unicos[$huella])) {
                $procesados++;
                $file->next();
                continue; 
            }
            $registros_unicos[$huella] = true;

            $ov = trim($row_data['OV'] ?? '');

            // Datos financieros del INFOOTOVFACT
            $valor_usd      = $info_cache[$ov]['valor_usd'] ?? null;
            $factura        = $info_cache[$ov]['invoice'] ?? $info_cache[$ov]['factura'] ?? 'Sin registro';
            $status_ov      = $info_cache[$ov]['estatus_ov'] ?? 'Sin registro';
            $cliente        = $info_cache[$ov]['cliente_real'] ?? 'Sin registro';
            $region_cliente = $info_cache[$ov]['region_cliente'] ?? 'Sin registro';

            // Estatus real de la OT obtenido del nuevo FactForecast usando la OT como llave
            $status_ot      = $forecast_cache[$ot]['status_ot'] ?? 'Sin registro';

            $vendedor = $row_data['asesor'] ?? '';
            $status = $row_data['estatus'] ?? '';
            $laboratorio = $row_data['area'] ?? '';
            $cuarentena = (isset($row_data['cuarentena']) && $row_data['cuarentena'] == '1') ? 'Sí' : 'No';

            $f_rec = limpiarFechaSegura($row_data['frecepcion'] ?? '');
            $f_prog = limpiarFechaSegura($row_data['fprogramada'] ?? '');
            $f_tra = limpiarFechaSegura($row_data['ftranferencia'] ?? '');
            $f_asi = limpiarFechaSegura($row_data['fasignacionot'] ?? '');
            $f_ter = limpiarFechaSegura($row_data['fterminoot'] ?? '');
            $f_lim = limpiarFechaSegura($row_data['fprogramada_limite'] ?? ''); 
            $f_cie = limpiarFechaSegura($row_data['fcierre'] ?? '');

            $d_rec_tra = calcularDias($f_rec, $f_tra);
            $d_tra_ot  = calcularDias($f_tra, $f_asi);
            $d_ter_cie = calcularDias($f_ter, $f_cie);
            $d_tra_cie = calcularDias($f_tra, $f_cie);
            $d_ret_cie = calcularDias($f_lim, $f_cie);

            $stmt->bind_param("ssdsssssssssssssdsdsdssdd",
                $ov, $ot, $valor_usd, $factura, $status_ov, $vendedor, $cliente, $region_cliente,
                $folio, $status, $status_ot, $laboratorio, $cuarentena,
                $f_rec, $f_prog, $f_tra, $d_rec_tra,
                $f_asi, $d_tra_ot, $f_ter, $d_ter_cie,
                $f_lim, $f_cie, $d_tra_cie, $d_ret_cie
            );
            $stmt->execute();
        }
        $procesados++;
        $file->next();
    }

    echo json_encode(["status" => "success", "procesados" => $procesados]);
    exit;
}
// ==========================================
// PASO 3: LIMPIEZA
// ==========================================
if ($accion === 'finalizar') {
    if (file_exists($dir_temp . 'temp_reot_sabana.csv')) unlink($dir_temp . 'temp_reot_sabana.csv');
    if (file_exists($dir_temp . 'info_cache.json')) unlink($dir_temp . 'info_cache.json');
    if (file_exists($dir_temp . 'forecast_cache.json')) unlink($dir_temp . 'forecast_cache.json');
    echo json_encode(["status" => "success"]);
    exit;
}
?>