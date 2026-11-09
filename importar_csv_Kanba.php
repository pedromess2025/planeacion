<?php
// importar.php
include 'conn.php';

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        function limpiar_ov($val) {
            $val = trim($val);
            if (empty($val)) return '';
            if (stripos($val, 'MESS-OV-') === false) {
                return 'MESS-OV-' . $val;
            }
            return $val;
        }

        // 1. IMPORTAR VENTAS (VENTAS_...csv) - SOPORTANDO MULTIPARTIDA
        if (isset($_FILES['csv_ventas']) && $_FILES['csv_ventas']['error'] == 0) {
            if (($handle = fopen($_FILES['csv_ventas']['tmp_name'], "r")) !== FALSE) {
                $header = fgetcsv($handle, 0, ",", "\"", "\\");
                // Importante: La llave única compuesta es (salesorder_code, number)
                $stmt = $conn->prepare("INSERT INTO kanban_ov (salesorder_code, salesorder_date, customer_id, total_mxn, status, number, orderedQty) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?)
                                       ON DUPLICATE KEY UPDATE total_mxn = VALUES(total_mxn), status = VALUES(status), orderedQty = VALUES(orderedQty)");
                while (($row = fgetcsv($handle, 0, ",", "\"", "\\")) !== FALSE) {
                    if (count($row) < count($header)) continue;
                    $data = array_combine($header, $row);
                    $ov = limpiar_ov($data['OvOrderCode'] ?? '');
                    $date = !empty($data['created']) ? date('Y-m-d H:i:s', strtotime($data['created'])) : null;
                    $cust = $data['customer_id'] ?? '';
                    $total = $data['totalMXN'] ?? 0;
                    $status = $data['status'] ?? '';
                    $number = $data['number'] ?? 1;
                    $orderedQty = $data['orderedQty'] ?? 1;
                    
                    if (!empty($ov)) {
                        $stmt->bind_param("sssdsid", $ov, $date, $cust, $total, $status, $number, $orderedQty);
                        $stmt->execute();
                    }
                }
                $stmt->close();
                fclose($handle);
            }
        }

        // 2. IMPORTAR REGISTRO DE ENTRADAS (REG-ENTRADAS)
        if (isset($_FILES['csv_re']) && $_FILES['csv_re']['error'] == 0) {
            if (($handle = fopen($_FILES['csv_re']['tmp_name'], "r")) !== FALSE) {
                $header = fgetcsv($handle, 0, ",", "\"", "\\");
                $stmt = $conn->prepare("INSERT INTO kanban_re (salesorder_code, re_code, cliente, area, description, qty, estatus_re, frecepcion) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                                       ON DUPLICATE KEY UPDATE estatus_re = VALUES(estatus_re)");
                while (($row = fgetcsv($handle, 0, ",", "\"", "\\")) !== FALSE) {
                    if (count($row) < count($header)) continue;
                    $data = array_combine($header, $row);
                    $ov = limpiar_ov($data['OV'] ?? '');
                    $re = $data['RE'] ?? '';
                    $cliente = $data['cliente'] ?? '';
                    $area = $data['area'] ?? '';
                    $desc = $data['description'] ?? '';
                    $qty = $data['qty'] ?? 1;
                    $estatus = $data['estatus'] ?? '';
                    $frep = !empty($data['frecepcion']) ? $data['frecepcion'] : null;
                    
                    if (!empty($re)) {
                        $stmt->bind_param("sssssiss", $ov, $re, $cliente, $area, $desc, $qty, $estatus, $frep);
                        $stmt->execute();
                    }
                }
                $stmt->close();
                fclose($handle);
            }
        }

        // 3. OTs: DETALLE-SIN-TIEMPO
        if (isset($_FILES['csv_ot_sintiempo']) && $_FILES['csv_ot_sintiempo']['error'] == 0) {
            procesar_ot_csv($_FILES['csv_ot_sintiempo']['tmp_name'], 'SIN_TIEMPO', $conn);
        }

        // 4. OTs: DETALLE-TIEMPO-LAB
        if (isset($_FILES['csv_ot_lab']) && $_FILES['csv_ot_lab']['error'] == 0) {
            procesar_ot_csv($_FILES['csv_ot_lab']['tmp_name'], 'TIEMPO_LAB', $conn);
        }

        // 5. OTs: DETALLE-TIEMPO-SITIO
        if (isset($_FILES['csv_ot_sitio']) && $_FILES['csv_ot_sitio']['error'] == 0) {
            procesar_ot_csv($_FILES['csv_ot_sitio']['tmp_name'], 'TIEMPO_SITIO', $conn);
        }

        // 6. ACTUALIZAR CERTIFICADOS
        if (isset($_FILES['csv_certificados']) && $_FILES['csv_certificados']['error'] == 0) {
            if (($handle = fopen($_FILES['csv_certificados']['tmp_name'], "r")) !== FALSE) {
                $header = fgetcsv($handle, 0, ",", "\"", "\\");
                $stmt_cert = $conn->prepare("UPDATE kanban_ot SET certificateCode = ?, isSent = ? WHERE workorder_code = ?");
                while (($row = fgetcsv($handle, 0, ",", "\"", "\\")) !== FALSE) {
                    if (count($row) < count($header)) continue;
                    $data = array_combine($header, $row);
                    
                    $ot_code = trim($data['workorder_code'] ?? '');
                    $cert_code = trim($data['certificateCode'] ?? null);
                    $is_sent = trim($data['isSent'] ?? 'NO');
                    
                    if (!empty($ot_code)) {
                        $stmt_cert->bind_param("sss", $cert_code, $is_sent, $ot_code);
                        $stmt_cert->execute();
                    }
                }
                $stmt_cert->close();
                fclose($handle);
            }
        }

        $conn->commit();
        $mensaje = "¡Importación masiva y multipartida completada con éxito!";
        $tipo_alerta = "success";
    } catch (Exception $e) {
        $conn->rollback();
        $mensaje = "Error al procesar: " . $e->getMessage();
        $tipo_alerta = "error";
    }
}

function procesar_ot_csv($filepath, $tipo_ot, $conn) {
    if (($handle = fopen($filepath, "r")) !== FALSE) {
        $header = fgetcsv($handle, 0, ",", "\"", "\\");
        $stmt = $conn->prepare("INSERT INTO kanban_ot (salesorder_code, workorder_code, workOrderStatus, certificateCode, isSent, technician, description, area_name, partida, cant_registros, duration, tipo_ot) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE workOrderStatus = VALUES(workOrderStatus), duration = VALUES(duration), partida = VALUES(partida), cant_registros = VALUES(cant_registros)");
        while (($row = fgetcsv($handle, 0, ",", "\"", "\\")) !== FALSE) {
            if (count($row) < count($header)) continue;
            $data = array_combine($header, $row);
            
            $ov = '';
            if (isset($data['OV'])) $ov = trim($data['OV']);
            elseif (isset($data['salesorder_code'])) $ov = trim($data['salesorder_code']);
            if (stripos($ov, 'MESS-OV-') === false && !empty($ov)) {
                $ov = 'MESS-OV-' . $ov;
            }

            $ot = $data['order_code'] ?? '';
            $status = $data['type'] ?? 'PROCESO';
            $cert = $data['certificateCode'] ?? null;
            $issent = $data['isSent'] ?? 'NO';
            $tech = $data['engineer'] ?? '';
            $desc = $data['descripcion'] ?? '';
            $area = $data['regionEj'] ?? '';
            $partida = $data['partida'] ?? 1;
            $cant_reg = $data['cant_registros'] ?? 1;
            $duration = $data['duration'] ?? 0;
            
            if (!empty($ot)) {
                $stmt->bind_param("ssssssssidds", $ov, $ot, $status, $cert, $issent, $tech, $desc, $area, $partida, $cant_reg, $duration, $tipo_ot);
                $stmt->execute();
            }
        }
        $stmt->close();
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <title>PLANEACIÓN - Importación Masiva</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/planeacion.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Importación de Paquete CSV (Multipartida)</h1>
                        <a href="kanban.php" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver al Kanban</a>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-xl-8 col-lg-10">
                            <div class="card shadow mb-4">
                                <div class="card-body">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3"><label class="fw-bold">1. Ventas CSV:</label><input class="form-control" type="file" name="csv_ventas" accept=".csv"></div>
                                        <div class="mb-3"><label class="fw-bold">2. Entradas CSV:</label><input class="form-control" type="file" name="csv_re" accept=".csv"></div>
                                        <div class="mb-3"><label class="fw-bold">3. OTs Sin Tiempo:</label><input class="form-control" type="file" name="csv_ot_sintiempo" accept=".csv"></div>
                                        <div class="mb-3"><label class="fw-bold">4. OTs Tiempo Lab:</label><input class="form-control" type="file" name="csv_ot_lab" accept=".csv"></div>
                                        <div class="mb-3"><label class="fw-bold">5. OTs Tiempo Sitio:</label><input class="form-control" type="file" name="csv_ot_sitio" accept=".csv"></div>
                                        <div class="mb-4"><label class="fw-bold text-success">6. Actualizar Certificados (OT-DETALLE):</label><input class="form-control border-success" type="file" name="csv_certificados" accept=".csv"></div>
                                        <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-upload"></i> Importar Todo</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>    
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (!empty($mensaje)): ?>
            Swal.fire({ icon: '<?= $tipo_alerta ?>', title: 'Aviso', text: '<?= $mensaje ?>', confirmButtonColor: '#4e73df' });
        <?php endif; ?>
    </script>
</body>
</html>