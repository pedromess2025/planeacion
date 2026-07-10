<?php
    session_start();
    include 'conn.php';
    if($_COOKIE['noEmpleado'] == '' || $_COOKIE['noEmpleado'] == null){
        echo '<script>window.location.assign("index")</script>';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Disponibilidad de Vehículos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/planeacion.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .grid-disp { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid-disp th, .grid-disp td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: center; vertical-align: middle; font-size: 13px; }
        .grid-disp th { background: #4e73df; color: #fff; position: sticky; top: 0; z-index: 2; }
        .grid-disp th.col-veh { width: 220px; min-width: 180px; text-align: left; }
        .grid-disp td.col-veh { text-align: left; font-weight: 600; background: #f8f9fc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .grid-disp td.col-veh small { display:block; font-weight: 400; font-size: 10px; color: #6c757d; }
        .grid-disp td.col-veh .badge-comodin { display:inline-block; font-size: 9px; font-weight: 700; background:#495057; color:#fff; border-radius:6px; padding:1px 6px; margin-left:4px; vertical-align:middle; }
        .grid-disp tr.fila-comodin td.col-veh { border-left: 4px solid #495057; background:#eef1f4; }
        .grid-disp tbody tr { border-bottom: 2px solid #dee2e6; }
        .celda-disp { min-height: 46px; line-height: 1.25; font-size: 11px; font-weight: 600; }
        .celda-disp small { display:block; font-weight: 400; font-size: 10px; opacity: 0.85; }
        .celda-muted { background: #f1f3f5 !important; color: #adb5bd !important; }
        .celda-hoy { box-shadow: inset 0 0 0 2px #4e73df; }
        .nav-semana { display: flex; align-items: center; gap: 10px; }
        .nav-semana h5 { margin: 0; min-width: 250px; text-align: center; }
        .leyenda .badge { font-size: 12px; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <h1><i class="fas fa-car"></i> Disponibilidad de Vehículos</h1>
                    <p class="text-muted">Vista de consulta. Se muestran los vehículos asignados a ingenieros; el estatus se deriva de préstamos autorizados/en curso y mantenimientos programados.</p>

                    <div class="row mb-2">
                        <div class="col-md-3">
                            <label for="filtro-laboratorio"><b>Laboratorio:</b></label>
                            <select id="filtro-laboratorio" class="form-select" multiple="multiple"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-vehiculo"><b>Vehículo:</b></label>
                            <select id="filtro-vehiculo" class="form-select" multiple="multiple"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-ingeniero"><b>Ingeniero:</b></label>
                            <select id="filtro-ingeniero" class="form-select" multiple="multiple"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-area"><b>Región:</b></label>
                            <select id="filtro-area" class="form-select">
                                <option value="">Todas</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12 d-flex align-items-center">
                            <div class="nav-semana">
                                <button class="btn btn-outline-primary btn-sm" onclick="cambiarSemana(-1)"><i class="fas fa-chevron-left"></i></button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="irAHoy()">Hoy</button>
                                <h5 id="tituloSemana"></h5>
                                <button class="btn btn-outline-primary btn-sm" onclick="cambiarSemana(1)"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="leyenda mb-2" style="font-size:12px;">
                        <span class="badge" style="background:#c6f6d5;color:#1b5e20;">Disponible</span>
                        <span class="badge" style="background:#d0ebff;color:#0b4f8a;">En servicio</span>
                        <span class="badge" style="background:#ffd8a8;color:#8a3b00;">En préstamo</span>
                        <span class="badge" style="background:#d0bfff;color:#5f3dc4;">En mantenimiento</span>
                    </div>

                    <div id="contenedorGrid" style="overflow-x:auto;">
                        <p class="text-muted"><i class="fas fa-info-circle"></i> Cargando disponibilidad...</p>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto"><span>Copyright &copy; MESS <?php echo date('Y'); ?></span></div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        var fechaBaseSemana = getLunes(new Date());
        var nombreDias = ['lun','mar','mié','jue','vie','sáb','dom'];

        // Datos de la última consulta (para re-render al cambiar el filtro de estatus sin refetch)
        var vehiculosData = [];
        var celdasData = {};
        var todosVehiculos = [];            // lista maestra (para filtrar el dropdown por área)
        var vehiculosFiltroCargado = false; // el select de vehículo se puebla en la 1ª carga
        var porPagina = 10;                 // registros por hoja
        var paginaActual = 1;

        var ESTATUS_META = {
            disponible:    { label: 'Disponible',       bg: '#c6f6d5', fg: '#1b5e20' },
            servicio:      { label: 'En servicio',      bg: '#d0ebff', fg: '#0b4f8a' },
            prestamo:      { label: 'En préstamo',      bg: '#ffd8a8', fg: '#8a3b00' },
            mantenimiento: { label: 'En mantenimiento', bg: '#d0bfff', fg: '#5f3dc4' }
        };

        $(document).ready(function() {
            actualizarTituloSemana();
            $('#filtro-laboratorio').select2({ placeholder: 'Uno o varios laboratorios', allowClear: true });
            $('#filtro-vehiculo').select2({ placeholder: 'Uno o varios vehículos', allowClear: true });
            $('#filtro-ingeniero').select2({ placeholder: 'Uno o varios ingenieros', allowClear: true });
            $('#filtro-area').select2({ placeholder: 'Todas las regiones', allowClear: true });

            cargarLaboratorios();
            cargarIngenieros();
            cargarAreas();
            cargarDisponibilidad();

            // Al cambiar la Región: filtrar el dropdown de Vehículo a esa región (y recargar el grid)
            $('#filtro-area').on('change', function() {
                var area = $('#filtro-area').val() || '';
                var lista = area ? todosVehiculos.filter(function(v){ return v.area === area; }) : todosVehiculos;
                $('#filtro-vehiculo').val(null);
                poblarFiltroVehiculos(lista, true);
                cargarDisponibilidad();
            });
            // Los otros filtros solo recargan el grid
            $('#filtro-vehiculo, #filtro-laboratorio, #filtro-ingeniero').on('change', cargarDisponibilidad);
        });

        // ================ NAVEGACIÓN SEMANAL ================
        function getLunes(d) {
            var fecha = new Date(d);
            var dia = fecha.getDay();
            var diff = fecha.getDate() - dia + (dia === 0 ? -6 : 1);
            return new Date(fecha.setDate(diff));
        }
        function cambiarSemana(dir) {
            fechaBaseSemana.setDate(fechaBaseSemana.getDate() + (dir * 7));
            actualizarTituloSemana();
            cargarDisponibilidad();
        }
        function irAHoy() {
            fechaBaseSemana = getLunes(new Date());
            actualizarTituloSemana();
            cargarDisponibilidad();
        }
        function actualizarTituloSemana() {
            var fin = new Date(fechaBaseSemana);
            fin.setDate(fin.getDate() + 6);
            var opts = { day: 'numeric', month: 'short', year: 'numeric' };
            $('#tituloSemana').text(fechaBaseSemana.toLocaleDateString('es-MX', opts) + '  –  ' + fin.toLocaleDateString('es-MX', opts));
        }
        function formatFecha(d) {
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }

        // ================ CARGAR FILTROS ================
        function cargarAreas() {
            $.ajax({
                url: 'acciones_disponibilidad.php', method: 'POST', dataType: 'json',
                data: { accion: 'areasVehiculos' },
                success: function(data) {
                    if (data.status === 'success') {
                        var sel = $('#filtro-area');
                        data.areas.forEach(function(a) {
                            sel.append('<option value="' + a + '">' + a + '</option>');
                        });
                    }
                }
            });
        }
        function cargarLaboratorios() {
            $.ajax({
                url: 'acciones_disponibilidad.php', method: 'POST', dataType: 'json',
                data: { accion: 'laboratoriosVehiculos' },
                success: function(data) {
                    if (data.status === 'success') {
                        var sel = $('#filtro-laboratorio');
                        data.laboratorios.forEach(function(l) {
                            sel.append('<option value="' + l.id + '">' + l.nombre + '</option>');
                        });
                    }
                }
            });
        }
        function cargarIngenieros() {
            $.ajax({
                url: 'acciones_disponibilidad.php', method: 'POST', dataType: 'json',
                data: { accion: 'ingenierosVehiculos' },
                success: function(data) {
                    if (data.status === 'success') {
                        var sel = $('#filtro-ingeniero');
                        data.ingenieros.forEach(function(i) {
                            sel.append('<option value="' + i.id + '">' + i.nombre + '</option>');
                        });
                    }
                }
            });
        }
        function poblarFiltroVehiculos(vehiculos, reemplazar) {
            var sel = $('#filtro-vehiculo');
            if (reemplazar) sel.empty();
            vehiculos.forEach(function(v) {
                var etiqueta = (v.placa || 'S/P') + ' · ' + [v.marca, v.modelo].filter(Boolean).join(' ');
                sel.append('<option value="' + v.id_vehiculo + '">' + etiqueta + '</option>');
            });
            sel.trigger('change.select2');
        }

        // ================ CARGAR DISPONIBILIDAD ================
        function cargarDisponibilidad() {
            var fechaInicio = formatFecha(fechaBaseSemana);
            var fin = new Date(fechaBaseSemana);
            fin.setDate(fin.getDate() + 6);
            var fechaFin = formatFecha(fin);

            $('#contenedorGrid').html('<p class="text-muted"><i class="fas fa-info-circle"></i> Cargando disponibilidad...</p>');
            $.ajax({
                url: 'acciones_disponibilidad.php', method: 'POST', dataType: 'json',
                data: {
                    accion: 'disponibilidadVehiculos',
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    area: $('#filtro-area').val() || '',
                    departamento: $('#filtro-laboratorio').val() || [],
                    ingeniero: $('#filtro-ingeniero').val() || [],
                    vehiculo: $('#filtro-vehiculo').val() || []
                },
                success: function(data) {
                    if (data.status === 'success') {
                        vehiculosData = data.vehiculos || [];
                        celdasData = data.celdas || {};
                        paginaActual = 1; // nueva carga -> vuelve a la 1ª hoja
                        // La 1ª carga (sin filtros) define la lista maestra del dropdown de Vehículo
                        if (!vehiculosFiltroCargado) {
                            todosVehiculos = vehiculosData;
                            poblarFiltroVehiculos(todosVehiculos, true);
                            vehiculosFiltroCargado = true;
                        }
                        renderizarGrid(vehiculosData, celdasData);
                    } else {
                        $('#contenedorGrid').html('<p class="text-danger">' + (data.message || 'Error') + '</p>');
                    }
                },
                error: function() {
                    $('#contenedorGrid').html('<p class="text-danger">Error al cargar la disponibilidad.</p>');
                }
            });
        }

        // ================ RENDER ================
        function renderizarGrid(vehiculos, celdas) {
            if (!vehiculos || vehiculos.length === 0) {
                $('#contenedorGrid').html('<p class="text-muted">No hay vehículos para los filtros seleccionados.</p>');
                return;
            }
            celdas = celdas || {};

            // Paginación: 10 vehículos por hoja (respeta el orden comodines-primero del backend)
            var totalReg = vehiculos.length;
            var totalPag = Math.max(1, Math.ceil(totalReg / porPagina));
            if (paginaActual > totalPag) paginaActual = totalPag;
            if (paginaActual < 1) paginaActual = 1;
            var iniIdx = (paginaActual - 1) * porPagina;
            var pageRows = vehiculos.slice(iniIdx, iniIdx + porPagina);

            var hoy = formatFecha(new Date());
            var fechaInicioStr = formatFecha(fechaBaseSemana);

            var fechas = [];
            for (var i = 0; i < 7; i++) {
                var d = new Date(fechaInicioStr + 'T12:00:00');
                d.setDate(d.getDate() + i);
                fechas.push(formatFecha(d));
            }

            var html = '<table class="grid-disp"><thead><tr><th class="col-veh"><i class="fas fa-car"></i> Vehículo</th>';
            fechas.forEach(function(f, idx) {
                var d = new Date(f + 'T12:00:00');
                var label = nombreDias[idx] + ' ' + d.getDate() + '/' + (d.getMonth()+1);
                var esHoy = (f === hoy) ? ' style="background:#1cc88a;"' : '';
                html += '<th' + esHoy + '>' + label + '</th>';
            });
            html += '</tr></thead><tbody>';

            pageRows.forEach(function(veh) {
                var titulo = (veh.placa || 'S/P') + ' — ' + [veh.marca, veh.modelo].filter(Boolean).join(' ');
                var sub = [ [veh.marca, veh.modelo].filter(Boolean).join(' '), veh.usuario ].filter(Boolean).join(' · ');
                var esComodin = (veh.comodin == 1);
                var badgeCom = esComodin ? ' <span class="badge-comodin">Comodín</span>' : '';
                html += '<tr' + (esComodin ? ' class="fila-comodin"' : '') + '><td class="col-veh" title="' + titulo.replace(/"/g,'&quot;') + '">' + (veh.placa || 'S/P') + badgeCom +
                        '<small>' + sub + '</small></td>';
                var celdasVeh = celdas[veh.id_vehiculo] || {};
                fechas.forEach(function(f) {
                    var info = celdasVeh[f];
                    var estatus = info ? info.estatus : 'disponible';
                    var meta = ESTATUS_META[estatus] || ESTATUS_META.disponible;
                    var claseHoy = (f === hoy) ? ' celda-hoy' : '';
                    var detalle = (info && info.detalle) ? info.detalle : '';
                    var titulo = (info && info.titulo) ? info.titulo : detalle;

                    var det = detalle ? '<small>' + detalle + '</small>' : '';
                    var ttAttr = titulo ? ' data-toggle="tooltip" data-html="true" title="' + titulo.replace(/"/g,'&quot;') + '"' : '';
                    html += '<td class="celda-disp' + claseHoy + '"' + ttAttr + ' style="background:' + meta.bg + ';color:' + meta.fg + ';">' + meta.label + det + '</td>';
                });
                html += '</tr>';
            });

            html += '</tbody></table>';

            // Barra de paginación
            var desde = totalReg === 0 ? 0 : (iniIdx + 1);
            var hasta = Math.min(iniIdx + porPagina, totalReg);
            html += '<div class="d-flex align-items-center justify-content-between mt-2 flex-wrap" style="font-size:13px; gap:8px;">' +
                        '<span class="text-muted">Mostrando ' + desde + '&ndash;' + hasta + ' de ' + totalReg + ' veh&iacute;culos</span>' +
                        '<div class="d-flex align-items-center">' +
                            '<button class="btn btn-sm btn-outline-primary" onclick="cambiarPagina(-1)"' + (paginaActual <= 1 ? ' disabled' : '') + '><i class="fas fa-chevron-left"></i> Anterior</button>' +
                            '<span class="mx-2">P&aacute;gina ' + paginaActual + ' de ' + totalPag + '</span>' +
                            '<button class="btn btn-sm btn-outline-primary" onclick="cambiarPagina(1)"' + (paginaActual >= totalPag ? ' disabled' : '') + '>Siguiente <i class="fas fa-chevron-right"></i></button>' +
                        '</div>' +
                    '</div>';

            $('body > .tooltip').remove(); // limpia tooltips flotantes de un render anterior
            $('#contenedorGrid').html(html);
            // Tooltip estilo "Actividades planeadas" (Bootstrap, HTML) en las celdas con detalle
            $('#contenedorGrid [data-toggle="tooltip"]').tooltip({ html: true, placement: 'top', container: 'body', trigger: 'hover' });
        }

        function cambiarPagina(dir) {
            paginaActual += dir;
            renderizarGrid(vehiculosData, celdasData);
        }
    </script>
</body>
</html>
