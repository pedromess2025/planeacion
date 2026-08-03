<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tablero Operativo - Recepción a Laboratorio</title>

<!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="css/planeacion.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>
                
                <div class="container-fluid">
                    <div class="d-flex align-items-center">
                        <!-- Nuevo Filtro por Laboratorio -->
                        <select id="filtro-laboratorio" class="form-select form-select-sm shadow-sm mr-3" style="max-width: 250px;">
                            <option value="TODOS">Todos los Laboratorios</option>
                        </select>

                        <button class="btn btn-sm btn-primary shadow-sm mr-1" id="btn-semana-anterior"><i class="fas fa-chevron-left"></i> Recepciones Pasadas</button>
                        <button class="btn btn-sm btn-secondary shadow-sm mr-1" id="btn-semana-actual">Semana Actual</button>
                        <button class="btn btn-sm btn-primary shadow-sm" id="btn-semana-siguiente">Recepciones Siguientes <i class="fas fa-chevron-right"></i></button>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary" id="rango-semana-titulo">Cargando fechas...</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center" style="width: 100%; table-layout: fixed;" id="tabla-kanban">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 16.6%;">Día Recepción</th>
                                            <th style="width: 16.6%;">1. Recepción</th>
                                            <th style="width: 16.6%;">2. Transferencia</th>
                                            <th style="width: 16.6%;">3. Laboratorio</th>
                                            <th style="width: 16.6%;">4. Terminado</th>
                                            <th style="width: 16.6%;">5. Cerrado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kanban-tbody">
                                        <!-- Dinámico -->
                                    </tbody>
                                    <tfoot class="table-light font-weight-bold">
                                        <tr>
                                            <td>Totales:</td>
                                            <td id="tot-recepcion">$0.00</td>
                                            <td id="tot-transferencia">$0.00</td>
                                            <td id="tot-laboratorio">$0.00</td>
                                            <td id="tot-terminado">$0.00</td>
                                            <td id="tot-cerrado">$0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap core JavaScript
    <script src = "vendor/jquery/jquery.min.js"></script>-->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js" defer="defer"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="funciones_canva.js"></script>
</body>
</html>