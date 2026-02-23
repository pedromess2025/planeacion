<!DOCTYPE html>

<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>ENTRADAS EQ - MESS</title>

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
            include 'menuEntradas.php';
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
                    </div>

                    <!-- TABLA DE EQUIPOS CON DATATABLE -->
                    <div class="card shadow-sm border-0">
                        <table class="table table-hover mb-0 table-responsive" id="tablaEquipos">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 15%;">Folio</th>
                                    <th style="width: 15%;">Cliente / Área</th>
                                    <th style="width: 15%;">Marca / Modelo / No Serie</th>
                                    <th style="width: 10%;">Estatus</th>
                                    <th style="width: 10%;">Fecha Entrada</th>
                                    <th style="width: 15%;">Fecha Compromiso</th>
                                    <th style="width: 15%;">Ingeniero</th>
                                    <th style="width: 10%;">Acciones</th>
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
                            <span>Copyright &copy; MESS <?php echo date('Y'); ?></span>
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
                    <button type="button" class="btn btn-outline-primary" onclick="guardarAsignacion()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modificar Ingenieros -->
    <div class="modal fade" id="modalModificarIngLabel" tabindex="-1" aria-labelledby="modalModificarIngLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 py-3">
                    <h5 class="modal-title fw-bold" id="modalModificarIngLabel">Modificar Ingenieros Asignados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding-top: 0;">
                    <small class="text-muted d-block mt-2">Selecciona un ingeniero para retirar.</small>
                    <input type="hidden" id="equipoIdModificar" value="">
                    <label class="small text-muted">Ingenieros asignados</label>
                    <select id="selectModificarIngeniero" class="form-select">
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-danger" onclick="retirarIngeniero()">Retirar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Entrada -->
    <div class="modal fade" id="modalEditarEntrada" tabindex="-1" aria-labelledby="modalEditarEntradaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalEditarEntradaLabel">Editar Entrada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <input type="hidden" id="editarEntradaId" value="">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" id="editarCliente">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre del contacto</label>
                            <input type="text" class="form-control" id="editarContacto">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contacto</label>
                            <input type="text" class="form-control" id="editarTelefono">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Área</label>
                            <select name="editarArea" id="editarArea" class="form-select">                                                          
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quien Envia</label>
                            <select name="editarQuienEnvia" id="editarQuienEnvia" class="form-select">                                
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">                        
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" id="editarMarca" name="editarMarca">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="editarModelo" name="editarModelo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. Serie</label>
                            <input type="text" class="form-control" id="editarSerie" name="editarSerie">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Notas de recepción - Diagnóstico inicial</label>
                            <textarea class="form-control" id="editarNotas" rows="3"></textarea>
                        </div>                    
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Fecha real de entrada</label>
                            <input type="date" class="form-control" id="editarFechaReal">                            
                        </div>
                        <div class="col-md-4" hidden>
                            <label class="form-label">Fecha de compromiso</label>
                            <input type="date" class="form-control" id="editarFechaCompromiso">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">OV/OT</label>
                            <input type="text" class="form-control" id="editarOV_OT">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-outline-primary" onclick="guardarEdicionEntrada()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Reprogramar Entrada -->
    <div class="modal fade" id="modalReprogramarEntrada" tabindex="-1" aria-labelledby="modalReprogramarEntradaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalReprogramarEntradaLabel">Reprogramar Entrada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <input type="hidden" id="reprogramarEntradaId" value="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha compromiso actual</label>
                            <input type="date" class="form-control" id="reprogramarFechaActual" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nueva fecha</label>
                            <input type="date" class="form-control" id="reprogramarFechaNueva">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-primary" onclick="guardarReprogramacion()">Guardar</button>
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

    <!-- Funciones Globales -->
    <script src="../loginMaster/funcionesGlobales.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            inicializarTablaEquipos();
            cargarEquipos();

            cargarIngenierosTrae();
            cargarAreas();
        });

        // Escucha el cierre de CUALQUIER modal
        document.addEventListener('hidden.bs.modal', function () {            
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());

            // Limpiar el body para restaurar el scroll y eliminar bloqueos
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';                        
        });

        // Funcion para verificar si el usuario es encargado (tiene permisos para asignar/modificar ingenieros) o es ingeniero regular (solo puede ver entradas)
        async function verificarAccesoSiEsEncargado() {
            // Funcion para verificar accesos
            async function verificarAcceso() {
            // 1.Mandamos llamar nuestra función principal. Agregamos await para esperar la respuesta
            const respuesta = await validaOpciones('entradasEq', 'verBotonesDetalleEq');
            
            // 2. Evaluamos la respuesta y aplicamos las acciones a realizar según el caso
            const cuantos = (respuesta && respuesta.status === 'success') 
                            ? parseInt(respuesta.data[0].cuantos) 
                            : 0;

            if (cuantos <= 0) {            
                return "Noesencargado"; // No tiene acceso, se puede bloquear la acción o redirigir
            }else {
                return "Esencargado"; // Tiene acceso, se permite la acción
            }
        }

        return await verificarAcceso(); //
        }

        // Variables globales
        let tablaEquiposDataTable;
        let modalAsignarInstance = null;
        let modalModificarInstance = null;
        let modalEditarInstance = null;
        let modalReprogramarInstance = null;

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
                order: [], // No aplicar ordenamiento automático, respetar orden del servidor
                columnDefs: [
                    { targets: 7, orderable: false } // Desactivar ordenamiento en columna de acciones
                ],
                dom: '<"top"f>rt<"bottom"lp><"clear">',
                drawCallback: function() {
                    // Callback después de redibujar la tabla
                }
            });
        }

        // Función para cargar equipos dinámicamente 
        async function cargarEquipos() {
            const esEncargado = await verificarAccesoSiEsEncargado(); // Verificar acceso 
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'obtenerEquipos',
                    esEncargado
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        llenarTablaEquipos(response.data);
                    } else {
                        $('#tablaEquipos tbody').html(
                            '<tr><td colspan="8" class="text-center text-muted py-5">No hay equipos pendientes</td></tr>'
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

                estatusBadge = `<span class="badge bg-warning">${equipo.estatus}</span>`
                if(equipo.estatus === 'CANCELADO') {
                    estatusBadge = `<span class="badge bg-danger">${equipo.estatus}</span>`;
                }
                if(equipo.estatus === 'TERMINADO') {
                    estatusBadge = `<span class="badge bg-success">${equipo.estatus}</span>`;
                }
                if(equipo.estatus === 'ENTRADA' ) {
                    estatusBadge = `<span class="badge bg-info">${equipo.estatus}</span>`;
                }

                let fechaBaseReprogramar = ''; 
                //Si la fecha ya pasó o es hoy 
                if (diferenciaDias < 0) claseFecha = 'fecha-danger'; 
                //Si la fecha está por llegar en 3 días o menos
                else if (diferenciaDias <= 3) claseFecha = 'fecha-warning';

                // Construir HTML de fila
                let fila = [
                    `<span class="fw-bold text-primary small">${equipo.folio}</span>`,
                    `
                    <div>
                        <small class="d-block">${equipo.cliente}</small>
                        <span class="badge bg-light text-dark"><i class="fas fa-tag"></i> ${equipo.area}</span>
                    </div>`,    
                    `
                    <div>
                        <small class="d-block">${equipo.marca} | ${equipo.modelo || 'S/R'}</small>
                        <small class="text-muted">${equipo.no_serie || 'S/R'}</small>
                    </div>`,
                    estatusBadge,
                    (() => {
                        let fechaEntrada = equipo.fecha_real_entrada && equipo.fecha_real_entrada !== '0000-00-00' ? formatearFecha(equipo.fecha_real_entrada) : 'S/R';
                        let diasTranscurridos = '';
                        if (typeof equipo.dias_transcurridos !== 'undefined' && equipo.dias_transcurridos !== null) {
                            if (equipo.dias_transcurridos >= 0) {
                                diasTranscurridos = `<span class="badge bg-info text-dark ms-1">${equipo.dias_transcurridos} días</span>`;
                            }
                        }
                        return `<small class="d-block"><i class="fas fa-calendar-alt"></i> ${fechaEntrada} ${diasTranscurridos}</small>`;
                    })(),
                    (() => {
                        let fechaCompromisoHTML = `<div>`;
                        
                        
                        // Si hay reprogramaciones, mostrar solo la nueva fecha
                        if (equipo.num_reprogramaciones > 0) {
                            fechaBaseReprogramar = equipo.fecha_reprogramacion;
                            fechaCompromisoHTML += `
                            <small class="d-block fw-bold text-primary">
                                <i class="fas fa-clock"></i> ${formatearFecha(equipo.fecha_reprogramacion)}
                            </small>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">(${equipo.num_reprogramaciones} reprogramación${equipo.num_reprogramaciones > 1 ? 'es' : ''})</small>`;
                        } else {
                            // Si no hay reprogramaciones, mostrar la fecha original
                            fechaBaseReprogramar = equipo.fecha_compromiso;
                            fechaCompromisoHTML += `
                            <small class="d-block fw-bold ${claseFecha === 'fecha-danger' ? 'text-danger' : claseFecha === 'fecha-warning' ? 'text-warning' : 'text-success'}">
                                <i class="fas fa-calendar-alt"></i> ${formatearFecha(equipo.fecha_compromiso)}
                            </small>`;
                        }
                        
                        fechaCompromisoHTML += `
                            <small class="text-muted d-block">"${equipo.diagnostico_inicial}"</small>
                        </div>`;
                        
                        return fechaCompromisoHTML;
                    })(),
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
                        
                        // Solo mostrar botones si usuario puede asignar (es encargado)
                        const puedeAsignar = equipo.puede_asignar === 'Esencargado';
                        
                            
                            // Mostrar botón de asignar solo si tiene menos de 3 ingenieros Y puede asignar
                            const botonAsignar = (cantidadIngenieros < 3 && puedeAsignar)
                                ? `<button class="btn btn-sm btn-outline-success" onclick="asignarIngeniero(${equipo.id})" title="Asignar Ingeniero">
                                    <i class="fas fa-user-plus"></i>
                                </button>`
                                : '';
                            // Agregar botón para modificar ingenieros si hay al menos 1 asignado Y puede asignar
                            const botonModificar = (cantidadIngenieros > 0 && puedeAsignar)
                                ? `<button class="btn btn-sm btn-outline-primary" onclick="modificarIngenieros(${equipo.id})" title="Modificar Ingenieros">
                                    <i class="fas fa-user-minus"></i>
                                </button>`
                                : '';
                            const botonEditar = (puedeAsignar)
                                ? `<button class="btn btn-sm btn-outline-info" onclick="editarEntrada(${equipo.id})" title="Editar Entrada">
                                    <i class="fas fa-pen"></i>
                                </button>`
                                : '';

                            const botonReprogramar = (puedeAsignar)
                                ? `<button class="btn btn-sm btn-outline-orange" onclick="reprogramarEntrada(${equipo.id}, '${fechaBaseReprogramar}')" title="Reprogramar Entrada">
                                    <i class="fas fa-calendar-alt"></i>
                                </button>`
                                : '';
                            return `<div class="btn-group" role="group" aria-label="Acciones">
                                ${botonAsignar}
                                ${botonModificar}
                                ${botonEditar}
                                ${botonReprogramar}
                                <button class="btn btn-sm btn-outline-secondary" onclick="verFicha(${equipo.id})" title="Ver Ficha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>`;
                    })()
                ];

                // Agregar fila a la tabla
                let row = tablaEquiposDataTable.row.add(fila);
            });

            // Redibujar tabla
            tablaEquiposDataTable.draw();
        }

        // Función para formatear fechas (sin conversión de zona horaria)
        function formatearFecha(fecha) {
            // Parsear fecha como local (YYYY-MM-DD) sin interpretarla como UTC
            const [año, mes, dia] = fecha.split('-');
            const fecha_local = new Date(año, mes - 1, dia);
            
            const opciones = { year: 'numeric', month: 'short', day: 'numeric' };
            return fecha_local.toLocaleDateString('es-MX', opciones);
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
                    const modalEl = document.getElementById('modalAsignarIngeniero');
                    modalAsignarInstance = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
                    modalAsignarInstance.show();
                    
                    // Permitir cerrar el modal con botones y X
                    document.querySelectorAll('#modalAsignarIngeniero [data-bs-dismiss="modal"]').forEach(btn => {
                        btn.onclick = function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            modal.hide();
                        };
                    });
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

        // Función para abrir modal de modificación y cargar ingenieros asignados
        function modificarIngenieros(equipoId) {
            document.getElementById('equipoIdModificar').value = equipoId;
            $('#modalModificarIngLabel').modal('show');
            
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'obtenerIngenierosAsignados',
                    id_registro: equipoId
                },
                success: function(response) {
                    if (response.success) {
                        const selectElement = $('#selectModificarIngeniero');
                        selectElement.empty();
                        selectElement.append($('<option></option>').attr('value', '').text('Seleccione un ingeniero...'));

                        if (Array.isArray(response.data) && response.data.length > 0) {
                            response.data.forEach(function(ing) {
                                selectElement.append($('<option></option>').attr('value', ing.id).text(ing.nombre || ('Ingeniero ' + ing.id)));
                            });
                        } else {
                            selectElement.append($('<option></option>').attr('value', '').text('No hay ingenieros asignados'));
                        }

                        
                        // Permitir cerrar el modal con botones y X
                        document.querySelectorAll('#modalModificarIngenieros [data-bs-dismiss="modal"]').forEach(btn => {
                            btn.onclick = function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                if (modalModificarInstance) {
                                    modalModificarInstance.hide();
                                }
                            };
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los ingenieros.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los ingenieros.' });
                }
            });
        }

        // Función para retirar ingeniero asignado
        function retirarIngeniero() {
            const equipoId = document.getElementById('equipoIdModificar').value;
            const ingenieroId = $('#selectModificarIngeniero').val();

            if (!ingenieroId) {
                Swal.fire({ icon: 'warning', 
                            title: 'Atención', 
                            text: 'Seleccione un ingeniero a retirar.'});
                return;
            }

            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'retirarIngeniero',
                    equipo_id: equipoId,
                    ingeniero_id: ingenieroId
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', 
                                    title: 'Listo', 
                                    text: 'Ingeniero retirado.', 
                                    }).then(() => {
                            if (modalModificarInstance) {
                                modalModificarInstance.hide();
                            } else {
                                const modalEl = document.getElementById('modalModificarIngenieros');
                                if (modalEl) {
                                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                                }
                            }
                            // Limpieza de respaldo por si queda el backdrop
                            document.body.classList.remove('modal-open');
                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                            cargarEquipos();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo retirar el ingeniero.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo retirar el ingeniero.' });
                }
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
                        $.ajax({
                            url: 'enviaNotificacionEntrada.php',
                            method: 'POST',
                            data: { id_entrada: equipoId },
                            async: true
                        });
                        Swal.fire({
                            icon: 'success',
                            title: 'Asignación Exitosa',
                            text: 'El ingeniero ha sido asignado correctamente.'
                        })
                        .then(() => {
                            // Cerrar modal usando Bootstrap
                            if (modalAsignarInstance) {
                                modalAsignarInstance.hide();
                            } else {
                                const modalEl = document.getElementById('modalAsignarIngeniero');
                                if (modalEl) {
                                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                                }
                            }
                            // Limpieza de respaldo por si queda el backdrop
                            document.body.classList.remove('modal-open');
                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
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

        // Función para abrir modal de edición y cargar detalles de la entrada
        function editarEntrada(equipoId) {
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'obtenerDetalleEquipo',
                    id_registro: equipoId
                },
                success: function(response) {
                    if (response && response.success && response.data && response.data.id_registro) {
                        const data = response.data;

                        $('#editarEntradaId').val(data.id_registro);

                        $('#editarCliente').val(data.cliente || '');
                        $('#editarContacto').val(data.contacto_nombre || '');
                        $('#editarTelefono').val(data.contacto || '');                        

                        $('#editarArea').val(data.area);
                        $('#editarQuienEnvia').val(data.id_ing_trae);
                        
                        $('#editarMarca').val(data.marca || '');
                        $('#editarModelo').val(data.modelo || '');
                        $('#editarSerie').val(data.no_serie || '');

                        $('#editarNotas').val(data.notas_recepcion || '');

                        $('#editarFechaReal').val(data.fecha_real_entrada ? data.fecha_real_entrada.split(' ')[0] : '');
                        $('#editarFechaCompromiso').val(data.fecha_promesa_entrega ? data.fecha_promesa_entrega.split(' ')[0] : '');
                        $('#editarOV_OT').val(data.ov_ot || '');

                        $('#modalEditarEntrada').modal('show');

                        const modalEl = document.getElementById('modalEditarEntrada');
                        modalEditarInstance = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
                        modalEditarInstance.show();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la entrada.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la entrada.' });
                }
            });
        }

        // Funcion para abrir modal de reprogramacion
        function reprogramarEntrada(equipoId, fechaCompromiso) {
            const fechaSolo = (fechaCompromiso || '').toString().split(' ')[0];
            $('#reprogramarEntradaId').val(equipoId);
            $('#reprogramarFechaActual').val(fechaSolo);
            $('#reprogramarFechaNueva').val('');

            const modalEl = document.getElementById('modalReprogramarEntrada');
            modalReprogramarInstance = new bootstrap.Modal(modalEl, { backdrop: true, keyboard: true });
            modalReprogramarInstance.show();
        }

        // Funcion para guardar reprogramacion
        function guardarReprogramacion() {
            const equipoId = $('#reprogramarEntradaId').val();
            const fechaNueva = $('#reprogramarFechaNueva').val();

            if (!equipoId) {
                Swal.fire({ icon: 'warning', title: 'Atencion', text: 'Falta el ID de la entrada.' });
                return;
            }
            if (!fechaNueva) {
                Swal.fire({ icon: 'warning', title: 'Atencion', text: 'Seleccione la nueva fecha.' });
                return;
            }

            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'reprogramarFecha',
                    id_registro: equipoId,
                    nueva_fecha: fechaNueva
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reprogramado',
                            text: 'La fecha se actualizo correctamente.',
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            if (modalReprogramarInstance) {
                                modalReprogramarInstance.hide();
                            } else {
                                const modalEl = document.getElementById('modalReprogramarEntrada');
                                if (modalEl) {
                                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                                }
                            }
                            document.body.classList.remove('modal-open');
                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                            cargarEquipos();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo reprogramar la fecha.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo reprogramar la fecha.' });
                }
            });
        }

        // Función para guardar edición de entrada
        function guardarEdicionEntrada() {
            const equipoId = $('#editarEntradaId').val();

            const cliente = $('#editarCliente').val().trim();
            const contacto = $('#editarContacto').val().trim();
            const telefono = $('#editarTelefono').val().trim();
            
            const area = $('#editarArea').val();
            const quienEnvia = $('#editarQuienEnvia').val();

            const marca = $('#editarMarca').val().trim();
            const modelo = $('#editarModelo').val().trim();
            const serie = $('#editarSerie').val().trim();

            const notas = $('#editarNotas').val().trim();
            const fechaReal = $('#editarFechaReal').val();
            const fechaCompromiso = $('#editarFechaCompromiso').val();
            const ov_ot = $('#editarOV_OT').val().trim();

            if (!equipoId) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Falta el ID de la entrada.' });
                return;
            }

            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'modificarEntrada',
                    id_registro: equipoId,
                    cliente: cliente,
                    contacto: contacto,
                    telefono: telefono,
                    areaedit: area,
                    quienEnvia: quienEnvia,
                    marca: marca,
                    modelo: modelo,
                    no_serie: serie,
                    notas: notas,
                    fechaReal: fechaReal,
                    fechaCompromiso: fechaCompromiso,
                    ov_ot: ov_ot                    
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: 'La entrada se actualizó correctamente.',
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            if (modalEditarInstance) {
                                modalEditarInstance.hide();
                            } else {
                                const modalEl = document.getElementById('modalEditarEntrada');
                                if (modalEl) {
                                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                                }
                            }
                            document.body.classList.remove('modal-open');
                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                            cargarEquipos();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'No se pudo actualizar la entrada.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo actualizar la entrada.' });
                }
            });
        }
        
        // Función para ver ficha del equipo
        function verFicha(equipoId) {
            window.location.href = 'entradaTareas.php?id=' + equipoId;
        }

        // Función para obtener el valor de una cookie
        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
        }

        // Función para cargar ingenieros en el select de edición
        function cargarIngenierosTrae() {
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: { accion: 'obtenerIngenieros' },
                success: function(response) {
                    var select = $('#editarQuienEnvia');
                    select.empty();
                    select.append($('<option></option>').attr('value', '0').text('Selecciona...'));

                    if (response && response.success && Array.isArray(response.data)) {
                        response.data.forEach(function(ingeniero) {
                            var option = $('<option></option>')
                                .attr('value', ingeniero.id_usuario)
                                .text(ingeniero.nombre);
                            select.append(option);
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error al cargar ingenieros',
                        icon: 'error',
                        draggable: true
                    });
                }
            });
        }

        // Función para cargar áreas en el select de edición
        function cargarAreas() {
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {accion: "obtenerAreas"},
                success: function(data) {
                    var select = $('#editarArea');
                    // soportar tanto respuesta directa como { success: true, data: [...] }
                    var areas = [];
                    if (Array.isArray(data)) {
                        areas = data;
                    } else if (data && Array.isArray(data.data)) {
                        areas = data.data;
                    }
                    // Añadir opción por defecto si no existe
                    if (select.find('option[value="0"]').length === 0) {
                        select.append($('<option></option>').attr('value', '0').text('Selecciona...'));
                    }
                    areas.forEach(function(area) {
                        var option = $('<option></option>').attr('value', area.CDAREA).text(area.AREA);
                        select.append(option);
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        title: "Error al cargar las áreas",
                        icon: "error",
                        draggable: true
                    });
                }
            });
        } 
    </script>
</body>
</html>
