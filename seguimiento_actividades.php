
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
                                            <img class="sidebar-card-illustration" src="img/MESS_05_Imagotipo_1.png" width="160">
                                        </div>
                                        <div class="col-sm-8">                                            
                                                <p class="fs-4"><b>SEGUIMIENTO ACTIVIDADES</b></p>                                            
                                        </div>
                                    </div>
                                    <hr>                                    
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
                        <div class="row card-footer border-left-primary">
                            <input type="hidden" class="form-control form-control-sm" id="idActividad" name="idActividad">
                            <div class="col-sm-12 mb-0">
                                <label for="slcRespoonsable">Ingeniero</label>
                                <div id="Divsolicita" name="Divsolicita">
                                    <select id="slcRespoonsable" name="slcRespoonsable">
                                        <option value="">Selecciona...</option>
                                    </select>
                                </div>
                            </div>                                                                       
                        </div>

                        <div class="row card-footer border-left-primary">
                            <div class="col-sm-6 mb-0">
                                <label for="txtOT">OT</label>
                                <input type="text" class="form-control form-control-sm" id="txtOT" name="txtOT">
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
                                        <option value="Sevicioconfirmadoparasuejecucion">Sevicio confirmado para su ejecución</option>
                                        <option value="Fechareservadasininformación">Fecha reservada sin información</option>
                                        <option value="Cancelar">Cancelar</option>
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
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js" defer="defer"></script>
                
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--FUNCNIONES JS DE INCIDENCIAS-->
    <script src="funciones_incidencias.js" defer="defer"></script>
    <script type="text/javascript">
        $(document).ready(function() {                   
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
                    responsive: true
                }); 

                // Mostrar inicialmente las solicitudes abiertas
                SolicitudesAbiertas();

                // Cargar los vehículos en el select        
                cargarVehiculos();
                // Cargar los empleados en el select            
                empleadoSolicita(); 



        });

        function empleadoSolicita() {
            opcion = "empleados";
            $.ajax({
                url: 'acciones_solicitud.php',
                method: 'POST',
                dataType: 'json',
                data: {opcion},
                success: function(data) {
                    var select = $('#slcRespoonsable');
                    data.forEach(function(usuarios) {
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
        
        function cargarVehiculos() {
        //FUNCION PARA CARGAR INFORMACIÓN DE LOS VEHÍCULOS        
            $.ajax({
                type: "POST",
                url: "acciones_solicitud.php",
                data: { opcion: "consultarInventarioGeneral" },
                dataType: "json",
                success: function (respuesta) {
                    var select = $("#slcAutomovil");
                    
                    respuesta.forEach(function (vehiculo) {
                        // Define el color según el valor de vehiculo.usuario
                        let color = "";
                        if(vehiculo.tipo === 'AREA') {
                                color = "background-color: #ffeeba;";
                        } else if(vehiculo.tipo === 'EXTERNO') {
                                color = "background-color:rgb(186, 201, 255);";
                        }
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
