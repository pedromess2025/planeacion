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
                <?php include 'encabezado.php'; ?>

                <div class="container py-4">
                    <div class="row">
                        <!-- DETALLE DEL EQUIPO -->
                        <div class="col-lg-5">
                            <div class="card card-order shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="header-accent">
                                            <small class="text-muted text-uppercase fw-bold">Folio de Servicio</small>
                                            <h4 id="folio" class="fw-bold text-primary"></h4>
                                        </div>
                                        <button type="button" class="btn btn-outline-warning" onclick="history.back()">
                                            <i class="bi bi-arrow-left"></i> Volver
                                        </button>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small text-muted text-uppercase fw-bold d-block">Ingeniero(s)</label>
                                        <div id="ingeniero" name="ingeniero"></div>
                                        <!--<label id="ingeniero" name="ingeniero" class="small text-muted text-uppercase fw-bold d-block">Ingeniero(s)</label>
                                        <small  id="noEmpleado" name="noEmpleado" class="d-block text-muted">No. Empleado:</small>-->
                                    </div>

                                    <div class="mb-3">
                                        <label id="equipo" name="equipo" class="small text-muted text-uppercase fw-bold d-block"></label>
                                        <small id="marca" name="marca" class="d-block text-muted"></small> 
                                        <small id="modelo" name="modelo" class="d-block text-muted"></small>
                                        <small id="serie" name="serie" class="d-block text-muted"></small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted text-uppercase fw-bold d-block">Notas Recepción</label>
                                        <p id="diagnostico" name="diagnostico" class="small text-dark bg-light p-3 rounded border" >Sin diagnóstico inicial</p>
                                    </div>

                                    <div class="mb-3">
                                        <label class="small text-muted text-uppercase fw-bold d-block">Notas Ing.</label>
                                        <p id="nota_ing" name="nota_ing" class="small text-dark bg-light p-3 rounded border">Sin nota inicial</p>
                                    </div>

                                    <label class="small text-muted text-uppercase fw-bold d-block mb-2">Fotos de Entrada</label>
                                    <div class="img-gallery">
                                        <!-- Fotos se cargarán aquí dinámicamente -->
                                    </div>
                                    <button type="button" id="btnVerFotos" class="btn btn-outline-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalFotos" style="display:none;">
                                        <i class="fas fa-image"></i> Ver fotos en carrusel
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
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small text-muted">CAMBIAR ESTATUS A:</label>
                                                    <select name="nuevo_estatus" class="form-select border-primary fw-bold">
                                                        <option value="DIAGNOSTICO" selected>🔍 En Diagnóstico</option>
                                                        <option value="REPARACION">🛠️ En Reparación</option>
                                                        <option value="REFACCIONES">📦 Espera de Refacciones</option>
                                                        <option value="CALIBRACION">⚖️ En Calibración</option>
                                                        <option value="TERMINADO">✅ Terminado</option>
                                                        <option value="SINENVIAR">🔚 Terminado Sin Enviar</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small text-muted">FECHA ACTUALIZACION:</label>
                                                    <input type="date" name="fecha_termino" class="form-control">
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

                                        <div class="d-flex justify-content-between align-items-center">
                                            <button type="button" class="btn btn-outline-primary px-5 fw-bold shadow-sm" onclick="guardarCambios(); return false;">
                                                Guardar Avance
                                            </button>
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
                        <span>Copyright &copy; MESS 2025</span>
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
                        $('#folio').text(equipo.folio || '#MET-0000-00');
                        
                        // Ingeniero
                        const nombresStr = equipo.ingeniero_nombre || equipo.nombres_ingenieros || 'Sin asignar';
                        // Dividir los nombres por comas y crear elementos separados
                        const nombresList = nombresStr.split(',').map(n => n.trim()).filter(n => n);
                        // Actualizar el contenedor con los nombres
                        $('#ingeniero').html(nombresList.map(nombre => `<small class="d-block text-dark">-${nombre}</small>`).join(''));
                        //$('#noEmpleado').text('No. Empleado(s): ' + (equipo.ids_ingenieros || 'N/A'));
                        
                        // Equipo
                        $('#equipo').text((equipo.marca || '') + ' ' + (equipo.modelo || '' ) + ' ' + (equipo.no_serie));      
                        $('#marca').text('Marca: ' + (equipo.marca || 'N/A'));
                        $('#modelo').text('Modelo: ' + (equipo.modelo || 'N/A'));
                        $('#serie').text('No. Serie: ' + (equipo.no_serie || 'N/A'));

                        // Diagnóstico
                        $('#diagnostico').text(equipo.notas_recepcion || 'Sin diagnóstico inicial');
                        
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

        // Funcion para guardar cambios
        function guardarCambios() {
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
            
            $.ajax({
                url: 'accionesEntradas.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Los cambios se han guardado correctamente.',
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            location.reload();
                        });
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
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor.',
                        confirmButtonText: 'Aceptar'
                    });
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
    </script>
</body>
</html>