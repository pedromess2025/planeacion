<?php
// Configuración de base de datos
$host    = 'localhost';
$db      = 'mess_rrhh';
$user    = 'mess_incidencias';
$pass    = 'Pipmytrade123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 1. Manejo de Fechas (Semanas: Lunes a Domingo)
$fecha_get = isset($_GET['semana']) ? $_GET['semana'] : date('Y-m-d');
$filtro_ingeniero = isset($_GET['ingeniero']) ? trim($_GET['ingeniero']) : '';

// Forzar a que la fecha de inicio siempre sea el Lunes de esa semana
$dia_semana = date('N', strtotime($fecha_get)); // 1 (Lunes) a 7 (Domingo)
if ($dia_semana == 1) {
    $fecha_inicio = date('Y-m-d', strtotime($fecha_get));
} else {
    $fecha_inicio = date('Y-m-d', strtotime($fecha_get . ' - ' . ($dia_semana - 1) . ' days'));
}
$fecha_fin = date('Y-m-d', strtotime($fecha_inicio . ' + 6 days'));

$fecha_prev = date('Y-m-d', strtotime($fecha_inicio . ' - 1 week'));
$fecha_next = date('Y-m-d', strtotime($fecha_inicio . ' + 1 week'));

$texto_semana = "Del " . date('d/m/Y', strtotime($fecha_inicio)) . " al " . date('d/m/Y', strtotime($fecha_fin));

// 2. Obtener lista de ingenieros para el Select2
$sql_lista_ing = "SELECT DISTINCT tecnicos FROM seguimiento_ots WHERE tecnicos IS NOT NULL AND tecnicos != '' ORDER BY tecnicos";
$lista_ingenieros = $pdo->query($sql_lista_ing)->fetchAll(PDO::FETCH_COLUMN);

// 3. Consulta Principal: Estrictamente acotada a la semana consultada
$where_detalles = "WHERE tecnicos IS NOT NULL AND tecnicos != '' AND ((DATE(end_date) BETWEEN :inicio AND :fin) OR (DATE(start_date) BETWEEN :inicio AND :fin))";
$params_detalles = [':inicio' => $fecha_inicio, ':fin' => $fecha_fin];

if ($filtro_ingeniero !== '') {
    $where_detalles .= " AND tecnicos = :ingeniero";
    $params_detalles[':ingeniero'] = $filtro_ingeniero;
}

$sql_detalles = "
    SELECT 
        tecnicos AS ingeniero,
        OT,
        start_date,
        end_date,
        CASE 
            WHEN DATE(end_date) BETWEEN :inicio AND :fin THEN 'Terminada'
            ELSE 'En Proceso'
        END as estado_calculado
    FROM seguimiento_ots
    $where_detalles
    ORDER BY ingeniero, estado_calculado DESC, start_date ASC
";
$stmt_det = $pdo->prepare($sql_detalles);
$stmt_det->execute($params_detalles);
$filas_detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

// Agrupar los datos en PHP
$datos_ingenieros = [];
$total_terminadas_sem = 0;
$total_proceso_sem = 0;

foreach ($filas_detalles as $row) {
    $ing = $row['ingeniero'];
    if (!isset($datos_ingenieros[$ing])) {
        $datos_ingenieros[$ing] = ['terminadas' => 0, 'en_proceso' => 0, 'detalles' => []];
    }
    
    if ($row['estado_calculado'] === 'Terminada') {
        $datos_ingenieros[$ing]['terminadas']++;
        $total_terminadas_sem++;
    } else {
        $datos_ingenieros[$ing]['en_proceso']++;
        $total_proceso_sem++;
    }
    $datos_ingenieros[$ing]['detalles'][] = $row;
}

$labels_ing = [];
$data_terminadas = [];
$data_proceso = [];

foreach ($datos_ingenieros as $ing => $datos) {
    $labels_ing[] = $ing;
    $data_terminadas[] = $datos['terminadas'];
    $data_proceso[] = $datos['en_proceso'];
}

$total_asignadas_sem = $total_terminadas_sem + $total_proceso_sem;
$cumplimiento_sem = ($total_asignadas_sem > 0) ? round(($total_terminadas_sem / $total_asignadas_sem) * 100) : 0;

// 4. Consulta: Resumen diario de los 7 días de la semana (Sintaxis corregida y segura)
$params_semana = [':inicio' => $fecha_inicio];
$filtro_ing_sql = "";

if ($filtro_ingeniero !== '') {
    $filtro_ing_sql = " AND t.tecnicos = :ingeniero";
    $params_semana[':ingeniero'] = $filtro_ingeniero;
}

$sql_semana_dias = "
    SELECT 
        fechas.fecha,
        COALESCE(SUM(CASE WHEN DATE(t.end_date) = fechas.fecha THEN 1 ELSE 0 END), 0) AS terminadas,
        COALESCE(SUM(CASE WHEN DATE(t.start_date) = fechas.fecha THEN 1 ELSE 0 END), 0) AS en_proceso
    FROM (
        SELECT :inicio AS fecha UNION ALL
        SELECT DATE_ADD(:inicio, INTERVAL 1 DAY) UNION ALL
        SELECT DATE_ADD(:inicio, INTERVAL 2 DAY) UNION ALL
        SELECT DATE_ADD(:inicio, INTERVAL 3 DAY) UNION ALL
        SELECT DATE_ADD(:inicio, INTERVAL 4 DAY) UNION ALL
        SELECT DATE_ADD(:inicio, INTERVAL 5 DAY) UNION ALL
        SELECT DATE_ADD(:inicio, INTERVAL 6 DAY)
    ) AS fechas
    LEFT JOIN seguimiento_ots t ON (DATE(t.start_date) = fechas.fecha OR DATE(t.end_date) = fechas.fecha) $filtro_ing_sql
    GROUP BY fechas.fecha
    ORDER BY fechas.fecha
";
$stmt_sem = $pdo->prepare($sql_semana_dias);
$stmt_sem->execute($params_semana);
$datos_semana = $stmt_sem->fetchAll(PDO::FETCH_ASSOC);

$labels_dias = [];
$data_dias_terminadas = [];
$dias_nombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

foreach ($datos_semana as $dia) {
    $num_dia = date('w', strtotime($dia['fecha']));
    $labels_dias[] = $dias_nombres[$num_dia] . ' ' . date('d/m', strtotime($dia['fecha']));
    $data_dias_terminadas[] = $dia['terminadas'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>PLANEACIÓN - Seguimiento OTs</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="css/planeacion.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/responsive/3.0.0/css/responsive.dataTables.min.css" rel="stylesheet" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .kpi-box { text-align: center; padding: 15px; border-radius: 8px; color: white; }
        .bg-proceso { background-color: #64b5f6; }      /* Azul más claro */
        .bg-terminada { background-color: #2ecc71; }     /* Verde */
        .bg-cumplimiento { background-color: #1b4f72; }  /* Azul marino */
        
        .row-hover:hover { background-color: #f8f9fa; }
        .icon-sign { font-size: 0.8rem; vertical-align: middle; }
    </style>
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php include 'encabezado.php'; ?>
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Content Row -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4">
                                <!-- Card Body -->
                                <div class="card-body">
                                    
                                    <!-- NAVEGACIÓN Y FILTROS -->
                                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                                        <div>
                                            <h4 class="mb-0 text-secondary fw-bold">Seguimiento Semanal de OTs</h4>
                                            <span class="badge bg-primary fs-6 mt-1"><?= $texto_semana ?></span>
                                        </div>

                                        <!-- Formulario de Filtro -->
                                        <form method="GET" class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="semana" value="<?= $fecha_inicio ?>">
                                            <label class="fw-bold mb-0 text-nowrap">Filtrar Ingeniero:</label>
                                            <select name="ingeniero" class="form-select select2" style="min-width: 250px;">
                                                <option value="">-- Todos los ingenieros --</option>
                                                <?php foreach($lista_ingenieros as $ing_opt): ?>
                                                    <option value="<?= htmlspecialchars($ing_opt) ?>" <?= ($filtro_ingeniero === $ing_opt) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($ing_opt) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-secondary text-nowrap">Filtrar</button>
                                        </form>

                                        <!-- Botones de semana -->
                                        <div class="btn-group" role="group">
                                            <a href="?semana=<?= $fecha_prev ?>&ingeniero=<?= urlencode($filtro_ingeniero) ?>" class="btn btn-outline-primary">« Anterior</a>
                                            <a href="?semana=<?= $fecha_next ?>&ingeniero=<?= urlencode($filtro_ingeniero) ?>" class="btn btn-outline-primary">Siguiente »</a>
                                            <a href="?semana=<?= date('Y-m-d') ?>&ingeniero=<?= urlencode($filtro_ingeniero) ?>" class="btn btn-outline-secondary ms-2 rounded">Hoy</a>
                                        </div>
                                    </div>

                                    <!-- KPIs -->
                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <div class="kpi-box bg-proceso shadow-sm">
                                                <h5 class="mb-0">OT en Proceso</h5>
                                                <h2 class="fw-bold mb-0"><?= $total_proceso_sem ?></h2>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="kpi-box bg-terminada shadow-sm">
                                                <h5 class="mb-0">OT Terminadas</h5>
                                                <h2 class="fw-bold mb-0"><?= $total_terminadas_sem ?></h2>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="kpi-box bg-cumplimiento shadow-sm">
                                                <h5 class="mb-0">% Cumplimiento</h5>
                                                <h2 class="fw-bold mb-0"><?= $cumplimiento_sem ?>%</h2>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- SECCIÓN IZQUIERDA: INGENIEROS -->
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light fw-bold text-primary">Carga de trabajo por ingeniero</div>
                                                <div class="card-body p-0">
                                                    <div class="p-3" style="height: 250px; width: 100%;">
                                                        <canvas id="chartIngenieros"></canvas>
                                                    </div>
                                                    <hr class="m-0">
                                                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                                        <table class="table table-sm text-center mb-0 border-top">
                                                            <thead class="table-light sticky-top">
                                                                <tr>
                                                                    <th class="text-start ps-3">Ingeniero</th>
                                                                    <th>En Proceso</th>
                                                                    <th>Terminada</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($datos_ingenieros as $ing => $datos): 
                                                                    $id_collapse = md5($ing); 
                                                                ?>
                                                                <tr class="row-hover border-bottom toggle-collapse" data-target="#col-<?= $id_collapse ?>" style="cursor: pointer;">
                                                                    <td class="text-start ps-3 fw-bold text-secondary">
                                                                        <span class="text-primary me-1 icon-sign">➕</span> <?= htmlspecialchars($ing) ?>
                                                                    </td>
                                                                    <td><?= $datos['en_proceso'] > 0 ? $datos['en_proceso'] : '' ?></td>
                                                                    <td><?= $datos['terminadas'] > 0 ? $datos['terminadas'] : '' ?></td>
                                                                    <td class="fw-bold text-dark"><?= $datos['en_proceso'] + $datos['terminadas'] ?></td>
                                                                </tr>
                                                                
                                                                <tr class="collapse bg-white" id="col-<?= $id_collapse ?>">
                                                                    <td colspan="4" class="p-0 border-0">
                                                                        <div class="px-3 py-2 bg-light">
                                                                            <table class="table table-sm table-bordered bg-white text-center mb-0" style="font-size: 0.75rem;">
                                                                                <thead class="table-secondary text-muted">
                                                                                    <tr>
                                                                                        <th>OT</th>
                                                                                        <th>Estado</th>
                                                                                        <th>Fecha Inicio</th>
                                                                                        <th>Fecha Fin</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php foreach($datos['detalles'] as $det): ?>
                                                                                    <tr>
                                                                                        <td class="fw-bold"><?= htmlspecialchars($det['OT']) ?></td>
                                                                                        <td>
                                                                                            <?php if($det['estado_calculado'] == 'Terminada'): ?>
                                                                                                <span class="badge bg-success">Terminada</span>
                                                                                            <?php else: ?>
                                                                                                <span class="badge" style="background-color: #64b5f6;">En Proceso</span>
                                                                                            <?php endif; ?>
                                                                                        </td>
                                                                                        <td><?= $det['start_date'] ? date('d/m/Y H:i', strtotime($det['start_date'])) : '-' ?></td>
                                                                                        <td><?= $det['end_date'] ? date('d/m/Y H:i', strtotime($det['end_date'])) : '-' ?></td>
                                                                                    </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>

                                                                <?php if(empty($datos_ingenieros)): ?>
                                                                <tr><td colspan="4" class="py-3">No hay datos en esta semana.</td></tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                            <tfoot class="table-light fw-bold border-top">
                                                                <tr>
                                                                    <td class="text-start ps-3">Total general</td>
                                                                    <td><?= $total_proceso_sem ?></td>
                                                                    <td><?= $total_terminadas_sem ?></td>
                                                                    <td><?= $total_asignadas_sem ?></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- SECCIÓN DERECHA: DESGLOSE DIARIO -->
                                        <div class="col-lg-6 mb-4">
                                            <div class="card border">
                                                <div class="card-header bg-light fw-bold text-primary">Desglose diario (Lunes a Domingo)</div>
                                                <div class="card-body">
                                                    <div style="height: 250px; width: 100%;">
                                                        <canvas id="chartDias"></canvas>
                                                    </div>
                                                    <hr>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped text-center border">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Día</th>
                                                                    <th>Asignadas</th>
                                                                    <th>Terminadas</th>
                                                                    <th>% Cumplimiento</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($datos_semana as $idx => $dia): 
                                                                    $asignadas_dia = $dia['en_proceso'] + $dia['terminadas'];
                                                                    $porcentaje_dia = ($asignadas_dia > 0) ? round(($dia['terminadas'] / $asignadas_dia) * 100) : 0;
                                                                    $hoy = date('Y-m-d');
                                                                ?>
                                                                <tr <?= ($dia['fecha'] == $hoy) ? 'class="table-warning"' : '' ?>>
                                                                    <td class="fw-bold"><?= $labels_dias[$idx] ?></td>
                                                                    <td><?= $asignadas_dia ?></td>
                                                                    <td><?= $dia['terminadas'] ?></td>
                                                                    <td><?= $porcentaje_dia ?>%</td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->            
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>    
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>    
    <script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="funciones_incidencias.js" defer="defer"></script>

    <!-- Inicialización de Gráficos, Select2 y Control del Acordeón -->
    <script type="text/javascript">
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                placeholder: "Selecciona un ingeniero",
                allowClear: true
            });

            // Control manual del acordeón
            $(document).on('click', '.toggle-collapse', function() {
                const targetId = $(this).data('target');
                const $targetElement = $(targetId);
                const $icon = $(this).find('.icon-sign');

                const bsCollapse = bootstrap.Collapse.getOrCreateInstance($targetElement[0], { toggle: false });
                
                if ($targetElement.hasClass('show')) {
                    bsCollapse.hide();
                    $icon.text('➕');
                } else {
                    bsCollapse.show();
                    $icon.text('➖');
                }
            });

            // Gráfica: Carga por Ingeniero
            const ctxIng = document.getElementById('chartIngenieros').getContext('2d');
            new Chart(ctxIng, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels_ing) ?>,
                    datasets: [
                        {
                            label: 'En Proceso',
                            data: <?= json_encode($data_proceso) ?>,
                            backgroundColor: '#64b5f6'
                        },
                        {
                            label: 'Terminada',
                            data: <?= json_encode($data_terminadas) ?>,
                            backgroundColor: '#2ecc71'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            // Gráfica: OT Terminadas por Día
            const ctxDias = document.getElementById('chartDias').getContext('2d');
            new Chart(ctxDias, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels_dias) ?>,
                    datasets: [{
                        label: 'Terminadas',
                        data: <?= json_encode($data_dias_terminadas) ?>,
                        backgroundColor: '#1b4f72'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { display: false } }
                }
            });
        });
    </script>
</body>
</html>