<?php
$mensaje = '';
$tipoAlerta = '';
$logs = []; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csv_archivo']['tmp_name']) || $_FILES['csv_archivo']['error'] !== UPLOAD_ERR_OK) {
        $mensaje = "Error: El archivo no se subió. Verifica el tamaño o selecciona un archivo válido.";
        $tipoAlerta = "danger";
    } else {
        
        // --- CONFIGURACIÓN DE TU BASE DE DATOS ---
        $host    = 'localhost';
        $db      = 'mess_rrhh';
        $user    = 'root';
        $pass    = '';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $logs[] = "✅ Conexión a la base de datos exitosa.";

            function detectarSeparador($archivo) {
                $handle = fopen($archivo, 'r');
                $primeraLinea = fgets($handle);
                fclose($handle);
                return (strpos($primeraLinea, ';') !== false) ? ';' : ',';
            }

            $tmpFile = $_FILES['csv_archivo']['tmp_name'];
            $sep = detectarSeparador($tmpFile);
            $logs[] = "🔍 Separador detectado: '$sep'";

            // Vaciamos la tabla para la carga del día
            $pdo->exec("TRUNCATE TABLE seguimiento_ots");
            $logs[] = "🗑️ La tabla 'seguimiento_ots' fue vaciada con éxito.";

            // Mantenemos la misma estructura de insert para no romper tu base de datos
            $sql = "INSERT INTO seguimiento_ots (
                idOT, OT, start_date, end_date, fecha_planeada_inicio, fecha_planeada_fin, 
                status, state, entity_type, idOV, OV, currency, totalOV, invoice, invoice2, 
                service_id, service, orderedQty, price, discountType, discount, total, 
                TOTAL_MXP, TOTAL_USD, number, area3, tecnicos
            ) VALUES (
                :idOT, :OT, :start_date, :end_date, :fecha_planeada_inicio, :fecha_planeada_fin, 
                :status, :state, :entity_type, :idOV, :OV, :currency, :totalOV, :invoice, :invoice2, 
                :service_id, :service, :orderedQty, :price, :discountType, :discount, :total, 
                :TOTAL_MXP, :TOTAL_USD, :number, :area3, :tecnicos
            )";
            $stmt = $pdo->prepare($sql);

            $insertados = 0;
            $erroresBd = 0;

            if (($handle = fopen($tmpFile, "r")) !== FALSE) {
                // Eliminar el BOM si existe
                $bom = fread($handle, 3);
                if ($bom !== b"\xEF\xBB\xBF") { rewind($handle); }

                $headers = fgetcsv($handle, 10000, $sep);
                // Limpiar espacios y pasar a mayúsculas para búsqueda exacta
                $headersUpper = array_map(function($h){ return strtoupper(trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF")); }, $headers);
                
                $logs[] = "📋 Cabeceras leídas: " . implode(" | ", $headersUpper);
                
                if (array_search('ORDER_CODE', $headersUpper) === false) {
                    $logs[] = "❌ ERROR: No se encontró la columna 'ORDER_CODE' en el archivo.";
                }

                while (($row = fgetcsv($handle, 10000, $sep)) !== FALSE) {
                    
                    // Función para obtener la columna sin importar posición
                    $getCol = function($nombreColumna) use ($headersUpper, $row) {
                        $idx = array_search(strtoupper($nombreColumna), $headersUpper);
                        return ($idx !== false && isset($row[$idx])) ? trim((string)$row[$idx]) : '';
                    };

                    $otCode = $getCol('ORDER_CODE'); // En el nuevo archivo se llama order_code
                    if (empty($otCode)) continue; 

                    // Limpiador de fechas adaptado al formato DD/MM/YYYY HH:mm
                    $cleanDate = function($val) {
                        $val = trim((string)$val);
                        if (empty($val) || $val === "'-" || $val === "-") return null;
                        
                        // Intentar parsear formato DD/MM/YYYY HH:mm
                        $d = DateTime::createFromFormat('d/m/Y H:i', $val);
                        if ($d !== false) {
                            return $d->format('Y-m-d H:i:s');
                        }
                        
                        // Fallback si viene en otro formato
                        $timestamp = strtotime(str_replace('/', '-', $val));
                        return ($timestamp === false || $timestamp <= 0) ? null : date('Y-m-d H:i:s', $timestamp);
                    };

                    // Aquí está el cambio: mandamos '' o 0 en lugar de null a los campos que omitimos
                    $params = [
                        ':idOT'                  => (int)$getCol('SERVICE_ORDER_ID'), 
                        ':OT'                    => $otCode,
                        ':start_date'            => $cleanDate($getCol('START_DATE')),
                        ':end_date'              => $cleanDate($getCol('END_DATE')),
                        ':fecha_planeada_inicio' => null, 
                        ':fecha_planeada_fin'    => null, // Dejé null para fechas, si tu bd pide NOT NULL, ponle '0000-00-00 00:00:00'
                        ':status'                => '', // Cambio crítico: vacío en lugar de null
                        ':state'                 => '', 
                        ':entity_type'           => '', 
                        ':idOV'                  => 0,  
                        ':OV'                    => $getCol('OV'),
                        ':currency'              => '', 
                        ':totalOV'               => 0,  
                        ':invoice'               => '', 
                        ':invoice2'              => 0,  // Normalmente es float
                        ':service_id'            => 0,  
                        ':service'               => $getCol('TYPE'), 
                        ':orderedQty'            => 0,  
                        ':price'                 => (float)str_replace(',', '', $getCol('PRECIO')),
                        ':discountType'          => '', 
                        ':discount'              => 0,  
                        ':total'                 => 0,  
                        ':TOTAL_MXP'             => 0,  
                        ':TOTAL_USD'             => 0,  
                        ':number'                => 0,  
                        ':area3'                 => $getCol('REGION'), 
                        ':tecnicos'              => $getCol('ENGINEER') 
                    ];

                    try {
                        $stmt->execute($params);
                        $insertados++;
                    } catch (PDOException $e) {
                        $erroresBd++;
                        if ($erroresBd <= 3) { 
                            $logs[] = "❌ Error en OT $otCode: " . $e->getMessage();
                        }
                    }
                }
                fclose($handle);
                $logs[] = "✅ Inserción finalizada. Éxitos: $insertados | Errores: $erroresBd";
            }

            if ($insertados > 0) {
                $mensaje = "¡Éxito! Se limpió la base de datos y se insertaron $insertados registros nuevos.";
                $tipoAlerta = "success";
            } else {
                $mensaje = "Proceso terminado. Se vació la tabla pero se insertaron 0 registros.";
                $tipoAlerta = "warning";
            }

        } catch (PDOException $e) {
            $mensaje = "Error de base de datos.";
            $tipoAlerta = "danger";
            $logs[] = "❌ ERROR FATAL: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronización de Tiempos y OT's</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0">Sincronización de Técnicos (DetalleTiemposOTS)</h4>
        </div>
        <div class="card-body">
            
            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $tipoAlerta ?> text-center">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <div class="alert alert-info py-2 text-center" style="font-size: 0.9rem;">
                <strong>Aviso:</strong> Al cargar este archivo, la información de días anteriores será reemplazada por la actualización actual.
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label fw-bold">Archivo CSV (DetalleTiempoOTS)</label>
                    <input type="file" class="form-control" name="csv_archivo" accept=".csv" required>
                    <small class="text-muted">Asegúrate de subir el archivo que contiene la columna <code>engineer</code>.</small>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">Procesar e Insertar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- CONSOLA DE DIAGNÓSTICO -->
    <?php if (!empty($logs)): ?>
    <div class="card shadow-sm mx-auto mt-4" style="max-width: 800px;">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0">🔍 Consola de Diagnóstico</h6>
        </div>
        <div class="card-body bg-black text-light p-3" style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.9rem;">
            <?php foreach ($logs as $log): ?>
                <div><?= htmlspecialchars($log) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

</body>
</html>