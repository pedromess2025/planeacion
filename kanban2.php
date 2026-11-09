<?php
// kanban.php
include 'conn.php';

$filtro_ov = isset($_GET['filtro_ov']) ? trim($_GET['filtro_ov']) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <title>PLANEACIÓN - Tablero Ejecutivo por OV y Partidas</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/planeacion.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .row-group-card { background: #fff; border: 1px solid #e3e6f0; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.08); overflow: hidden; }
        .col-ov-block { background: #f8f9fc; border-right: 1px solid #e3e6f0; padding: 20px; display: flex; flex-direction: column; justify-content: flex-start; }
        .col-steps-block { padding: 20px; display: flex; flex-direction: column; gap: 15px; }
        .step-row { display: flex; gap: 15px; align-items: stretch; border-bottom: 1px dashed #e3e6f0; padding-bottom: 15px; }
        .step-row:last-child { border-bottom: none; padding-bottom: 0; }
        
        .partida-badge-col { width: 110px; background: #eaecf4; border-radius: 6px; padding: 10px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border: 1px solid #d1d3e2; }
        
        .sub-box { background: #ffffff; border: 1px solid #d1d3e2; border-radius: 6px; padding: 12px; flex: 1; font-size: 0.85rem; position: relative; border-left: 4px solid #4e73df; }
        .sub-box.re-box { border-left-color: #36b9cc; }
        .sub-box.ot-box { border-left-color: #f6c23e; }
        .sub-box.ot-sent { border-left-color: #1cc88a; background: #fdfefe; }
        .sub-box.empty-box { border-left-color: #e74a3b; background: #fff8f8; color: #e74a3b; }
        
        .progress-global { height: 14px; border-radius: 7px; }
        .progress-partida { height: 4px; width: 100%; margin-top: 6px; }
        .badge-cert { font-size: 0.7rem; }
        
        .partida-resumen-item { background: #fff; border: 1px solid #e3e6f0; border-radius: 6px; padding: 8px 10px; margin-top: 6px; font-size: 0.78rem; }
        .global-box { background: #ffffff; border: 2px solid #1cc88a; border-radius: 8px; padding: 12px; margin-top: 12px; margin-bottom: 12px; box-shadow: 0 0.1rem 0.5rem rgba(0,0,0,0.05); }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>
                
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Tablero Ejecutivo - Seguimiento por OV y Partidas</h1>
                        <a href="importar.php" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-upload fa-sm text-white-50"></i> Importar CSVs</a>
                    </div>

                    <!-- Filtro por OV -->
                    <div class="card shadow mb-4">
                        <div class="card-body py-3">
                            <form method="GET" action="" class="row align-items-center">
                                <div class="col-md-4">
                                    <label for="filtro_ov" class="form-label fw-bold mb-1" style="font-size: 0.85rem;">Filtrar por Orden de Venta (OV):</label>
                                    <select class="form-select select2" id="filtro_ov" name="filtro_ov">
                                        <option value="">-- Todas las Órdenes de Venta --</option>
                                        <?php
                                            $res_lista = $conn->query("SELECT DISTINCT salesorder_code FROM kanban_ov ORDER BY salesorder_date DESC");
                                            while($l = $res_lista->fetch_assoc()) {
                                                $code_opt = trim($l['salesorder_code']);
                                                $selected = ($filtro_ov == $code_opt) ? 'selected' : '';
                                                echo "<option value=\"$code_opt\" $selected>$code_opt</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mt-3 mt-md-0">
                                    <button type="submit" class="btn btn-primary btn-sm me-2"><i class="fas fa-filter"></i> Filtrar</button>
                                    <a href="kanban.php" class="btn btn-secondary btn-sm"><i class="fas fa-sync"></i> Limpiar</a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
                        function formatear_ov($code) {
                            $code = trim($code);
                            if (empty($code)) return 'MESS-OV-S/N';
                            if (stripos($code, 'MESS-OV-') === false) {
                                return 'MESS-OV-' . $code;
                            }
                            return $code;
                        }

                        function obtener_color_ov($str) {
                            $hash = md5($str);
                            return '#' . substr($hash, 0, 6);
                        }

                        $where_sql = "";
                        if (!empty($filtro_ov)) {
                            $clean_filtro = str_replace('MESS-OV-', '', $filtro_ov);
                            $where_sql = " WHERE salesorder_code = '$filtro_ov' OR salesorder_code = '$clean_filtro' ";
                        }

                        $res_ov_codes = $conn->query("SELECT DISTINCT salesorder_code FROM kanban_ov $where_sql ORDER BY salesorder_date DESC LIMIT 30");
                        
                        if ($res_ov_codes && $res_ov_codes->num_rows > 0) {
                            while($row_code = $res_ov_codes->fetch_assoc()) {
                                $ov_raw = $row_code['salesorder_code'];
                                $ov_code = formatear_ov($ov_raw);
                                $color_ov = obtener_color_ov($ov_code);

                                // 1. Obtener TODAS las partidas de la OV
                                $stmt_partidas = $conn->prepare("SELECT * FROM kanban_ov WHERE salesorder_code = ? OR salesorder_code = ? ORDER BY number ASC");
                                $stmt_partidas->bind_param("ss", $ov_raw, $ov_code);
                                $stmt_partidas->execute();
                                $res_p = $stmt_partidas->get_result();
                                $partidas_ov = [];
                                $customer_id = '';
                                $status_ov = '';
                                $date_ov = '';
                                while($p = $res_p->fetch_assoc()) {
                                    $partidas_ov[] = $p;
                                    $customer_id = $p['customer_id'];
                                    $status_ov = $p['status'];
                                    $date_ov = $p['salesorder_date'];
                                }
                                $stmt_partidas->close();

                                // 2. Obtener REs de la OV (el RE no tiene partida, aplica para la OV en general)
                                $stmt_re = $conn->prepare("SELECT * FROM kanban_re WHERE salesorder_code = ? OR salesorder_code = ?");
                                $stmt_re->bind_param("ss", $ov_raw, $ov_code);
                                $stmt_re->execute();
                                $res_re = $stmt_re->get_result();
                                $lista_re = [];
                                while($re = $res_re->fetch_assoc()) { $lista_re[] = $re; }
                                $stmt_re->close();

                                // 3. Obtener OTs de la OV (estas sí tienen campo partida)
                                $stmt_ot = $conn->prepare("SELECT * FROM kanban_ot WHERE salesorder_code = ? OR salesorder_code = ?");
                                $stmt_ot->bind_param("ss", $ov_raw, $ov_code);
                                $stmt_ot->execute();
                                $res_ot = $stmt_ot->get_result();
                                $lista_ot = [];
                                while($ot = $res_ot->fetch_assoc()) { $lista_ot[] = $ot; }
                                $stmt_ot->close();

                                // Procesar partidas y cálculo global
                                $suma_porcentajes = 0;
                                $total_partidas = count($partidas_ov);
                                $desglose_partidas = [];

                                foreach($partidas_ov as $p) {
                                    $num_p = $p['number'] ?? 1;
                                    $cant_req = floatval($p['orderedQty'] ?? 1);
                                    if ($cant_req <= 0) $cant_req = 1;

                                    // REs (como el RE cubre la OV, tomamos las cantidades generales o proporcionales)
                                    $cant_re_p = 0;
                                    foreach($lista_re as $re_item) {
                                        $cant_re_p += floatval($re_item['qty'] ?? 1);
                                    }

                                    // OTs de ESTA partida específica
                                    $ot_match = [];
                                    $cant_ot_sent_p = 0;
                                    foreach($lista_ot as $ot_item) {
                                        if (isset($ot_item['partida']) && intval($ot_item['partida']) == intval($num_p)) {
                                            $ot_match[] = $ot_item;
                                            if (isset($ot_item['isSent']) && $ot_item['isSent'] == 'SI') {
                                                $cant_ot_sent_p += floatval($ot_item['cant_registros'] ?? 1);
                                            }
                                        }
                                    }

                                    // Cálculo de avance por partida (50% RE + 50% OT con Certificado Enviado)
                                    $r_re = min($cant_re_p / $cant_req, 1.0);
                                    $r_ot = min($cant_ot_sent_p / $cant_req, 1.0);
                                    $porc_p = round(($r_re * 50) + ($r_ot * 50));

                                    $suma_porcentajes += $porc_p;
                                    $desglose_partidas[] = [
                                        'number' => $num_p,
                                        'cant_req' => $cant_req,
                                        'ot_items' => $ot_match,
                                        'cant_re' => $cant_re_p,
                                        'cant_ot_sent' => $cant_ot_sent_p,
                                        'porc' => $porc_p
                                    ];
                                }

                                $porcentaje_global = ($total_partidas > 0) ? round($suma_porcentajes / $total_partidas) : 0;
                    ?>

                    <!-- BLOQUE / FILA JERÁRQUICA POR OV -->
                    <div class="row-group-card">
                        <div class="row g-0">
                            
                            <!-- COLUMNA IZQUIERDA: OV Principal (Protagonismo al % Global y Resumen Limpio) -->
                            <div class="col-md-3 col-ov-block" style="border-left: 6px solid <?= $color_ov ?>;">
                                <h4 class="fw-bold mb-1" style="color: <?= $color_ov ?>;"><?= htmlspecialchars($ov_code) ?></h4>
                                <p class="text-muted mb-1" style="font-size: 0.85rem;">Cliente ID: <b><?= htmlspecialchars($customer_id) ?></b></p>
                                <span class="badge bg-secondary mb-2 align-self-start"><?= htmlspecialchars($status_ov) ?></span>
                                
                                <!-- PROTAGONISMO TOTAL AL % GLOBAL -->
                                <div class="global-box">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-bold text-success" style="font-size: 0.85rem;"><i class="fas fa-chart-line"></i> AVANCE GLOBAL</span>
                                        <span class="badge bg-success fs-6"><?= $porcentaje_global ?>%</span>
                                    </div>
                                    <div class="progress progress-global">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $porcentaje_global ?>%;" aria-valuenow="<?= $porcentaje_global ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>

                                <!-- DESGLOSE DISCRETO POR PARTIDA -->
                                <div class="mt-1 w-100">
                                    <span class="fw-bold text-dark" style="font-size: 0.78rem;"><i class="fas fa-layer-group"></i> Partidas en esta OV (<?= $total_partidas ?>):</span>
                                    
                                    <?php foreach($desglose_partidas as $dp): ?>
                                    <div class="partida-resumen-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><b>Partida #<?= htmlspecialchars($dp['number']) ?></b> (Cant: <?= $dp['cant_req'] ?>)</span>
                                            <span class="badge bg-light text-dark border" style="font-size: 0.7rem;"><?= $dp['porc'] ?>%</span>
                                        </div>
                                        <div class="progress progress-partida">
                                            <div class="progress-bar" role="progressbar" style="width: <?= $dp['porc'] ?>%; background-color: <?= $color_ov ?>;" aria-valuenow="<?= $dp['porc'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                                <small class="text-muted mt-3" style="font-size: 0.72rem;">Fecha OV: <?= htmlspecialchars($date_ov) ?></small>
                            </div>

                            <!-- COLUMNA DERECHA: Desglose limpio ordenado por Partida -->
                            <div class="col-md-9 col-steps-block">
                                <?php 
                                    foreach($desglose_partidas as $dp):
                                        $re_actual = $lista_re[0] ?? null; // RE general asociado a la OV
                                        $ot_actual = $dp['ot_items'][0] ?? null; // OT correspondiente a ESTA partida
                                ?>
                                <div class="step-row align-items-center">
                                    
                                    <!-- ETIQUETA DE PARTIDA COMPACTA -->
                                    <div class="partida-badge-col">
                                        <span class="fw-bold text-dark" style="font-size: 0.8rem;">Partida #<?= $dp['number'] ?></span>
                                        <span class="text-muted" style="font-size: 0.7rem;">Req: <?= $dp['cant_req'] ?></span>
                                        <span class="badge bg-primary mt-1" style="font-size: 0.68rem;"><?= $dp['porc'] ?>%</span>
                                    </div>

                                    <!-- BLOQUE RE (Entrada de la OV) -->
                                    <div class="sub-box <?= $re_actual ? 're-box' : 'empty-box' ?>">
                                        <?php if ($re_actual): ?>
                                            <div class="d-flex justify-content-between">
                                                <strong class="text-info">RE: <?= htmlspecialchars($re_actual['re_code']) ?></strong>
                                                <span class="badge bg-warning text-dark" style="font-size: 0.65rem;"><?= htmlspecialchars($re_actual['estatus_re']) ?></span>
                                            </div>
                                            <p class="mb-1 text-truncate" style="font-size: 0.78rem;"><b>Cliente:</b> <?= htmlspecialchars($re_actual['cliente']) ?></p>
                                            <small class="text-muted" style="font-size: 0.7rem;">Área: <?= htmlspecialchars($re_actual['area']) ?></small>
                                        <?php else: ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>Falta Registro de Entrada (RE)</strong>
                                                <span class="badge bg-danger" style="font-size: 0.65rem;">Pendiente</span>
                                            </div>
                                            <small style="font-size: 0.7rem;">Equipo pendiente de recibir.</small>
                                        <?php endif; ?>
                                    </div>

                                    <!-- BLOQUE OT (Orden de Trabajo específica de esta Partida) -->
                                    <div class="sub-box <?= ($ot_actual && $ot_actual['isSent'] == 'SI') ? 'ot-sent' : ($ot_actual ? 'ot-box' : 'empty-box') ?>">
                                        <?php if ($ot_actual): ?>
                                            <div class="d-flex justify-content-between">
                                                <strong>OT: <?= htmlspecialchars($ot_actual['workorder_code']) ?></strong>
                                                <?php if ($ot_actual['isSent'] == 'SI'): ?>
                                                    <span class="badge bg-success badge-cert"><i class="fas fa-check-circle"></i> Cert. Enviado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary badge-cert"><?= htmlspecialchars($ot_actual['workOrderStatus']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-1 text-truncate" style="font-size: 0.78rem;"><b>Técnico:</b> <?= htmlspecialchars($ot_actual['technician']) ?></p>
                                            <?php if (!empty($ot_actual['certificateCode'])): ?>
                                                <small class="text-primary fw-bold" style="font-size: 0.7rem;">Cert: <?= htmlspecialchars($ot_actual['certificateCode']) ?></small>
                                            <?php else: ?>
                                                <small class="text-danger" style="font-size: 0.7rem;">Certificado pendiente</small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>Falta OT para Partida #<?= $dp['number'] ?></strong>
                                                <span class="badge bg-danger" style="font-size: 0.65rem;">Sin OT</span>
                                            </div>
                                            <small style="font-size: 0.7rem;">No hay orden de servicio asignada a esta partida.</small>
                                        <?php endif; ?>
                                    </div>

                                </div>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>
                    <?php 
                            }
                        } else {
                            echo '<div class="alert alert-warning text-center">No se encontraron registros de Órdenes de Venta con el filtro seleccionado.</div>';
                        }
                    ?>

                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>    
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
        });
    </script>
</body>
</html>