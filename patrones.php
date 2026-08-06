<?php
// Conexión a Base de Datos
$db_host = 'localhost';
$db_user = 'mess_incidencias';
$db_pass = 'Pipmytrade123';
$db_name = 'mess_rrhh';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener filtro de Área
$area_filtro = isset($_GET['area']) ? trim($_GET['area']) : '';

// 1. Obtención DINÁMICA de las áreas presentes en las Ordenes de Trabajo
$query_areas = "
    SELECT DISTINCT REGEXP_SUBSTR(orden_trabajo, '^[A-Za-z]+') AS area 
    FROM patrones_ot 
    WHERE orden_trabajo REGEXP '^[A-Za-z]+'
    ORDER BY area ASC
";
$lista_areas = $pdo->query($query_areas)->fetchAll(PDO::FETCH_COLUMN);

// 2. Consulta de Análisis basada en los campos del CSV
$where_clause = "";
$params = [];

if (!empty($area_filtro)) {
    $where_clause = " WHERE REGEXP_SUBSTR(orden_trabajo, '^[A-Za-z]+') = :area ";
    $params[':area'] = $area_filtro;
}

$query_analisis = "
    SELECT 
        patron,
        descripcion_patron,
        COUNT(*) AS total_usos,
        COUNT(DISTINCT orden_trabajo) AS ots_unicas
    FROM patrones_ot
    $where_clause
    GROUP BY patron, descripcion_patron
    ORDER BY total_usos DESC, patron ASC
";

$stmt = $pdo->prepare($query_analisis);
$stmt->execute($params);
$patrones_data = $stmt->fetchAll();

// Estadísticas generales de la consulta activa
$total_usos_general = array_sum(array_column($patrones_data, 'total_usos'));
$total_patrones_diferentes = count($patrones_data);
$patron_mas_usado = !empty($patrones_data) ? $patrones_data[0] : null;

// Preparación de datos para la gráfica Top 10
$top_10 = array_slice($patrones_data, 0, 10);
$chart_labels = json_encode(array_column($top_10, 'patron'));
$chart_values = json_encode(array_column($top_10, 'total_usos'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Patrones - OTs</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid my-4 px-4">
    
    <div class="mb-4">
        <h2 class="fw-bold text-primary"><i class="fa-solid fa-chart-pie me-2"></i>Análisis de Uso de Patrones</h2>
        <p class="text-muted">Estadísticas e historial basado en el catálogo de Patrones y Ordenes de Trabajo.</p>
    </div>

    <!-- Filtro dinámico por Área (calculado según la nomenclatura de la OT) -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label for="area" class="form-label fw-bold"><i class="fa-solid fa-filter me-1"></i>Filtrar por Área:</label>
                    <select name="area" id="area" class="form-select" onchange="this.form.submit()">
                        <option value="">-- TODAS LAS ÁREAS --</option>
                        <?php foreach ($lista_areas as $area): ?>
                            <option value="<?= htmlspecialchars($area) ?>" <?= $area_filtro === $area ? 'selected' : '' ?>>
                                Área: <?= htmlspecialchars($area) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($area_filtro)): ?>
                    <div class="col-md-2 align-self-end">
                        <a href="patrones_analisis.php" class="btn btn-outline-secondary w-100">Limpiar Filtro</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tarjetas de Métricas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white shadow-sm p-3 border-0">
                <span class="fs-6 opacity-75">Servicios / Registros</span>
                <h3 class="fw-bold mb-0"><?= number_format($total_usos_general) ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white shadow-sm p-3 border-0">
                <span class="fs-6 opacity-75">Patrones Distintos Usados</span>
                <h3 class="fw-bold mb-0"><?= number_format($total_patrones_diferentes) ?></h3>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card bg-success text-white shadow-sm p-3 border-0">
                <span class="fs-6 opacity-75"><i class="fa-solid fa-star me-1"></i>Patrón Más Usado</span>
                <h4 class="fw-bold mb-0">
                    <?= $patron_mas_usado ? htmlspecialchars($patron_mas_usado['patron']) : 'N/A' ?>
                </h4>
                <small>
                    <?= $patron_mas_usado ? htmlspecialchars($patron_mas_usado['descripcion_patron']) . " — <strong>" . $patron_mas_usado['total_usos'] . " usos en " . $patron_mas_usado['ots_unicas'] . " OTs</strong>" : '' ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Gráfica Top 10 y Tabla -->
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Top 10 Patrones Más Utilizados</div>
                <div class="card-body">
                    <canvas id="chartTopPatrones"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Frecuencia por Patrón</span>
                    <span class="badge bg-secondary"><?= count($patrones_data) ?> patrones</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaPatrones" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Patrón</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Total Usos</th>
                                    <th class="text-center">OTs Distintas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($patrones_data as $row): 
                                ?>
                                <tr>
                                    <td><strong><?= $rank++ ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['patron']) ?></span></td>
                                    <td><?= htmlspecialchars($row['descripcion_patron']) ?></td>
                                    <td class="text-center fw-bold text-primary"><?= $row['total_usos'] ?></td>
                                    <td class="text-center"><?= $row['ots_unicas'] ?></td>
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

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    $('#tablaPatrones').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 10,
        order: [[3, 'desc']]
    });

    const ctx = document.getElementById('chartTopPatrones').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= $chart_labels ?>,
            datasets: [{
                label: 'Cantidad de Usos',
                data: <?= $chart_values ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.75)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });
});
</script>

</body>
</html>