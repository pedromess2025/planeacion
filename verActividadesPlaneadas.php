<?php
    session_start();
    include 'conn.php';
    if($_COOKIE['noEmpleado'] == '' || $_COOKIE['noEmpleado'] == null){
        echo '<script>window.location.assign("index")</script>';
    }
?>
<!DOCTYPE html>
<html lang = "en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PLANEACION SEMANAL</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.css">
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body id = "page-top">

    <!-- Page Wrapper -->
    <div id = "wrapper">
        <?php
            //session_start();
            //if(isset($_SESSION['nombredelusuario'])){}
            
            include 'menu.php';
        ?>

        

        <!-- Content Wrapper -->
        <div id = "content-wrapper" class = "d-flex flex-column">

            <!-- Main Content -->
            <div id = "content">
            
                <?php
                    //session_start();
                    //if(isset($_SESSION['nombredelusuario'])){}
                    
                    include 'encabezado.php';
                ?>
<!-- Begin Page Content -->
                <div class="container-fluid">                    
                    <h1>Calendario de Actividades Planeadas</h1>

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
                            <label for="txtCiudad">Ciudad</label>
                            <div id="DivCiudad" name="DivCiudad">
                                <select id="filtro-ciudad" name="ciudad[]" class="form-select  mr-3" multiple="multiple">
                                    <option value="">Selecciona...</option>
                                </select>                                                    
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-md w-100" style="margin-top: 24px;" onclick="filtrar()">Aplicar filtro</button>
                        </div>
                    </div>
                    <br><br>
                    <!-- PLANEADAS -->
                    <div id="divPlaneadas">                        
                        <div id="calendarioActividadesPlaneadas" name="calendarioActividadesPlaneadas"></div>            
                    </div>                        
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class = "sticky-footer bg-white">
                <div class = "container my-auto">
                    <div class = "copyright text-center my-auto">
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
    <a class = "scroll-to-top rounded" href = "#page-top">
        <i class = "fas fa-angle-up"></i>
    </a>
    <!-- Bootstrap core JavaScript-->
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
    <!-- DataTables JavaScript -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Popper.js -->
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.10.1/locales/es.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {            
            filtrar();
            cargarCiudades();
            cargarIngenieros();
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
        
        function mostrarCalendarioActividadesPlaneadas() {
            // Limpiar el contenedor antes de agregar el calendario
            $('#calendarioActividadesPlaneadas').empty();

            // Crear un array de eventos para FullCalendar
            $.ajax({
                url: 'acciones_calendario.php',
                type: 'POST',
                data: { accion: 'ActividadesCalendarioPlaneadas' },
                dataType: 'json',
                success: function(data) {
                    if (data.status === 'success') {
                        var eventos = [];
                        $.each(data.actividades, function(index, actividad) {
                            // Definir color según el estatus
                            var colorEvento = '';
                            // Extraer el área de order_code usando substring antes del primer '-'                            
                            areaOT =actividad.area;// actividad.order_code.substring(0, actividad.order_code.indexOf('-')).replace('25', '');
                            

                            switch (areaOT) {
                                case 'MMZ': colorEvento = '#b08805'; break;    // Amarillo (oscurecido)
                                case 'FZ': colorEvento = '#b06b00'; break;     // Naranja (oscurecido)
                                case 'MG': colorEvento = '#a11646'; break;     // Rosa (oscurecido)
                                case 'BR': colorEvento = '#573d32'; break;     // Café (oscurecido)
                                case 'LC': colorEvento = '#1b722f'; break;     // Verde (oscurecido)
                                case 'MT': colorEvento = '#008692'; break;     // Cyan (oscurecido)
                                case 'MTS': colorEvento = '#62872f'; break;    // Verde claro (oscurecido)
                                case 'EV': colorEvento = '#6e1c7d'; break;     // Morado (oscurecido)
                                case 'DU': colorEvento = '#42565e'; break;     // Azul grisáceo (oscurecido)
                                case 'MO': colorEvento = '#2d397e'; break;     // Azul (oscurecido)
                                case 'MMM': colorEvento = '#ae2e26'; break;    // Rojo (oscurecido)
                                case 'SFG': colorEvento = '#929900'; break;    // Lima (oscurecido)
                                case 'DISL': colorEvento = '#b8a92b'; break;   // Amarillo claro (oscurecido)
                                case 'LE': colorEvento = '#827f4c'; break;     // Caqui (oscurecido)
                                case 'PT': colorEvento = '#00b800'; break;     // Verde neón (oscurecido)
                                case 'OPT': colorEvento = '#006c61'; break;    // Verde azulado (oscurecido)
                                case 'TE': colorEvento = '#176bb0'; break;     // Azul claro (oscurecido)
                                case 'MA': colorEvento = '#b33e18'; break;     // Naranja fuerte (oscurecido)
                                case 'APP': colorEvento = '#49287f'; break;    // Morado oscuro (oscurecido)
                                case 'LS': colorEvento = '#7c8690'; break;     // Gris azulado (oscurecido)
                                case 'PR': colorEvento = '#9b252f'; break;     // Rojo oscuro (oscurecido)
                                case 'FM': colorEvento = '#42562a'; break;     // Verde oliva (oscurecido)
                                case 'EL': colorEvento = '#b39800'; break;     // Dorado (oscurecido)
                                case 'MI': colorEvento = '#357335'; break;     // Verde medio (oscurecido)
                                case 'AX': colorEvento = '#b38000'; break;     // Amarillo anaranjado (oscurecido)
                                case 'PRSL': colorEvento = '#760e3c'; break;   // Rosa oscuro (oscurecido)
                                case 'FZSL': colorEvento = '#b34c2e'; break;   // Naranja suave (oscurecido)
                                case 'D': colorEvento = '#434343'; break;      // Gris oscuro (oscurecido)
                                case 'PTSL': colorEvento = '#008778'; break;   // Verde azulado claro (oscurecido)
                                case 'ELSL': colorEvento = '#b39837'; break;   // Amarillo pastel (oscurecido)
                                case 'OPTM': colorEvento = '#157a7f'; break;   // Verde menta (oscurecido)
                                case 'ME': colorEvento = '#8c1b1b'; break;     // Rojo intenso (oscurecido)
                                case 'LD': colorEvento = '#725f59'; break;     // Marrón claro (oscurecido)
                                case 'TF': colorEvento = '#6287b2'; break;     // Azul pastel (oscurecido)
                                case 'LDM': colorEvento = '#b89486'; break;    // Naranja pastel (oscurecido)
                                case 'AM': colorEvento = '#b8949e'; break;     // Rosa pastel (oscurecido)
                                case 'DMTY': colorEvento = '#7ea2a0'; break;   // Verde agua (oscurecido)
                                case 'TI': colorEvento = '#7e6e9e'; break;     // Morado pastel (oscurecido)
                                case 'HU': colorEvento = '#b28594'; break;     // Rosa claro (oscurecido)
                                case 'DINL': colorEvento = '#8a9b72'; break;   // Verde claro pastel (oscurecido)
                                default: colorEvento = '#00559f';              // Azul por defecto (oscurecido)
                            }                                                        
                            
                            // Construir la descripción con todos los campos
                            var descripcionCompleta = 
                                '<i class="fas fa-user"></i> <b>' + actividad.nombre + '</b>\n' +
                                'Área: ' + areaOT + '\n' +
                                'OT: ' + actividad.order_code + '\n' +
                                'Cliente: ' + (actividad.ds_cliente || '') + '\n'+
                                '<hr style="margin-top:0;margin-bottom:0;border-width:2px; border-color:black; border-style:solid;">';
                        
                            eventos.push({
                                title: descripcionCompleta.replace(/\n/g, '<br>'), // Mostrar toda la descripción en el title
                                description: actividad.areaOT, // Para el tooltip en HTML
                                start: actividad.FechaPlaneadaInicioDate,
                                end: actividad.FechaPlaneadaInicioDate,
                                color: colorEvento
                            });
                            
                        });
                        
                        
                        // Crear el calendario
                        var calendarEl = document.createElement('div');
                        calendarEl.id = 'fullcalendar';
                        $('#calendarioActividadesPlaneadas').append(calendarEl);

                        // Inicializar FullCalendar 
                            inicializarCalendario(calendarEl, eventos);
                        
                    } else {
                        $('#calendarioActividadesPlaneadas').html('<p>No hay actividades planeadas.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar actividades pendientes:', error);
                    $('#calendarioActividadesPlaneadas').html('<p>Error al cargar actividades.</p>');
                }
            });

            function inicializarCalendario(calendarEl, eventos) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridWeek',
                    locale: 'es',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,timeGridDay'
                    },
                    events: eventos,
                    eventContent: function(arg) {
                        // Permitir HTML en el título del evento
                        return { html: arg.event.title };
                    },
                    eventDidMount: function(info) {
                        // Tooltip con la descripción
                        $(info.el).tooltip({
                            title: info.event.extendedProps.description,
                            html: true,
                            placement: 'top',
                            container: 'body',
                            trigger: 'hover'
                        });
                    }
                });
                calendar.render();
            }
        }

        function cargarCiudades() {
            //FUNCION PARA CARGAR INFORMACIÓN DE LAS CIUDADES        
            $.ajax({
                type: "POST",
                url: "acciones_solicitud.php",
                data: { opcion: "consultarCiudades" },
                dataType: "json",
                success: function (respuesta) {
                    var select = $("#filtro-ciudad");
                    var i = 0;
                    respuesta.forEach(function (ciudad) {
                        if (i == 0) {
                            var option = `<option value="">Selecciona...</option>`;
                            
                            select.append(option);
                        }
                        var option = `<option value="${ciudad.ciudad}"><b>${ciudad.estado}</b>  -  ${ciudad.ciudad}</option>`;
                        select.append(option);
                        i++;
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

        //funcion para cargar los ingenieros
        function cargarIngenieros() {
            //FUNCION PARA CARGAR INFORMACIÓN DE LOS INGENIEROS
            $.ajax({
                type: "POST",
                url: "acciones_solicitud.php",
                data: { opcion: "empleados" },
                dataType: "json",
                success: function (respuesta) {
                    var select = $("#filtro-ingeniero");
                    var i = 0;
                    respuesta.forEach(function (ingeniero) {                        
                        var option = `<option value="${ingeniero.noEmpleado}">${ingeniero.nombre}</option>`;
                        select.append(option);
                        i++;
                    });
                },
                error: function (xhr, status, error) {
                    
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al cargar los ings.",
                        confirmButtonText: "Aceptar"
                    });
                }
            });
        }


        //Funcion para Enviar los datos
        function filtrar() {
            var ing = $('#filtro-ingeniero').val();
            var area = $('#filtro-area').val();
            var ciudad = $('#filtro-ciudad').val();
            var accion = "ActividadesCalendarioPlaneadas";
            
            $.ajax({
                url: 'acciones_calendario.php',
                method: 'POST',
                async: false,
                dataType: 'json',
                data: { accion, ing, area, ciudad },
                success: function (data) {
                    $('#calendarioActividadesPlaneadas').empty();
                    if (data.status === 'success') {
                        var eventos = [];
                        $.each(data.actividades, function(index, actividad) {
                            // Definir color según el estatus
                            var colorEvento = '';
                            // Extraer el área de order_code usando substring antes del primer '-'                            
                            areaOT = actividad.area;

                            switch (areaOT) {
                                case 'ALTA EXACITUD': colorEvento = '#7a5c00'; break;    // Alta Exactitud
                                case 'CALIBRACIONES': colorEvento = '#804600'; break;    // Calibraciones
                                case 'DIMENSIONAL': colorEvento = '#6b102e'; break;      // Dimensional
                                case 'SFG': colorEvento = '#011c51'; break;              // SFG
                                case 'MITUTOYO': colorEvento = '#3e0d44'; break;         // Mitutoyo
                                case 'DUREZA': colorEvento = '#232d33'; break;           // Dureza
                                case 'MANTENIMIENTO': colorEvento = '#3a241c'; break;    // Mantenimiento
                                case 'ELECTRICA': colorEvento = '#6b5c00'; break;        // Electrica
                                case 'TEMPERATURA': colorEvento = '#0b3557'; break;      // Temperatura
                                case 'PRESION': colorEvento = '#005900'; break;          // Presion
                                case 'APLICACIONES': colorEvento = '#27143f'; break;     // APP Aplicaciones
                                case 'MT': colorEvento = '#004d53'; break;               // MT
                                case 'MTS': colorEvento = '#3d4f1a'; break;              // MTS
                                case 'ZEISS': colorEvento = '#10491d'; break;            // Zeiss
                                case 'MASA': colorEvento = '#6b4a3c'; break;             // Masa
                                case 'FUERZA': colorEvento = '#4a5b3a'; break;           // Fuerza
                                case 'PAR TORSIONAL': colorEvento = '#00413c'; break;    // Par Torsional
                                default: colorEvento = '#002244';                        // Azul por defecto oscuro
                            }
                            
                            estatus = '';
                            nombre2 = '';
                            nombre3 = '';
                            if(actividad.estatus == 'Pendientedeinformacion'){
                                estatus = '<span class="badge text-bg-primary">Pendiente de información</span>';
                            }
                            if(actividad.estatus == 'Programadasinconfirmar'){
                                estatus = '<span class="badge text-bg-info">Programada sin confirmar</span>';
                            }
                            if(actividad.estatus == "Servicioconfirmadoparasuejecucion"){
                                estatus = '<span class="badge text-bg-warning">Servicio confirmado<br> para ejecución</span>';
                            }
                            if(actividad.estatus == 'Fechareservadasininformación'){
                                estatus = '<span class="badge text-bg-dark">Fecha reservada sin <br>información</span>';
                            }

                            if(actividad.nombre2 != null && actividad.nombre2 != ''){
                                nombre2 = '<i class="fas fa-user"></i> <b>' + actividad.nombre2 + '</b>\n';
                            }
                            if(actividad.nombre3 != null && actividad.nombre3 != ''){
                                nombre3 = '<i class="fas fa-user"></i> <b>' + actividad.nombre3 + '</b>\n';
                            }

                            // Construir la descripción con todos los campos
                            var descripcionCompleta = 
                                '<i class="fas fa-user"></i> <b>' + actividad.nombre + '</b>\n' +
                                nombre2 +
                                nombre3 +
                                'Area: ' + areaOT + '\n' +
                                'OT: ' + actividad.order_code + '\n' +
                                'Cliente: ' + (actividad.ds_cliente || '') + '\n'+
                                'Ciudad: ' + (actividad.city || '') + '\n'+
                                estatus+'\n' +
                                '<hr style="margin-top:0;margin-bottom:0;border-width:2px; border-color:black; border-style:solid;">';

                            eventos.push({
                                title: descripcionCompleta.replace(/\n/g, '<br>'), // Mostrar toda la descripción en el title
                                description: actividad.comment, // Para el tooltip en HTML
                                start: actividad.FechaPlaneadaInicioDate,
                                end: actividad.FechaPlaneadaInicioDate,
                                color: colorEvento
                            });
                            
                        });
                        

                        // Crear el calendario
                        var calendarEl = document.createElement('div');//alert(calendarEl);   
                        calendarEl.id = 'fullcalendar';
                        $('#calendarioActividadesPlaneadas').append(calendarEl);

                        // Inicializar FullCalendar 
                            inicializarCalendariof(calendarEl, eventos);
                        
                    } else {
                        $('#calendarioActividadesPlaneadas').html('<p>No hay actividades planeadas.</p>');
                    }
                    
                }, error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error al aplicar el filtro'.textStatus   );
                }
            });

            function inicializarCalendariof(calendarEl, eventos) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridWeek',
                    locale: 'es',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,dayGridWeek,timeGridDay'
                    },
                    events: eventos,
                    eventContent: function(arg) {
                        // Permitir HTML en el título del evento
                        return { html: arg.event.title };
                    },
                    eventDidMount: function(info) {
                        // Tooltip con la descripción
                        $(info.el).tooltip({
                            title: info.event.extendedProps.description,
                            html: true,
                            placement: 'top',
                            container: 'body',
                            trigger: 'hover'
                        });
                    }
                });
                calendar.render();
            }
        }
    </script>
</body>
</html>