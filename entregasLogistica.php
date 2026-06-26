<?php
    session_start();
    include 'conn.php';
    include 'config_entregas.php';

    // Guard de acceso: requiere sesión y departamento de Logística (mismo patrón que calendarioVentas.php)
    if (!isset($_COOKIE['noEmpleado']) || $_COOKIE['noEmpleado'] === '' || $_COOKIE['noEmpleado'] === null) {
        echo '<script>window.location.assign("index")</script>';
    }
    if (!isset($_COOKIE['departamento']) || (string)$_COOKIE['departamento'] !== DEPTO_LOGISTICA) {
        echo '<script>window.location.assign("seguimiento_actividades")</script>';
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Entregas / Enlaces</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/planeacion.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/responsive/3.0.0/css/responsive.dataTables.min.css" rel="stylesheet" />
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <p class="fs-4"><b><i class="fas fa-truck"></i> ENTREGAS / ENLACES</b></p>
                                        </div>
                                        <div class="col-sm-4 text-end">
                                            <button class="btn btn-primary" onclick="nuevoEnlace()"><i class="fas fa-plus"></i> Nuevo enlace</button>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label for="filtroEstatusEnlace" class="mr-2">Filtrar por estatus:</label>
                                            <select id="filtroEstatusEnlace" class="form-select" onchange="cargarEnlaces()">
                                                <option value="">Todos</option>
                                                <?php foreach (ESTATUS_ENLACES as $e): ?>
                                                    <option value="<?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <table id="TEnlaces" class="table table-hover" style="width:100%">
                                                <thead class="table-primary">
                                                    <tr>
                                                        <th>Folio</th>
                                                        <th>Ruta</th>
                                                        <th>Contenido</th>
                                                        <th>Responsable</th>
                                                        <th>Envío</th>
                                                        <th>Entrega est.</th>
                                                        <th>Estatus</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MESS 2025</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!-- Contenedor donde se carga el modal de enlace (vía AJAX) -->
    <div id="contenedorModalEnlace"></div>
</body>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Catálogo de estatus compartido (fuente: config_entregas.php) -->
    <script>
        window.ESTATUS_ENLACES = <?php echo json_encode(ESTATUS_ENLACES); ?>;
        window.ESTATUS_REQUIERE_COMENTARIO = <?php echo json_encode(ESTATUS_REQUIERE_COMENTARIO); ?>;
    </script>

    <script src="funciones_entregas.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#TEnlaces').DataTable({
                "responsive": true,
                "ordering": true,
                "lengthMenu": [5, 10, 25, 50, -1],
                "pageLength": 10,
                "searching": true,
                "language": {
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "No hay enlaces registrados",
                    "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando 0 a 0 de 0 registros",
                    "sInfoFiltered":   "(filtrado de _MAX_ registros)",
                    "sSearch":         "Buscar:",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
                }
            });
            cargarEnlaces();
        });
    </script>
</html>
