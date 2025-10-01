<!DOCTYPE html>

<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PLANEACION</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .quitarbarra {
            width: 100%;
            clip-path: polygon(0% 0%, 100% 0%, 100% calc(100% - 37px), 0% calc(100% - 37px));
        }
    </style>

<?php
    $usuariosRegistran = array(212, 14, 42, 161, 403, 183, 521, 276, 26, 147, 189, 177, 45, 26, 525, 435);

    if (in_array($_COOKIE['noEmpleado'], $usuariosRegistran)) {
        // El usuario tiene permiso para ver la página
    } else {
        header("Location: seguimiento_actividades.php");
    }
?>
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
                                <div class="card-body   card-header">
                                    <div class="row">   
                                        <div class="col-xl-12 mb-0" style="text-align: center">
                                            <center>
                                                <h4>REGISTRO DE ACTIVIDADES</h4>
                                            </center>
                                        </div>
                                    </div>
                                    <form id="formPlaneacion" name="formPlaneacion">
                                        <div class="row">
                                            <div class="col-sm-6 mb-0">
                                                <label for="slcRespoonsable">Ingeniero</label>
                                                <div id="Divsolicita" name="Divsolicita">
                                                    <select id="slcRespoonsable" name="slcRespoonsable">
                                                        <option value="0">Selecciona...</option>
                                                    </select>                                                    
                                                </div>
                                                <div id="Divsolicita2" name="Divsolicita2" style="display: none;">
                                                    <select id="slcRespoonsable2" name="slcRespoonsable2">
                                                        <option value="0">Selecciona...</option>
                                                    </select>
                                                </div>
                                                <div id="Divsolicita3" name="Divsolicita3" style="display: none;">
                                                    <select id="slcRespoonsable3" name="slcRespoonsable3">
                                                        <option value="0">Selecciona...</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-2 mb-0">
                                                <label for="btnAgregar">+ Ing.</label>
                                                <div class="input-group">
                                                    <button id="btnAgregar" type="button" class="btn btn-sm btn-outline-success" onclick="divsIng('agrega')"><i class="fas fa-plus"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning" onclick="divsIng('elimina')"><i class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-0">
                                                <label for="slcAreas">Área:</label>
                                                <select  name="slcAreas" id="slcAreas" class="form-select mr-5">
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
                                                    
                                                    <!---
                                                    <option value="FM">FM Ensayos de Torque</option>
                                                    <option value="PTSL">PTSL Par Torsional SLP</option>
                                                    <option value="FZSL">FZSL Fuerza SLP</option>
                                                    <option value="MMM">MMM Servicios Mitutoyo</option>                                                    
                                                    <option value="BR">BR Servicios Movil</option>
                                                    <option value="LS">LS Laser Tracker</option>
                                                    <option value="MT">MT Servicios MT</option>                                                                                                        
                                                    <option value="OPT">OPT Servicios SFG</option>
                                                    <option value="MO">MO Servicio de Microscopio</option>
                                                    <option value="SFGM">SFGM Servicios SFG</option>
                                                    <option value="MI">MI Calibración de Microscopio</option>
                                                    <option value="EV">EV Equipos de Visión</option>
                                                    <option value="OPTM">OPTM Calibración Comparador</option>
                                                    <option value="MV">MV Conteo de Partículas</option>
                                                    <option value="EVM">EVM Equipos de Visión</option>
                                                    <option value="SC">SC Servicio a ScanMax</option>                                                                                                        
                                                    <option value="AE">AE Alta Exactitud</option>                                                    
                                                    <option value="DISL">DISL Dimensional SLP</option>
                                                    <option value="FIX">FIX Fixtures</option>
                                                    <option value="AM">AM Dimensional Sitio</option>                                                                                                        
                                                    <option value="QU">QU Medidor de PH</option>
                                                    <option value="ELC">ELC Electrica</option>
                                                    <option value="TI">TI Temperatura</option>
                                                    <option value="TEM">TEM Temperatura</option>
                                                    <option value="CRF">CRF Servicios Ingenieria Inversa</option>
                                                    <option value="CNMD">CNMD Digitalización</option>
                                                    <option value="MG">MG Metalografía</option>
                                                    <option value="VF">VF Volumen</option>
                                                    <option value="PRSL">PRSL Presión SLP</option>
                                                    <option value="ZEISS">ZEISS Servicio Zeiss</option>
                                                    <option value="LE">LE Laboratorio especialidades</option>                                                    
                                                    <option value="BW">BW BW</option>
                                                    <option value="D">D D</option>
                                                    <option value="DINL">DINL Dimensional SLP</option>
                                                    <option value="DMTY">DMTY Dimensional MTY</option>
                                                    <option value="ELSL">ELSL Electrica SLP</option>
                                                    <option value="HU">HU Humedad</option>
                                                    <option value="II">II Ingenieria Inversa</option>
                                                    <option value="LDISL">LDISL Dimensional SLP</option>
                                                    <option value="LDM">LDM LDM</option>
                                                    <option value="ME">ME Mediciones Especiales</option>                                                    
                                                    <option value="TF">TF Tiempo y Frecuencia</option> -->
                                                </select>
                                            </div>                                                                                        
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-4 mb-0">
                                                <label for="txtCliente">Cliente</label>
                                                <div id="DivCliente" name="DivCliente">
                                                    <input type="text" class="form-control form-control-sm" oninput="convertirTexto(this)" id="txtCliente" name="txtCliente" placeholder="Cliente">
                                                </div>
                                            </div>
                                            <div class="col-sm-4 mb-0">
                                                <label for="txtCiudad">Ciudad</label>
                                                <div id="DivCiudad" name="DivCiudad">
                                                    <select id="txtCiudad" name="txtCiudad">
                                                        <option value="">Selecciona...</option>
                                                    </select>                                                    
                                                </div>
                                            </div>
                                            <div class="col-sm-4 mb-0">
                                                <label for="txtOT">OT</label>
                                                <input type="text" class="form-control form-control-sm" id="txtOT" name="txtOT" oninput="convertirTexto(this)" placeholder="Ej. EL25-01E-1">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-3 mb-0">
                                                <label for="datefechaCierre">Fecha planeada</label>
                                                <input type="datetime-local" class="form-control form-control-sm" id="datefechaCierre" name="datefechaCierre">
                                            </div>
                                            <div class="col-sm-3 mb-0">
                                                <label for="txtDuracion">Dur. Estimada</label>
                                                <input type="number" class="form-control form-control-sm" id="txtDuracion" name="txtDuracion" placeholder="Horas servicio">
                                            </div>
                                            <div class="col-sm-3 mb-0">
                                                <label for="txtDuracion">Duración Viaje</label>
                                                <input type="number" class="form-control form-control-sm" id="txtDuracionViaje" name="txtDuracionViaje" placeholder="Horas viaje">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-5">
                                                <label for="slcAutomovil">Automovil</label>
                                                <div id="DivAutomovil" name="DivAutomovil">
                                                    <select id="slcAutomovil" name="slcAutomovil" class="form-select">
                                                        <option value="">Selecciona...</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="slcEstatus">Estatus</label>
                                                <div id="DivEstatus" name="DivEstatus">
                                                    <select id="slcEstatus" name="slcEstatus" class="form-select" onchange="mostrarMensajeEstatus()">
                                                        <option value="">Selecciona...</option>
                                                        <option value="Pendientedeinformacion">Pendiente de información</option>
                                                        <option value="Programadasinconfirmar">Programada sin confirmar</option>
                                                        <option value="Servicioconfirmadoparasuejecucion">Servicio confirmado para su ejecución</option>
                                                        <option value="Fechareservadasininformación">Fecha reservada sin información</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="badge text-bg-secondary" id="mensajeEstatus" style="display: none" name="mensajeEstatus"></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-3"></div>
                                            <div class="col-xl-6 mb-1   ">
                                                <center>
                                                    <button id="btnSolicitar" type="button" class="btn btn-success" onclick="generarSolicitud()">Registrar</button><br>
                                                    <p id="mensaje" class="badge text-bg-primary"></p>
                                                </center>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-sm-12 mb-0">
                                            <embed class="quitarbarra" id="vistaPrevia" src='https://app.powerbi.com/view?r=eyJrIjoiODJkZWY0MjQtODQxNC00YWJmLWIzOWMtMThhYTEyODdmZmMwIiwidCI6ImZlMGNmZmU4LTkxMjYtNGRmYS1iNjE2LTU3MGM2YWViYTdiNiJ9&pageName=4c96c5accec6d9000806' type="application/pdf" width="100%" height="500px" />
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
            // Cargar la información al iniciar la página
            //cargar ciudades
            cargarCiudades();
            //cargar empleados
            empleadoSolicita('#slcRespoonsable');
            empleadoSolicita('#slcRespoonsable2');
            empleadoSolicita('#slcRespoonsable3');
            //cargar automoviles
            cargarVehiculos();

            // Inicializa Select2 en el campo de responsable
            $('#slcRespoonsable').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });
            $('#slcRespoonsable2').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });
            $('#slcRespoonsable3').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });
            $('#slcAreas').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });
            $('#slcCiudad').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });
            $('#slcAutomovil').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });
            $('#slcEstatus').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });
            $('#txtCiudad').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });

            
        });

        function generarSolicitud() {            
            
            var formData = getFormData('formPlaneacion');
            var responsable = formData["slcRespoonsable"];
            var responsable2 = formData["slcRespoonsable2"];
            var responsable3 = formData["slcRespoonsable3"];
            
            //validacion de que no se repitan los responsables
            if (responsable !== "0" && (responsable === responsable2 || responsable === responsable3 || (responsable2 !== "0" && responsable2 === responsable3))) {
                Swal.fire({
                    title: "Los ingenieros seleccionados no pueden ser los mismos. Por favor, elige ingenieros diferentes.",
                    icon: "warning",
                    draggable: true
                });
                return; // Detiene la ejecución del código de envío/guardado.
            }

            var area = formData["slcAreas"];
            var ciudad = formData["txtCiudad"];
            var cliente = formData["txtCliente"];
            var ot = formData["txtOT"];
            var fechaPlaneada = formData["datefechaCierre"];
            var duracion = formData["txtDuracion"];
            var duracionViaje = formData["txtDuracionViaje"];
            var automovil = formData["slcAutomovil"];
            var estatus = formData["slcEstatus"];    
            
            if (!validarFormularioConsolidado(formData)) {
                // La función ya mostró la alerta con todos los errores.
                return; // Detiene la ejecución del código de envío/guardado.
            }
            
            $.ajax({
                url: 'acciones_solicitud.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    opcion: 'generarSolicitud',
                    responsable: responsable,
                    responsable2: responsable2,
                    responsable3: responsable3,
                    area: area,
                    ciudad: ciudad,
                    cliente: cliente,
                    ot: ot,
                    fechaPlaneada: fechaPlaneada,
                    duracion: duracion,
                    duracionViaje: duracionViaje,
                    automovil: automovil,
                    estatus: estatus
                },
                success: function(data) {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: "La actividad se registró con éxito!",
                            icon: "success",
                            draggable: true
                        });
                        // Redirigir
                        window.location.href = 'seguimiento_actividades.php';

                    } else {
                        Swal.fire({
                            title: "Error al registrar la actividad: " + data.message,
                            icon: "error",
                            draggable: true
                        });
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        title: "La actividad no se pudo registrar!",
                        icon: "error",      
                        draggable: true
                    });
                }
            });
            
        }

        // Validaciones consolidadas
        function validarFormularioConsolidado(formData) {
    
            // --- 1. Mapeo de Campos, Valores, IDs y Mensajes ---
            // Define todos los campos que requieren validación.
            const camposAValidar = [
                // Campos que no pueden ser cadena vacía ("")
                { valor: formData["slcAreas"],          mensaje: "Selecciona un área" },
                { valor: formData["txtCiudad"],         mensaje: "Selecciona una ciudad" },
                //{ valor: formData["txtDuracion"],       mensaje: "Ingresa la duración estimada" },
                //{ valor: formData["txtDuracionViaje"],  mensaje: "Ingresa la duración del viaje" },
                { valor: formData["slcAutomovil"],      mensaje: "Selecciona un automóvil" },
                { valor: formData["slcEstatus"],        mensaje: "Selecciona un estatus" },
                { valor: formData["datefechaCierre"],   mensaje: "Selecciona una fecha planeada" },
                
                // Campos de texto que deben validarse con .trim() para evitar solo espacios
                { valor: formData["txtCliente"],        mensaje: "Ingresa un cliente",     esTexto: true },
                //{ valor: formData["txtOT"],             mensaje: "Ingresa una OT",         esTexto: true },
            ];

            const mensajesDeError = [];
            
            // --- 2. Validar Responsables (Lógica Especial) ---
            // Regla: Al menos uno de los tres campos de responsable debe tener un valor diferente de "0" o "".
            const resp1 = formData["slcRespoonsable"];
            const resp2 = formData["slcRespoonsable2"];
            const resp3 = formData["slcRespoonsable3"];
            
            // La condición 'esVacio' es TRUE si TODOS están vacíos o en "0"
            const responsablesVacios = (resp1 === "0" || resp1 === "") && 
                                    (resp2 === "0" || resp2 === "") && 
                                    (resp3 === "0" || resp3 === "");

            if (responsablesVacios) {
                mensajesDeError.push("Debes seleccionar **al menos un ingeniero**");
            }

            // --- 3. Validar Campos Generales ---
            for (const campo of camposAValidar) {
                let valor = campo.valor || ""; // Asegura que el valor sea una cadena para la comprobación
                
                if (campo.esTexto) {
                    // Validar campos de texto con trim()
                    if (valor.trim() === "") {
                        mensajesDeError.push(campo.mensaje);
                    }
                } else {
                    // Validar selects y otros campos por cadena vacía
                    if (valor === "" || valor === "0") { // Incluimos "0" por si son selects de IDs
                        mensajesDeError.push(campo.mensaje);
                    }
                }
            }

            // --- 4. Mostrar Alerta Única o Continuar ---
            if (mensajesDeError.length > 0) {
                Swal.fire({
                    title: "Campos Incompletos",
                    html: "Por favor, corrige lo siguiente:<br><br>• " + mensajesDeError.join('<br>• '),
                    icon: "warning",
                    draggable: true
                });
                return false; // Validación fallida
            }

            return true; // Validación exitosa
        }

        function getFormData(formId) {
            var formArray = $('#' + formId).serializeArray();
            var formData = {};
            formArray.forEach(function(item) {
                formData[item.name] = item.value;
            });
            return formData;
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

        function cargarCiudades() {
            //FUNCION PARA CARGAR INFORMACIÓN DE LAS CIUDADES        
            $.ajax({
                type: "POST",
                url: "acciones_solicitud.php",
                data: { opcion: "consultarCiudades" },
                dataType: "json",
                success: function (respuesta) {
                    var select = $("#txtCiudad");
                    
                    respuesta.forEach(function (ciudad) {
                        var option = `<option value="${ciudad.ciudad}"><b>${ciudad.estado}</b>  -  ${ciudad.ciudad}</option>`;
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

        function convertirTexto(e) {
            // Convertir a mayúsculas y quitar acentos
            e.value = e.value
            .toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
        }

        function getCookie(name) {
            let value = "; " + document.cookie;
            let parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
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
        //Funcion para mostrar mensaje segun estatus
        function mostrarMensajeEstatus() {
            var estatus = $('#slcEstatus').val();
            var mensajeEstatus = $('#mensajeEstatus');

            if (estatus === 'Pendientedeinformacion') {
                mensajeEstatus.text('Espera información del cliente');
                mensajeEstatus.show();
            } else if (estatus === 'Programadasinconfirmar') {
                mensajeEstatus.text('Fecha tentativa, espera confirmación del cliente');
                mensajeEstatus.show();
            } else if (estatus === 'Servicioconfirmadoparasuejecucion') {
                mensajeEstatus.text('Servicio confirmado, listo para ejecutar');
                mensajeEstatus.show();
            } else if (estatus === 'Fechareservadasininformación') {
                mensajeEstatus.text('Fecha reservada, falta información del cliente(OV/OT/PO)');
                mensajeEstatus.show();
            } else {
                mensajeEstatus.hide();
            }
        }

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
    </script>
</body>

</html>
