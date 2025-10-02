<?php
header('Access-Control-Allow-Origin: *');
?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <title>PLANEACIÓN</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" rel="stylesheet" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
                <div class="container-fluid">
                    <!-- Content Row -->
                    <div class="row">
                        <!-- Area Chart -->
                        <div class="col-xl-12">
                            <div class="card shadow">
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="row"> 
                                        <div class="col-sm-4" style="text-align: center">
                                            
                                        </div>
                                        <div class="col-sm-8">                                            
                                                <p class="fs-4"><b>SEGUIMIENTO ACTIVIDADES</b></p>                                            
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <label for="filtro-area" class="mr-2">Filtrar por Área:</label>
                                            <select id="filtro-area" class="form-select mr-3" multiple="multiple" name="areas[]">
                                                <option value="">Todas las áreas</option> 
                                                <option value="ALTA EXACITUD">Servicios Alta Exactitud</option> 
                                                <option value="CALIBRACIONES">Servicios Calibraciones</option>
                                                <option value="DIMENSIONAL">Servicios Dimensional</option>
                                                <option value="SFG">Servicios SFG</option>
                                                <option value="MITUTOYO">Servicios Mitutoyo</option>
                                                <option value="DUREZA">Servicios Dureza</option>
                                                <option value="MANTENIMIENTO">Servicios Mantenimiento</option>
                                                <option value="ELECTRICA">Servicios Electrica</option>
                                                <option value="TEMPERATURA">Servicios Temperatura</option>
                                                <option value="PRESION">Servicios Presion</option>
                                                <option value="APLICACIONES">Servicios APP Aplicaciónes</option>
                                                <option value="MT">Servicios MT</option>
                                                <option value="MTS">Servicios MTS</option>
                                                <option value="ZEISS">Servicios Zeiss</option>
                                                <option value="MASA">Servicios Masa</option>
                                                <option value="FUERZA">Servicios Fuerza</option>
                                                <option value="PAR TORSIONAL">Servicios Par Torsional</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filtro-ingeniero" class="mr-2">Filtrar por Ingeniero:</label>
                                            <select id="filtro-ingeniero" name="ingeniero[]" class="form-select mr-3" multiple="multiple">
                                                <option value="0">Selecciona...</option>
                                            </select>                             
                                        </div>
                                        <div class="col-sm-4 mb-0">
                                            <label for="filtro-ciudad">Ciudad</label>
                                            <div id="DivCiudad" name="DivCiudad">
                                                <select id="filtro-ciudad" name="ciudad[]" class="form-select  mr-3" multiple="multiple">
                                                    <option value="">Selecciona...</option>
                                                </select>                                                    
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button class="btn btn-primary btn-md w-100" style="margin-top: 24px;" onclick="SolicitudesAbiertas()">Aplicar filtro</button>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-12">

                                            <!-- Representa la tabla de solicitudes Abiertas.-->
                                            <table id="TSolAbiertas" name="TSolAbiertas" class="table table-hover table-striped table-bordered" style="width:100%">
                                                <thead class="table-primary">
                                                    <tr>                                                        
                                                        <th>Ingeniero</th>
                                                        <th>Area</th>
                                                        <th>OT</th>
                                                        <th>Fecha Actividad</th>
                                                        <th>Cliente</th>
                                                        <th>Ciudad</th>
                                                        <th>Vehiculo</th>
                                                        <th>Estatus</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
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

    <!-- Modal para responder incidencias -->
    <div class="modal fade" id="actualizarActividadModal" tabindex="-1" aria-labelledby="actualizarActividadLabel" aria-hidden="true">
        <div class="modal-dialog">            
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="actualizarActividadLabel">Actualizar Actividad</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row card-footer border-left-primary mb-3">
                            <input type="hidden" class="form-control form-control-sm" id="idActividad" name="idActividad">
                            <div class="col-sm-9 mb-0">
                                <label for="slcRespoonsable">Ingeniero</label>
                                <div id="Divsolicita" name="Divsolicita">
                                    <select id="slcRespoonsable" name="slcRespoonsable">
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3 mb-0">
                                <label for="slcRespoonsable">Ing.</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="divsIng('agrega')"><i class="fas fa-plus"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="divsIng('elimina')"><i class="fas fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="col-sm-12 mb-0">                                
                                <div id="Divsolicita2" name="Divsolicita2">
                                    <label for="slcRespoonsable2">Ingeniero 2</label>
                                    <select id="slcRespoonsable2" name="slcRespoonsable2">
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 mb-0">                                
                                <div id="Divsolicita3" name="Divsolicita3">
                                    <label for="slcRespoonsable3">Ingeniero 3</label>
                                    <select id="slcRespoonsable3" name="slcRespoonsable3">
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                            </div>  
                        </div>

                        <div class="row card-footer border-left-primary">
                            <div class="col-sm-6 mb-0">
                                <label for="txtOT">OT</label>
                                <input type="text" class="form-control form-control-sm" id="txtOT" name="txtOT" placeholder="Ej. EL25-01E-1">
                            </div>
                            <div class="col-sm-6 mb-0">
                                <label for="datefechaCierre">Fecha planeada</label>
                                <input type="datetime-local" class="form-control form-control-sm" id="datefechaCierre" name="datefechaCierre">
                            </div>                            
                        </div>

                        <div class="row card-header border-left-primary">                                           
                            <div class="col-sm-6">
                                <label for="slcAutomovil">Automovil</label>
                                <div id="DivAutomovil" name="DivAutomovil">
                                    <select id="slcAutomovil" name="slcAutomovil" class="form-select">
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="slcEstatus">Estatus</label>
                                <div id="DivEstatus" name="DivEstatus">
                                    <select id="slcEstatus" name="slcEstatus" class="form-select">
                                        <option value="">Selecciona...</option>
                                        <option value="Pendientedeinformacion">Pendiente de información</option>
                                        <option value="Programadasinconfirmar">Programada sin confirmar</option>
                                        <option value="Servicioconfirmadoparasuejecucion">Sevicio confirmado para su ejecución</option>
                                        <option value="Fechareservadasininformación">Fecha reservada sin información</option>
                                        <option value="Cancelada">Cancelar</option>
                                        <option value="Cerrada">Cerrar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="ActualizarActividad()">Actualizar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            
        </div>
    </div>

    <!-- Bootstrap core JavaScript
    <script src = "vendor/jquery/jquery.min.js"></script>-->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>    
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js" defer="defer"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js" defer="defer"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.0/js/dataTables.responsive.min.js"></script>
                
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--FUNCNIONES JS DE INCIDENCIAS-->
    <script src="funciones_incidencias.js" defer="defer"></script>
    <script type="text/javascript">
        $(document).ready(function() {                             
                // Manejar el estado activo de los botones
                $('#statusBtnGroup .btn').on('click', function() {
                    $('#statusBtnGroup .btn').removeClass('active');
                    $(this).addClass('active');
                });
                
                // Aplica el estilo a ambas tablas
                $('#TSolAbiertas').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json"
                    },
                    "order": [[ 3, "desc" ]],
                    "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    "pageLength": 10,
                    "responsive": true,
                    "searching": false                    
                }); 

                // Mostrar inicialmente las solicitudes abiertas
                SolicitudesAbiertas();

                // Cargar los vehículos en el select        
                cargarVehiculos('#slcAutomovil');

                // Cargar los empleados en el select            
                empleadoSolicita('#slcRespoonsable');
                empleadoSolicita('#slcRespoonsable2');
                empleadoSolicita('#slcRespoonsable3');
                empleadoSolicita('#filtro-ingeniero');

                cargarCiudades();
                
                // Inicializar Select2
                $('#filtro-area').select2({
                    placeholder: "Selecciona una o varias áreas", // Opcional: un texto de ayuda
                    allowClear: true // Opcional: permite deseleccionar todo
                });
                $('#filtro-ciudad').select2({            
                    placeholder: "Seleccione una o varias ciudades", // Opcional: un texto de ayuda
                    allowClear: true // Opcional: permite deseleccionar todo
                });
                $('#filtro-ingeniero').select2({            
                    placeholder: "Seleccione uno o varios ingenieros", // Opcional: un texto de ayuda
                    allowClear: true // Opcional: permite deseleccionar todo
                });


        });

    
        function empleadoSolicita(selectIng) {
            opcion = "empleados";
            $.ajax({
                url: 'acciones_solicitud.php',
                method: 'POST',
                dataType: 'json',
                data: {opcion},
                success: function(data) {
                    var select = $(selectIng);
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

        function convertirTexto(e) {
            // Convertir a mayúsculas y quitar acentos
            e.value = e.value
            .toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        }
        
        function cargarVehiculos(selectVehiculos) {
        //FUNCION PARA CARGAR INFORMACIÓN DE LOS VEHÍCULOS        
            $.ajax({
                type: "POST",
                url: "acciones_solicitud.php",
                data: { opcion: "consultarInventarioGeneral" },
                dataType: "json",
                success: function (respuesta) {
                    var select = $(selectVehiculos);
                    
                    respuesta.forEach(function (vehiculo) {
                        // Define el color según el valor de vehiculo.usuario
                        let color = "";
                        if(vehiculo.tipo === 'AREA') {
                                color = "background-color: #ffeeba;";
                        } else if(vehiculo.tipo === 'EXTERNO') {
                                color = "background-color:rgb(186, 201, 255);";
                        }

                        var optionOtro = `<option value="Otro">Otro</option>`;
                    select.append(optionOtro);
                    var optionNa = `<option value="N/A">No Aplica</option>`;
                    select.append(optionNa);

                        var option = `<option value="${vehiculo.placa}" style="${color}">${vehiculo.modelo} - ${vehiculo.placa} - Usr: ${vehiculo.usuario}</option>`;
                        select.append(option);
                    });
                },
                error: function (xhr, status, error) {
                    
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al cargar los datos.",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }
        


    </script>
</body>

</html>
