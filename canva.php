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
<style>
    /* Asegurar que el contenedor de la tabla tenga scroll vertical controlado */
    .table-responsive {
        max-height: 75vh; /* Ajusta esta altura según lo alto que quieras tu tablero */
        overflow-y: auto;
    }

    /* Fijar la cabecera de la tabla Kanban */
    #tabla-kanban thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #125395; /* Tu color de fondo sólido */
        color: #ffffff; /* Asegura que el texto sea blanco para que contraste perfectamente */
        box-shadow: inset 0 -1px 0 #dee2e6;
    }
</style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>
                
                <div class="container-fluid">
                    <div class="row align-items-center mb-3">
    
                        <!-- Izquierda: Filtro por Laboratorio -->
                        <div class="col-md-4">
                            <select id="filtro-laboratorio" class="form-select form-select-sm shadow-sm" style="max-width: 250px;">
                                <option value="TODOS">Todos los Laboratorios</option>
                            </select>
                        </div>

                        <!-- Centro: Navegación por Semana -->
                        <div class="col-md-4 text-center">
                            <div class="btn-group shadow-sm" role="group">
                                <button class="btn btn-sm btn-outline-primary" id="btn-semana-anterior" title="Recepciones Pasadas"><i class="fas fa-chevron-left"></i></button>
                                <button class="btn btn-sm btn-outline-secondary px-3" id="btn-semana-actual">Semana Actual</button>
                                <button class="btn btn-sm btn-outline-primary" id="btn-semana-siguiente" title="Recepciones Siguientes"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <!-- Derecha: Botones de Acciones Especiales (Rezagados y Rastreo) -->
                        <div class="col-md-4 text-end">
                            <button class="btn btn-sm btn-warning shadow-sm text-dark font-weight-bold mr-1" id="btn-abrir-modal-rezago">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Rezagados
                            </button>
                            <button class="btn btn-sm btn-dark shadow-sm" id="btn-abrir-modal-rastreo">
                                <i class="fas fa-info mr-1"></i> Rastrear RE
                            </button>
                        </div>

                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary" id="rango-semana-titulo">Cargando fechas...</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center" style="width: 100%; table-layout: fixed;" id="tabla-kanban">
                                    <thead class="table-dark text-center">
                                        <tr>
                                            <th style="width: 16.6%;">Día Recepción</th>
                                            <th style="width: 16.6%;">1. Recepción</th>
                                            <th style="width: 16.6%;">2. Transferido</th>
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
    
<!-- Modal de Detalle / Rezagados -->
    <div class="modal fade" id="modalDetalleRezago" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetalleLabel"><i class="fas fa-list-alt"></i> Detalle de Equipos: Rezagados y Actuales</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped" id="tabla-modal-detalle" style="font-size: 0.85rem;">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Folio / Tipo</th>
                                    <th>Orden de Venta</th>
                                    <th>OT</th>
                                    <th>Cliente / Laboratorio</th>
                                    <th>Etapa Actual / Antigüedad</th>
                                    <th>F. Recepción</th>
                                    <th>F. Límite</th>
                                    <th>Valor (USD)</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-modal-detalle">
                                <!-- Los datos se llenarán dinámicamente vía AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Rastreo Estilo Guía (DHL style) -->    
    <div class="modal fade" id="modalRastreoRE" tabindex="-1" aria-labelledby="modalRastreoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                
                <!-- Cabecera limpia sin fondos pesados -->
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title font-weight-bold text-gray-800 fs-6" id="modalRastreoLabel">
                        <i class="fas fa-search-location text-primary mr-2"></i> Rastreo de Equipo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <!-- Buscador minimalista -->
                    <div class="input-group input-group-sm mb-4">
                        <input type="text" id="input-buscar-re" class="form-control form-control-solid bg-light border-0 py-2 px-3" placeholder="Ingresa el Folio RE (Ej. MESS-RE-3680-2026)">
                        <button class="btn btn-primary px-4" type="button" id="btn-ejecutar-rastreo">Buscar</button>
                    </div>

                    <!-- Contenedor de Resultados -->
                    <div id="resultado-rastreo" style="display: none;">
                        
                        <!-- Tarjeta de Resumen Compacta y Plana -->
                        <div class="bg-light p-3 rounded mb-4 border-0">
                            <div class="row g-2 text-secondary" style="font-size: 0.85rem;">
                                <div class="col-md-4">Folio: <strong class="text-dark" id="lbl-rastreo-re"></strong></div>
                                <div class="col-md-4">OV: <strong class="text-dark" id="lbl-rastreo-ov"></strong></div>
                                <div class="col-md-4">OT: <strong class="text-dark" id="lbl-rastreo-ot"></strong></div>
                                <div class="col-md-6 mt-2">Cliente: <strong class="text-dark" id="lbl-rastreo-cliente"></strong></div>
                                <div class="col-md-6 mt-2">Laboratorio: <strong class="text-dark" id="lbl-rastreo-lab"></strong></div>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size: 0.7rem; letter-spacing: 0.5px;">Historial de Etapas</h6>
                        
                        <!-- Línea de Tiempo Minimalista nativa -->
                        <div id="timeline-etapas" class="px-2">
                            <!-- Se llena dinámicamente -->
                        </div>
                    </div>

                    <div id="mensaje-busqueda" class="text-center text-muted py-5 small">
                        Ingresa un folio de recepción para consultar su travesía.
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