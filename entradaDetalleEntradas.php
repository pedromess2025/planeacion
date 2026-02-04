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
                    <div class="card shadow-sm border-0">
                        <table class="table table-hover mb-0" id="tablaEquipos">
                            <thead class="table-light">
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
                    `<span class="fw-bold text-primary fs-6">${equipo.folio}</span>`,
                    `
                    <div>
                        <small class="d-block">${equipo.cliente}</small>
                        <span class="badge bg-light text-dark"><i class="fas fa-tag"></i> ${equipo.area}</span>
                    </div>`,    
                    `
                    <div>
                        <small class="d-block">${equipo.marca} | ${equipo.modelo}</small>
                        <small class="text-muted">${equipo.no_serie}</small>
                    </div>`,
                    `<span class="badge bg-secondary">${equipo.estatus}</span>`,
                    `
                    <div>
                        <small class="d-block fw-bold ${claseFecha === 'fecha-danger' ? 'text-danger' : claseFecha === 'fecha-warning' ? 'text-warning' : 'text-success'}">
                            <i class="fas fa-calendar-alt"></i> ${formatearFecha(equipo.fecha_compromiso)}
                        </small>
                        <small class="text-muted d-block">"${equipo.diagnostico_inicial}"</small>
                    </div>`,
                    (() => {
                        const nombresStr = (equipo.nombres_ingenieros || equipo.nombre || '').toString();

                        let nombresArr = nombresStr
                            .split(',')
                            .map(n => n.trim())
                            .filter(n => n.length > 0);

                        if (nombresArr.length === 0 && nombresStr.trim().length > 0) {
                            nombresArr = [nombresStr.trim()];
                        }

                        const maxLen = Math.max(nombresArr.length);
                        const items = [];

                        for (let i = 0; i < maxLen; i++) {
                            const nombre = nombresArr[i] || '';
                            const etiqueta = nombre ? `${nombre}` : '';
                            if (etiqueta) {
                                items.push(`<i class="fas fa-user"></i> ${etiqueta}`);
                            }
                        }

                        const contenido = items.join('<br>');
                        return `<small class="d-block ${contenido ? '' : 'text-warning fw-bold'}">${contenido ? ' ' + contenido : 'Sin asignar'}</small>`;
                    })(),
                    (() => {
                        // Contar ingenieros asignados
                        const idsIngenieros = (equipo.ids_ingenieros || '').trim();
                        const cantidadIngenieros = idsIngenieros.length > 0 ? idsIngenieros.split(',').length : 0;
                        
                        // Mostrar botón de asignar solo si tiene menos de 3 ingenieros
                        const botonAsignar = cantidadIngenieros < 3 
                            ? `<button class="btn btn-sm btn-outline-primary" onclick="asignarIngeniero(${equipo.id})" title="Asignar Ingeniero">
                                <i class="fas fa-user-plus"></i>
                            </button>`
                            : '';
                        
                        return `<div class="d-flex gap-2">
                            ${botonAsignar}
                            <button class="btn btn-sm btn-outline-warning" onclick="verFicha(${equipo.id})" title="Ver Ficha">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>`;
                    })()
                ];

                // Agregar fila a la tabla
                let row = tablaEquiposDataTable.row.add(fila);

                // Aplicar clase si hay alerta (borde izquierdo amarillo)
                if (diferenciaDias <= 3 && diferenciaDias >= 0) {
                    row.nodes().to$().addClass('border-start border-warning border-5');
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
                        option.value = ingeniero.id_usuario;
                        option.textContent = ingeniero.nombre;
                        selectElement.appendChild(option);
                    });

                    // Inicializar Select2
                    if ($.fn.select2) {
                        $('#selectIngenieroModal').select2({
                            language: 'es',
                            placeholder: 'Buscar ingeniero...',
                            allowClear: false,
                            width: '100%',
                            dropdownParent: $('#modalAsignarIngeniero')
                        });
                    }
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
            let ingeniero_id = document.getElementById('selectIngenieroModal').value;

            if (!ingeniero_id) {
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
                    ingeniero_id: ingeniero_id
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
                            // Cerrar modal usando jQuery
                            $('#modalAsignarIngeniero').modal('hide');
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
