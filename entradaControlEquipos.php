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
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="text-center mb-2">
                                <h2 class="fw-bold">Ingreso de Equipo</h2>
                            </div>

                            <div class="card card-minimal shadow-sm p-2">
                                <form id="entradaForm" method="POST" enctype="multipart/form-data">
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Cliente</label>
                                            <input type="text" name="cliente" class="form-control" placeholder="Nombre de la empresa" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Nombre del Contacto</label>
                                            <input type="text" name="nombre_cliente" class="form-control" placeholder="Nombre del contacto" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Teléfono de Contacto</label>
                                            <input type="text" name="contacto" class="form-control" placeholder="Teléfono" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label    small text-uppercase fw-bold text-muted">Correo de Contacto</label> 
                                            <input type="email" name="correo_cliente" class="form-control" placeholder="Correo electrónico" required>
                                        </div>                                        
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-sm-4 mb-0">
                                            <label for="slcRespoonsable" class="form-label small text-uppercase fw-bold text-muted">Ingeniero</label>
                                            <div id="Divsolicita" name="Divsolicita">
                                                <select id="slcRespoonsable" name="slcRespoonsable" class="form-select">
                                                    <option value="0">Selecciona...</option>
                                                </select>                                                    
                                            </div>
                                            <div id="Divsolicita2" name="Divsolicita2" style="display: none;">
                                                <select id="slcRespoonsable2" name="slcRespoonsable2" class="form-select">
                                                    <option value="0">Selecciona...</option>
                                                </select>
                                            </div>
                                            <div id="Divsolicita3" name="Divsolicita3" style="display: none;">
                                                <select id="slcRespoonsable3" name="slcRespoonsable3" class="form-select">
                                                    <option value="0">Selecciona...</option>
                                                </select>
                                            </div>
                                        </div>
                                            <div class="col-sm-1 mb-0">
                                                <label for="btnAgregar">+ Ing.</label>
                                                <div class="input-group">
                                                    <button id="btnAgregar" type="button" class="btn btn-sm btn-outline-success" onclick="divsIng('agrega')"><i class="fas fa-plus"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="divsIng('elimina')"><i class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Área</label>
                                            <select id="slcArea" name="area" class="form-select" required></select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Quién Envía</label>
                                            <select id="slcIngTrae" name="slcIngTrae" class="form-select">
                                                <option value="0">Selecciona...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-0">
                                            <div class="form-check form-switch">                                                
                                                <input name="demo" class="form-check-input" type="checkbox" role="switch" id="switchCheckDefault" value="1">                                                
                                                <label for="switchCheckDefault" class="form-label small text-uppercase fw-bold text-muted">Demo</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Marca</label>
                                            <input type="text" name="marca" class="form-control" placeholder="Marca" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Modelo</label>
                                            <input type="text" name="modelo" class="form-control" placeholder="Modelo">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold text-muted">No. Serie</label>
                                            <input type="text" name="no_serie" class="form-control" placeholder="N/S">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted">Notas de Recepción / Diagnóstico Preliminar</label>
                                        <textarea name="diagnostico_inicial" class="form-control" rows="2" placeholder="Describa el estado visual o falla reportada..." required></textarea>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Promesa de Entrega (Estimado)</label>
                                            <input type="date" name="fecha_estimada" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-muted">Fotos del Equipo</label>
                                            <input type="file" id="fotos" name="fotos[]" class="form-control" multiple accept="image/*" data-max-files="3" required>
                                            <small class="text-muted">Puedes seleccionar varias fotos del equipo reparado. Máximo 3 fotos. </small>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" class="btn btn-outline-success text-uppercase" onclick="guardarEntrada()">Registrar Entrada de Equipo</button>
                                        </div>                                        
                                    </div>

                                    <input type="hidden" name="estatus" value="ENTRADA">
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

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

    <!-- Logout Modal-->
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

    <script src="https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js" defer="defer"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        
        $(document).ready(function() {   
            //cargar empleados
            empleadoSolicita('#slcRespoonsable');
            empleadoSolicita('#slcRespoonsable2');
            empleadoSolicita('#slcRespoonsable3');
            cargarIngenierosTrae();
            //cargar areas
            cargarAreas();
            
            // Validar límite de fotos
            $('#fotos').on('change', function() {
                const maxFiles = parseInt($(this).data('max-files'));
                const files = this.files;
                
                if (files.length > maxFiles) {
                    Swal.fire({
                        title: "Límite de archivos excedido",
                        text: `Solo puedes seleccionar máximo ${maxFiles} fotos`,
                        icon: "warning"
                    });
                    this.value = ''; // Limpiar el input
                }
            });
            
            // Inicializar Select2 con búsqueda
            setTimeout(function() {
                $('#slcRespoonsable, #slcRespoonsable2, #slcRespoonsable3').select2({
                    placeholder: 'Buscar ingeniero...',
                    allowClear: true,
                    width: '100%'
                });
                $('#slcArea').select2({
                    placeholder: 'Buscar área...',
                    allowClear: true,
                    width: '100%'
                });
                $('#slcIngTrae').select2({
                    placeholder: 'Buscar ingeniero...',
                    allowClear: true,
                    width: '100%'
                });
            }, 500);
        });

        // FUNCION REGISTRAR ACTIVO
        function guardarEntrada() {
            // 1. Obtener el formulario HTML
            var formElement = document.getElementById('entradaForm');
            
            // 2. Validar formulario antes de enviar
            if (!formElement.checkValidity()) {
                formElement.reportValidity(); // Muestra mensajes de validación HTML5
                return false;
            }
            
            // 3. Crear objeto FormData (Captura automáticamente todos los inputs, selects y archivos)
            var formData = new FormData(formElement);

            // 4. Agregar datos manuales que no estén en inputs o que requieran lógica extra
            formData.append('accion', 'nuevaEntrada'); // Tu identificador para PHP

            // 5. Enviar vía AJAX
            $.ajax({
                url: 'accionesEntradas',
                method: 'POST',
                data: formData,         // Enviamos el objeto FormData directo
                
                // --- ESTAS DOS LÍNEAS SON OBLIGATORIAS PARA ARCHIVOS ---
                processData: false,     // Evita que jQuery transforme la data a string
                contentType: false,     // Evita que jQuery pongan cabeceras incorrectas
                // -------------------------------------------------------
                
                dataType: 'json',
                success: function(data) {
                    if (data.status === 'success') {
                        // Enviar notificación a los ingenieros asignados
                        if (data.id_entrada) {
                            $.ajax({
                                url: 'enviaNotificacionEntrada.php',
                                method: 'POST',
                                data: { id_entrada: data.id_entrada },
                                async: true // Enviar en background
                            });
                        }
                        
                        Swal.fire({
                            title: "¡Guardado!",
                            text: "La entrada se registró con éxito.",
                            icon: "success",
                            confirmButtonText: "Aceptar",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.href = 'entradaDetalleEntradas'; // Redirige a la lista de entradas
                                formElement.reset(); // Limpia el formulario
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.message,
                            icon: "error"
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error(textStatus, errorThrown);
                    Swal.fire({
                        title: "Error de Servidor",
                        text: "No se pudo registrar la entrada. Revise la consola.",
                        icon: "error"
                    });
                }
            });
        }

        function empleadoSolicita(seleccionado) {
            opcion = "empleados";
            $.ajax({
                url: 'acciones_solicitud.php',
                method: 'POST',
                dataType: 'json',
                data: {opcion},
                success: function(data) {
                    var select = $(seleccionado);
                    i = 0;
                    data.forEach(function(usuarios) {
                        if (i = 0) {
                            var option = $('<option></option>').attr('value', '0').text('Selecciona...');
                            select.append(option);
                        }
                        var option = $('<option></option>').attr('value', usuarios.noEmpleado).text(usuarios.nombre);
                        select.append(option);
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        title: "La solicitúd no se pudo procesar!",
                        icon: "error",
                        draggable: true
                    });
                }
            });
        }

        function cargarIngenierosTrae() {
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: { accion: 'obtenerIngenieros' },
                success: function(response) {
                    var select = $('#slcIngTrae');
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

        // Funcion para agregar o eliminar divs de ingenieros
        function divsIng(accion) {
            if (accion === 'agrega') {
                if ($('#Divsolicita2').is(':hidden')) {
                    $('#Divsolicita2').show();
                } else if ($('#Divsolicita3').is(':hidden')) {
                    $('#Divsolicita3').show();
                } else {
                    Swal.fire({
                        title: "Solo puedes agregar hasta 3 ingenieros",
                        icon: "warning",
                        draggable: true
                    });
                }
            } else if (accion === 'elimina') {
                if ($('#Divsolicita3').is(':visible')) {
                    $('#Divsolicita3').hide();
                    $('#slcRespoonsable3').val('0');
                    $('#slcRespoonsable2').val('0');
                } else if ($('#Divsolicita2').is(':visible')) {
                    $('#Divsolicita2').hide();
                    $('#slcRespoonsable2').val('0');
                    $('#slcRespoonsable3').val('0');
                } else {
                    $('#slcRespoonsable2').val('0');
                    $('#slcRespoonsable3').val('0');
                    Swal.fire({
                        title: "No hay más ingenieros para eliminar",
                        icon: "warning",
                        draggable: true
                    });
                }
            }
        }
        
        //Funcion para cargar areas
        function cargarAreas() {
            $.ajax({
                url: 'accionesEntradas.php',
                method: 'POST',
                dataType: 'json',
                data: {accion: "obtenerAreas"},
                success: function(data) {
                    var select = $('select[name="area"]');
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

        // Función para convertir texto a mayúsculas y quitar acentos
        function convertirTexto(e) {
            // Convertir a mayúsculas y quitar acentos
            e.value = e.value
            .toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        }

        // Función para obtener el valor de una cookie por su nombre
        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
        }

    </script>
</body>

</html>
