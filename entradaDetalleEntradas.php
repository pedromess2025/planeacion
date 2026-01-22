<!DOCTYPE html>

<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PLANEACION MESS</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', system-ui, sans-serif; }
        .card-equipment { border: 1px solid #e0e6ed; border-radius: 12px; background: #fff; transition: all 0.2s; }
        .card-equipment:hover { border-color: #0d6efd; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        /* Estilos de información */
        .info-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 700; }
        .info-value { font-size: 0.85rem; color: #2d3436; font-weight: 600; display: block; }
        
        /* Badges de Área */
        .area-tag { font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #f8f9fa; border: 1px solid #dee2e6; }
        
        /* Thumbnail */
        .img-container { position: relative; width: 80px; height: 80px; }
        .img-preview { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; }
        .photo-count { position: absolute; bottom: -5px; right: -5px; background: #000; color: #fff; font-size: 0.6rem; padding: 2px 5px; border-radius: 50%; }

        /* ===== ESTILOS PARA TABLA DE EQUIPOS ===== */
        .table-equipos-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-equipos {
            width: 100%;
            margin: 0;
        }

        .table-equipos thead {
            background: #f8f9fa;
            border-bottom: 1px solid #e0e6ed;
        }

        .table-equipos thead th {
            padding: 18px 15px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.5px;
            border: none;
        }

        .table-equipos tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .table-equipos tbody tr:hover {
            background-color: #fafbfc;
        }

        /* Fila del borde izquierdo (warning) */
        .table-equipos tbody tr.row-warning {
            border-left: 4px solid #ffc107;
        }

        .table-equipos tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            font-size: 0.95rem;
            color: #2d3436;
        }

        /* Folio */
        .cell-folio {
            font-weight: 700;
            color: #0d6efd;
            font-size: 1.1rem;
        }

        /* Imagen */
        .img-container-table {
            position: relative;
            width: 80px;
            height: 80px;
            flex-shrink: 0;
        }

        .img-container-table img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .photo-count-table {
            position: absolute;
            bottom: -8px;
            right: -8px;
            background: #000;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 6px;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Información */
        .info-label-table {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 700;
            display: block;
            margin-bottom: 2px;
        }

        .info-value-table {
            font-size: 0.95rem;
            color: #2d3436;
            font-weight: 600;
            display: block;
        }

        .info-subtitle-table {
            font-size: 0.8rem;
            color: #999;
            display: block;
            margin-top: 2px;
        }

        /* Area Tag */
        .area-tag-table {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 4px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            display: inline-block;
            margin-top: 4px;
        }

        /* Fecha Compromiso */
        .fecha-danger {
            color: #dc3545;
            font-weight: 700;
        }

        .fecha-success {
            color: #198754;
            font-weight: 700;
        }

        .fecha-warning {
            color: #ff9800;
            font-weight: 700;
        }

        /* Sin asignar */
        .sin-asignar {
            color: #ffc107;
            font-weight: 700;
        }

        /* Botones */
        .cell-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .cell-actions .btn-sm {
            padding: 6px 14px;
            font-size: 0.85rem;
            white-space: nowrap;
        }

        /* DataTables responsive */
        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
        }

        .dataTables_wrapper .dataTables_info {
            padding: 15px 20px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Select2 en Modales */
        .select2-container--open .select2-dropdown {
            z-index: 1070 !important; /* Asegurar que aparezca encima del modal */
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-selection--single {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 38px !important;
        }

        .select2-dropdown {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }

        @media (max-width: 768px) {
            .table-equipos tbody td {
                padding: 15px 10px;
                font-size: 0.85rem;
            }

            .img-container-table {
                width: 60px;
                height: 60px;
            }

            .cell-actions .btn-sm {
                padding: 4px 10px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php
            include 'menu.php';
        ?>
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <?php
                    include 'encabezado.php';
                ?>
                <!-- Begin Page Content -->
                <div class="container-fluid py-4">
                    <div class="row mb-4">
                        <div class="col">
                            <h2 class="fw-bold text-dark mb-1">Entrada de Equipos</h2>
                        </div>
                        <div class="col-auto">
                            <a href="entradaControlEquipos.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus"></i> Nuevo Registro</a>
                        </div>
                    </div>

                    <!-- TABLA DE EQUIPOS CON DATATABLE -->
                    <div class="table-equipos-wrapper">
                        <table class="table-equipos" id="tablaEquipos">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Folio</th>
                                    <th style="width: 15%;">Cliente / Área</th>
                                    <th style="width: 15%;">Equipo / Marca / Modelo</th>
                                    <th style="width: 15%;">Estatus</th>
                                    <th style="width: 15%;">Fecha Compromiso</th>
                                    <th style="width: 15%;">Ingeniero</th>
                                    <th style="width: 25%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Las filas se llenarán dinámicamente con jQuery/AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="sticky-footer bg-white">
                    <div class="container my-auto">
                        <div class="copyright text-center my-auto">
                            <span>Copyright &copy; MESS 2025</span>
                        </div>
                    </div>
                </footer>
                <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Modal Asignar Ingeniero -->
    <div class="modal fade" id="modalAsignarIngeniero" tabindex="-1" aria-labelledby="modalAsignarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalAsignarLabel">Asignar Ingeniero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <input type="hidden" id="equipoIdModal" value="">
                    <select id="selectIngenieroModal" class="form-select form-select-lg">
                        <option value="">Cargando ingenieros...</option>
                    </select>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary" onclick="guardarAsignacion()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal-->
    <a class="scroll-to-top rounded" href="#page-top">
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cerrar sesión</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">¿Estas seguro?</div>
                <div class="modal-footer">
                    <button class="btn btn-info" type="button" data-dismiss="modal">Cancelar</button>
                    <a class="btn btn-danger" href="logout">Salir</a>
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

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            inicializarTablaEquipos();
            cargarEquipos();
        });

        // Variables globales
        let tablaEquiposDataTable;

        // Función para inicializar DataTable
        function inicializarTablaEquipos() {
            tablaEquiposDataTable = $('#tablaEquipos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                paging: true,
                pageLength: 10,
                responsive: true,
                ordering: true,
                searching: true,
                columnDefs: [
                    { targets: 5, orderable: false } // Desactivar ordenamiento en columna de acciones
                ],
                dom: '<"top"f>rt<"bottom"lp><"clear">',
                drawCallback: function() {
                    // Callback después de redibujar la tabla
                }
            });
        }

        // Función para cargar equipos dinámicamente 
        function cargarEquipos() {
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'obtenerEquipos'
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        llenarTablaEquipos(response.data);
                    } else {
                        $('#tablaEquipos tbody').html(
                            '<tr><td colspan="6" class="text-center text-muted py-5">No hay equipos pendientes</td></tr>'
                        );
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los equipos.'
                    });
                }
            });
        }

        // Función para llenar la tabla con datos
        function llenarTablaEquipos(equipos) {
            if (!tablaEquiposDataTable) {
                inicializarTablaEquipos();
            }

            // Limpiar tabla anterior
            tablaEquiposDataTable.clear();

            // Agregar nuevas filas
            equipos.forEach(function(equipo) {
                // Determinar clase de fecha (danger/warning/success)
                let claseFecha = 'fecha-success';
                let hoy = new Date();
                hoy.setHours(0, 0, 0, 0);
                let fechaCompromiso = new Date(equipo.fecha_compromiso);
                fechaCompromiso.setHours(0, 0, 0, 0);
                let diferenciaDias = Math.ceil((fechaCompromiso - hoy) / (1000 * 60 * 60 * 24));

                //Si la fecha ya pasó o es hoy 
                if (diferenciaDias < 0) claseFecha = 'fecha-danger'; 
                //Si la fecha está por llegar en 3 días o menos
                else if (diferenciaDias <= 3) claseFecha = 'fecha-warning';

                // Construir HTML de fila
                let fila = [
                    `<span class="cell-folio">${equipo.folio}</span>`,
                    `
                    <div>
                        <span class="info-label-table">Cliente</span>
                        <span class="info-value-table">${equipo.cliente} <span class="area-tag-table"><i class="fas fa-tag"></i> ${equipo.area}</span></span>
                    </div>`,    
                    `
                    <div>
                        <span class="info-value-table">${equipo.marca} | ${equipo.modelo} | ${equipo.no_serie}</span>
                    </div>`,
                    `<span class="info-value-table">${equipo.estatus}</span>`,
                    `
                    <div>
                        <span class="info-value-table ${claseFecha}">
                            <i class="fas fa-calendar-alt"></i> ${formatearFecha(equipo.fecha_compromiso)}
                        </span>
                        <span class="info-subtitle-table">"${equipo.diagnostico_inicial}"</span>
                    </div>`,
                    `<span class="info-value-table ${equipo.nombre ? '' : 'sin-asignar'}">${equipo.nombre || 'Sin asignar'}</span>`,
                    `<div class="cell-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="asignarIngeniero(${equipo.id})">
                            Asignar Ing.
                        </button>
                        <button class="btn btn-sm btn-light" onclick="verFicha(${equipo.id})">
                            Ver Ficha
                        </button>
                    </div>
                    `
                ];

                // Agregar fila a la tabla
                let row = tablaEquiposDataTable.row.add(fila);

                // Aplicar clase si hay alerta (borde amarillo)
                if (diferenciaDias <= 3 && diferenciaDias >= 0) {
                    row.nodes().to$().addClass('row-warning');
                }
            });

            // Redibujar tabla
            tablaEquiposDataTable.draw();
        }

        // Función para formatear fechas
        function formatearFecha(fecha) {
            const opciones = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(fecha).toLocaleDateString('es-MX', opciones);
        }

        // Funcion para cargar ingenieros 
        function cargarIngenieros() {
            return $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'obtenerIngenieros'
                }
            });
        }

        // Función para abrir modal y cargar ingenieros
        function asignarIngeniero(equipoId) {
            document.getElementById('equipoIdModal').value = equipoId;
            
            // Cargar ingenieros
            cargarIngenieros().done(function(response) {
                if (response.success) {
                    // Limpiar y construir opciones
                    let selectElement = document.getElementById('selectIngenieroModal');
                    selectElement.innerHTML = '<option value="">Seleccione un Ingeniero...</option>';
                    
                    response.data.forEach(function(ingeniero) {
                        let option = document.createElement('option');
                        option.value = ingeniero.id;
                        option.textContent = ingeniero.nombre;
                        selectElement.appendChild(option);
                    });

                    // Inicializar Select2
                    if ($.fn.select2) {
                        // Destruir instancia anterior si existe
                        if ($('#selectIngenieroModal').hasClass('select2-hidden-accessible')) {
                            $('#selectIngenieroModal').select2('destroy');
                        }
                        
                        $('#selectIngenieroModal').select2({
                            language: 'es',
                            placeholder: 'Buscar ingeniero...',
                            allowClear: false,
                            width: '100%',
                            dropdownParent: $('#modalAsignarIngeniero')
                        });
                    }

                    // Abrir el modal
                    const modal = new bootstrap.Modal(document.getElementById('modalAsignarIngeniero'));
                    modal.show();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudieron cargar los ingenieros.'
                    });
                }
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los ingenieros.'
                });
            });
        }

        // Funcion para guardar asignacion de ingeniero
        function guardarAsignacion() {
            let equipoId = document.getElementById('equipoIdModal').value;
            let ingenieroId = document.getElementById('selectIngenieroModal').value;

            if (!ingenieroId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, seleccione un ingeniero.'
                });
                return;
            }

            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'asignarIngeniero',
                    equipo_id: equipoId,
                    ingeniero_id: ingenieroId
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Asignación Exitosa',
                            text: 'El ingeniero ha sido asignado correctamente.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        })
                        .then(() => {
                            // Cerrar modal
                            const modalElement = document.getElementById('modalAsignarIngeniero');
                            const modal = new bootstrap.Modal(modalElement);
                            modal.hide();
                            
                            // Recargar tabla
                            cargarEquipos();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudo asignar el ingeniero.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo asignar el ingeniero.'
                    });
                }
            });
        }
        
        // Función para ver ficha del equipo
        function verFicha(equipoId) {
            window.location.href = 'entradaTareas.php?id=' + equipoId;
        }
        
        function convertirTexto(e) {
            // Convertir a mayúsculas y quitar acentos
            e.value = e.value
            .toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        }

        // Función para obtener el valor de una cookie
        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
        }
    </script>
</body>
</html>
