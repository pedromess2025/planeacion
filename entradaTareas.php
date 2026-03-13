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
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .card-order { border: none; border-radius: 15px; }
        .header-accent { border-left: 5px solid #0d6efd; padding-left: 15px; }
        .img-gallery { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 10px; }
        .img-gallery img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; cursor: pointer; transition: 0.3s; }
        .img-gallery img:hover { opacity: 0.8; }
        .status-update-box { background-color: #f8f9fa; border-radius: 12px; padding: 20px; }
        .sticky-footer { padding: 2rem 0; flex-shrink: 0; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper" class="d-flex">
        <?php include 'menuEntradas.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column w-100">
            <div id="content">
                <?php include 'encabezadoEntradas.php'; ?>

                <div class="container py-4">
                    <div class="row">
                        <!-- DETALLE DEL EQUIPO -->
                        <div class="col-lg-5">
                            <div class="card card-order shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="header-accent">
                                            <small class="text-muted text-uppercase fw-bold">Folio de Servicio</small>
                                            <h6 id="folio" class="fw-bold text-primary"></h6>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger" onclick="generarPDF()">
                                            <i class="fas fa-file-pdf"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="history.back()">
                                            <i class="bi bi-arrow-left"></i> Volver
                                        </button>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small text-muted text-uppercase fw-bold d-block">Ingeniero(s):</label>
                                        <div id="ingeniero" name="ingeniero"></div>
                                        <div id="horas" name="horas"></div>
                                    </div>
                                    <hr>
                                    <div class="row g-2 mb-0 align-items-start">
                                        <div class="col-md-7">
                                            <label class="small text-muted text-uppercase fw-bold d-block">Equipo:</label>
                                            <label id="equipo" name="equipo" class="small text-muted d-block mb-0"></label>
                                            <label id="cliente" name="cliente" class="small text-muted d-block mb-0"></label>
                                            <label id="contacto" name="contacto" class="small text-muted d-block mb-0"></label>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-md-5">
                                        <div id="refaccion_resumen" name="refaccion_resumen"></div>
                                        <div id="precio_refaccion_resumen" name="precio_refaccion_resumen"></div>
                                    </div>
                                    <hr>
                                    <div class="mb-3">
                                        <label class="small text-muted text-uppercase fw-bold d-block">Notas Recepción</label>
                                        <p id="diagnostico" name="diagnostico" class="small text-dark bg-light p-2 rounded border" >Sin diagnóstico inicial</p>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted text-uppercase fw-bold d-block">Notas Ing(s).</label>
                                        <p id="nota_ing" name="nota_ing" class="small text-dark bg-light p-2 rounded border">Sin nota inicial</p>
                                    </div>

                                    <label class="small text-muted text-uppercase fw-bold d-block mb-2">Fotos de Entrada</label>
                                    <div class="img-gallery">
                                        <!-- Fotos se cargarán aquí dinámicamente -->
                                    </div>
                                    <button type="button" id="btnVerFotos" class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalFotos" style="display:none;">
                                        <i class="fas fa-image"></i> Ver fotos entrada
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- FORMULARIO DE ACTUALIZACION DE TRABAJO -->
                        <div class="col-lg-7">
                            <div class="card card-order shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="fw-bold mb-4">Actualización de Trabajo</h5>
                                    <form id="actualizar" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id_usuarioL" value="">
                                        <input type="hidden" name="accion" value="guardarSeguimiento">
                                        <div class="status-update-box mb-4">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small text-muted">ESTATUS ACTUAL:</label>
                                                    <select name="nuevo_estatus" class="form-select border-primary fw-bold">
                                                        <option value="ENTRADA" selected>📥 Entrada</option>
                                                        <option value="CUARENTENA">⏳ Cuarentena</option>
                                                        <option value="DEMO">🕹️ En Demo</option>
                                                        <option value="DIAGNOSTICO">🔍 En Diagnóstico</option>
                                                        <option value="REPARACION">🛠️ En Reparación</option>
                                                        <option value="REFACCIONES">📦 Espera de Refacciones</option>
                                                        <option value="CALIBRACION">⚖️ En Calibración</option>
                                                        <option value="TERMINADO">✅ Terminado</option>
                                                        <option value="SINENVIAR">🔚 Terminado Sin Enviar</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small text-muted">FECHA ACTUALIZACION:</label>
                                                    <input type="date" name="fecha_termino" class="form-control" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small text-muted">HORAS TRABAJADAS:</label>
                                                    <input type="number" name="hrs_trabajadas" class="form-control" min="0.25" max="24" step="0.25" placeholder="Ej. 2.5" required>
                                                </div>
                                            </div>
                                            <div id="camposRefacciones" class="row g-3 mt-1" style="display:none;">
                                                <div class="col-md-8">
                                                    <label class="form-label fw-bold small text-muted">REFACCIÓN:</label>
                                                    <input type="text" name="refaccion" class="form-control" maxlength="500" placeholder="Describe la refacción utilizada">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold small text-muted">PRECIO ESTIMADO:</label>
                                                    <input type="number" name="precio_refaccion" class="form-control" min="0" step="1" placeholder="Ej. 1500">
                                                </div>
                                            </div>

                                            <div id="campoPdfTerminado" class="row g-3 mt-1" style="display:none;">
                                                <div class="col-md-12">
                                                    <label class="form-label fw-bold small text-muted">SUBIR REPORTE DE SERVICIO:</label>
                                                    <input type="file" name="reporte_pdf" class="form-control" accept="application/pdf" data-max-files="1">
                                                    <small class="text-muted">Solo se permite 1 archivo PDF.</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label fw-bold small text-muted">BITÁCORA DE SERVICIO / NOTAS TÉCNICAS</label>
                                            <textarea name="notas_ingeniero" class="form-control" rows="6" placeholder="Escriba aquí los hallazgos, componentes reemplazados o procedimientos realizados..." required></textarea>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold small text-muted">SUBIR EVIDENCIA DE SALIDA (FOTOS)</label>
                                            <input type="file" name="fotos_salida[]" class="form-control" multiple accept="image/*" data-max-files="1">
                                            <small class="text-muted">Puedes seleccionar varias fotos del equipo reparado.</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="form-check m-0" style="min-width: 140px;">
                                                <label class="form-check-label fw-bold small text-muted" for="notificacion">Notificación </label>
                                                <input class="form-check-input ms-2" type="checkbox" id="notificacion" name="notificacion" value="1" checked>
                                            </div>
                                            <div class="flex-fill text-center">
                                                <button type="button" class="btn btn-outline-primary px-5 fw-bold shadow-sm" onclick="guardarCambios(); return false;">
                                                    Guardar Avance
                                                </button>
                                            </div>
                                            <div style="min-width: 140px;"></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> </div> </div>

            <!-- Modal Carrusel de Fotos de Entrada -->
            <div class="modal fade" id="modalFotos" tabindex="-1" aria-labelledby="modalFotosLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFotosLabel">Fotos de entrada</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="carouselFotos" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner" id="carouselFotosInner">
                                    <!-- Slides dinámicos -->
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselFotos" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselFotos" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Carrusel de Fotos de Seguimiento -->
            <div class="modal fade" id="modalFotosSeguimiento" tabindex="-1" aria-labelledby="modalFotosSeguimientoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFotosSeguimientoLabel">Fotos del seguimiento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="carouselFotosSeg" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner" id="carouselFotosSegInner">
                                    <!-- Slides dinámicos -->
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselFotosSeg" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselFotosSeg" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white border-top mt-auto">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; MESS <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
        </div> </div> <a class="btn btn-primary position-fixed bottom-0 end-0 m-3 rounded-circle shadow" href="#page-top" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
        <i class="fas fa-angle-up"></i>
    </a>

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

    <script>
        let notasSeguimiento = []; // Variable global para guardar las notas
        
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });

            inicializaCamposRefacciones();

            // Obtener ID del equipo desde URL
            const params = new URLSearchParams(window.location.search);
            const id_registro = params.get('id');
            cargarDetalleEquipo(id_registro);
            
        });

        // Función para cargar los detalles del equipo
        function cargarDetalleEquipo(id_registro) {
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'obtenerDetalleEquipo',
                    id_registro: id_registro
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const equipo = response.data;
                        
                        // Folio
                        $('#folio').text(equipo.folio || '#ENT-0000-00');
                        
                        // Ingeniero
                        const nombresStr = equipo.ingeniero_nombre || equipo.nombres_ingenieros || 'Sin asignar';
                        // Dividir los nombres por comas y crear elementos separados
                        const nombresList = nombresStr.split(',').map(n => n.trim()).filter(n => n);
                        const horasPorIngeniero = Array.isArray(equipo.horas_por_ingeniero) ? equipo.horas_por_ingeniero : [];

                        // Normalizar nombre para evitar fallas por acentos/espacios/mayúsculas
                        const normalizaNombre = (valor) => String(valor || '')
                            .normalize('NFD')
                            .replace(/[\u0300-\u036f]/g, '')
                            .replace(/\s+/g, ' ')
                            .trim()
                            .toLowerCase();

                        const horasPorNombre = {};
                        horasPorIngeniero.forEach(item => {
                            const clave = normalizaNombre(item.nombre);
                            const horasActuales = parseFloat(item.horas || 0);
                            if (!clave) {
                                return;
                            }
                            horasPorNombre[clave] = (horasPorNombre[clave] || 0) + horasActuales;
                        });

                        // Actualizar el contenedor con los nombres
                        let ingenierosHtml = nombresList.map(nombre => {
                            const claveNombre = normalizaNombre(nombre);
                            const horas = (horasPorNombre[claveNombre] || 0).toFixed(2);
                            return `<small class="d-flex justify-content-between text-dark"><span>-${nombre}</span><span class="ms-3">${horas} hrs</span></small>`;
                        }).join('');
                        $('#ingeniero').html(ingenierosHtml || '<small class="d-block text-muted">Sin asignar</small>');

                        // Horas acumuladas
                        const totalHoras = parseFloat(equipo.total_horas || 0).toFixed(2);
                        $('#horas').html(`<small class="d-block text-md-end text-dark fw-bold">Total: ${totalHoras} hrs</small>`);

                        // Refacciones utilizadas
                        const refacciones = Array.isArray(equipo.refacciones) ? equipo.refacciones : [];
                        if (refacciones.length > 0) {
                            const fmt = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 });
                            let refHtml = '<small class="d-block text-muted text-uppercase fw-bold mb-1">Refacciones:</small>';
                            refHtml += refacciones.map(r =>
                                `<small class="d-block text-dark">• ${r.refaccion}</small>` +
                                (r.precio_refaccion > 0 ? `<small class="d-block text-success fw-bold">${fmt.format(r.precio_refaccion)}</small>` : '')
                            ).join('');
                            $('#refaccion_resumen').html(refHtml);
                        } else {
                            $('#refaccion_resumen').empty();
                        }
                        $('#precio_refaccion_resumen').empty();
                        
                        // Equipo
                        const equipoTexto = `${equipo.marca || ''} ${equipo.modelo ? '-' + equipo.modelo : '- N/A'} ${equipo.no_serie ? '-' + equipo.no_serie : '- S/N'}`.trim();
                        $('#equipo').text(equipoTexto);

                        // Cliente y contacto
                        $('#cliente').text(equipo.cliente || 'N/A');
                        $('#contacto').text(equipo.contacto || 'N/A');
                        // Diagnóstico
                        var diagnosticoText = equipo.notas_recepcion && equipo.fecha_registro ? `Registrado el ${equipo.fecha_registro}<br> Diagnóstico: ${equipo.notas_recepcion}` : 'Sin diagnóstico inicial';
                        $('#diagnostico').html(diagnosticoText); 
                        
                        // Nota Ing. - Ahora es un array de objetos con html y fotos
                        if (equipo.notas_seguimiento && equipo.notas_seguimiento.length > 0) {
                            notasSeguimiento = equipo.notas_seguimiento; // Guardar globalmente
                            const notasHTML = equipo.notas_seguimiento.map((nota, index) => {
                                // Reemplazar el onclick con índice en lugar de JSON
                                return nota.html.replace(/onclick="[^"]*"/, `onclick="mostrarFotosSeguimiento(${index})"`);
                            }).join('<br><br>');
                            $('#nota_ing').html(notasHTML);
                        } else {
                            $('#nota_ing').text('Sin notas de seguimiento');
                        }

                        // Fotos
                        if (equipo.fotos && equipo.fotos.length > 0) {
                            $('#btnVerFotos').show();
                            renderCarouselFotos(equipo.fotos);
                        } else {
                            $('#btnVerFotos').hide();
                            $('#carouselFotosInner').empty();
                        }

                        // Estatus actual
                        if (equipo.estatus) {
                            $('select[name="nuevo_estatus"]').val(equipo.estatus);
                            actualizaCamposRefacciones(equipo.estatus);
                        }
                        
                        // Fecha de término
                        if (equipo.fechaTermino) {
                            $('input[name="fecha_termino"]').val(equipo.fechaTermino.substring(0, 10));
                        }
                        
                        // Notas previas
                        if (equipo.notas_ingeniero) {
                            $('textarea[name="notas_ingeniero"]').val(equipo.notas_ingeniero);
                        }
                        
                        // ID de registro en el formulario
                        $('form').append(`<input type="hidden" name="id_registro" value="${id_registro}">`);
                    }
                },
                error: function() {
                    console.log('Error al cargar los datos del equipo');
                }
            });
        }

        function inicializaCamposRefacciones() {
            const selectEstatus = $('select[name="nuevo_estatus"]');
            actualizaCamposRefacciones(selectEstatus.val());

            selectEstatus.on('change', function() {
                actualizaCamposRefacciones($(this).val());
            });
        }

        function actualizaCamposRefacciones(estatus) {
            const estatusNormalizado = String(estatus || '').toUpperCase();
            const mostrarRefacciones = estatusNormalizado === 'REFACCIONES';
            const mostrarPdf = estatusNormalizado === 'TERMINADO';

            const contenedor = $('#camposRefacciones');
            const inputRefaccion = $('input[name="refaccion"]');
            const inputPrecio = $('input[name="precio_refaccion"]');
            const contenedorPdf = $('#campoPdfTerminado');
            const inputPdf = $('input[name="reporte_pdf"]');

            if (mostrarRefacciones) {
                contenedor.show();
                inputRefaccion.prop('required', true);
                inputPrecio.prop('required', true);
            } else {
                contenedor.hide();
                inputRefaccion.prop('required', false).val('');
                inputPrecio.prop('required', false).val('');
            }

            if (mostrarPdf) {
                contenedorPdf.show();
                inputPdf.prop('required', true);
            } else {
                contenedorPdf.hide();
                inputPdf.prop('required', false).val('');
            }
        }

        // Funcion para guardar cambios
        function guardarCambios() {
            const archivoPdf = $('input[name="reporte_pdf"]')[0];
            const estatusActual = String($('select[name="nuevo_estatus"]').val() || '').toUpperCase();

            if (estatusActual === 'TERMINADO') {
                if (!archivoPdf || !archivoPdf.files || archivoPdf.files.length !== 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Archivo requerido',
                        text: 'Para estatus TERMINADO debes subir 1 archivo PDF.',
                        confirmButtonText: 'Aceptar'
                    });
                    return false;
                }

                const archivo = archivoPdf.files[0];
                const esPdf = archivo.type === 'application/pdf' || /\.pdf$/i.test(archivo.name || '');
                if (!esPdf) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Formato inválido',
                        text: 'El archivo debe ser PDF.',
                        confirmButtonText: 'Aceptar'
                    });
                    return false;
                }
            }

            // Validar campos required del formulario
            var formElement = document.getElementById('actualizar');
            if (formElement && !formElement.checkValidity()) {
                formElement.reportValidity(); // Muestra mensajes de validación HTML5
                return false;
            }

            // Validar que las notas no estén vacías
            const notas = $('textarea[name="notas_ingeniero"]').val().trim();
            if (!notas || notas === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Debes escribir notas técnicas antes de guardar.',
                    confirmButtonText: 'Aceptar'
                });
                return false;
            }
            
            // Llenar el campo oculto con el valor de la cookie
            $('input[name="id_usuarioL"]').val(getCookie('id_usuarioL'));
            var formData = new FormData($('#actualizar')[0]);
            const notificacionMarcada = $('#notificacion').is(':checked');
            const idRegistro = $('input[name="id_registro"]').val();
            
            $.ajax({
                url: 'accionesEntradas.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if (notificacionMarcada && idRegistro) {
                            registrarNotificacionEntrada(idRegistro, response.id_seguimiento, function(notificacionOk) {
                                const textoExito = notificacionOk
                                    ? 'Los cambios se han guardado correctamente.'
                                    : 'Los cambios se guardaron, pero no se pudo registrar la notificación.';

                                Swal.fire({
                                    icon: notificacionOk ? 'success' : 'warning',
                                    title: notificacionOk ? '¡Éxito!' : 'Atención',
                                    text: textoExito,
                                    confirmButtonText: 'Aceptar'
                                }).then(() => {
                                    location.reload();
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: 'Los cambios se han guardado correctamente.',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Hubo un problema al guardar los cambios.',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error AJAX:', textStatus, errorThrown);
                    console.error('Respuesta:', jqXHR.responseText);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo registrar la información.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        }

        function registrarNotificacionEntrada(idRegistro, idSeguimiento, callback) {
            $.ajax({
                url: 'acciones_notificaciones.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'registrarNotificacionEntrada',
                    id_registro_referencia: idRegistro, 
                    id_seguimiento: idSeguimiento
                },
                success: function(response) {
                    callback(!!(response && response.success));
                },
                error: function() {
                    callback(false);
                }
            });
        }

        // Función para obtener el valor de una cookie
        function getCookie(name) {
            const nameEQ = name + "=";
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                let cookie = cookies[i].trim();
                if (cookie.indexOf(nameEQ) === 0) {
                    return cookie.substring(nameEQ.length);
                }
            }
            return null;
        }

        // Función para renderizar el carrusel de fotos de entrada
        function renderCarouselFotos(fotos) {
            const carouselInner = $('#carouselFotosInner');
            carouselInner.empty();
            fotos.forEach((foto, index) => {
                const activeClass = index === 0 ? 'active' : '';
                carouselInner.append(`
                    <div class="carousel-item ${activeClass}">
                        <img src="${foto}" class="d-block w-100" alt="Foto ${index + 1}">
                    </div>
                `);
            });
        }

        // Función para mostrar fotos de seguimiento en el modal
        function mostrarFotosSeguimiento(index) {
            if (notasSeguimiento && notasSeguimiento[index] && notasSeguimiento[index].fotos) {
                const fotos = notasSeguimiento[index].fotos;
                if (fotos && fotos.length > 0) {
                    const carouselInner = $('#carouselFotosSegInner');
                    carouselInner.empty();
                    fotos.forEach((foto, i) => {
                        const activeClass = i === 0 ? 'active' : '';
                        carouselInner.append(`
                            <div class="carousel-item ${activeClass}">
                                <img src="${foto}" class="d-block w-100" alt="Foto ${i + 1}">
                            </div>
                        `);
                    });
                    const modal = new bootstrap.Modal(document.getElementById('modalFotosSeguimiento'));
                    modal.show();
                }
            }
        }

        // Función para generar PDF
        function generarPDF() {
            const params = new URLSearchParams(window.location.search);
            const id_registro = params.get('id');
            window.open('generar_pdf.php?id=' + id_registro, '_blank');
        }
    </script>
</body>
</html>