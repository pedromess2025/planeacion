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
    <title>Disponibilidad de Ingenieros</title>

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
        .grid-disp th.col-ing { width: 200px; min-width: 160px; text-align: left; }
        .grid-disp td.col-ing { text-align: left; font-weight: 600; background: #f8f9fc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
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
                    <h1><i class="fas fa-user-clock"></i> Disponibilidad de Ingenieros</h1>
                    <p class="text-muted">Vista de consulta. Los estatus se derivan de servicios planeados, ausencias autorizadas y la planeación de laboratorio/capacitación.</p>

                    <div class="row mb-2">
                        <div class="col-md-3">
                            <label for="filtro-area"><b>Área / Laboratorio:</b></label>
                            <select id="filtro-area" class="form-select">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-ingeniero"><b>Ingeniero:</b></label>
                            <select id="filtro-ingeniero" class="form-select" multiple="multiple"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-region"><b>Región:</b></label>
                            <select id="filtro-region" class="form-select" multiple="multiple"></select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-estatus"><b>Estatus:</b></label>
                            <select id="filtro-estatus" class="form-select" multiple="multiple">
                                <option value="disponible">Disponible</option>
                                <option value="vacaciones">Vacaciones</option>
                                <option value="capacitacion">Capacitación</option>
                                <option value="enlaboratorio">En laboratorio</option>
                                <option value="servicio">Servicio</option>
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
                        <span class="badge" style="background:#ffd8a8;color:#8a3b00;">Vacaciones</span>
                        <span class="badge" style="background:#fff3bf;color:#7a5b00;">Capacitación</span>
                        <span class="badge" style="background:#d0ebff;color:#0b4f8a;">En laboratorio</span>
                        <span class="badge" style="background:#e6d3c1;color:#5a3a1a;">Servicio</span>
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
        var ingenierosData = [];
        var celdasData = {};

        var ESTATUS_META = {
            disponible:    { label: 'Disponible',     bg: '#c6f6d5', fg: '#1b5e20' },
            vacaciones:    { label: 'Vacaciones',     bg: '#ffd8a8', fg: '#8a3b00' },
            capacitacion:  { label: 'Capacitación',   bg: '#fff3bf', fg: '#7a5b00' },
            enlaboratorio: { label: 'En laboratorio', bg: '#d0ebff', fg: '#0b4f8a' },
            servicio:      { label: 'Servicio',       bg: '#e6d3c1', fg: '#5a3a1a' }
        };

        $(document).ready(function() {
            actualizarTituloSemana();
            $('#filtro-area').select2({ placeholder: 'Todas las áreas', allowClear: true });
            $('#filtro-ingeniero').select2({ placeholder: 'Uno o varios ingenieros', allowClear: true });
            $('#filtro-region').select2({ placeholder: 'Una o varias regiones', allowClear: true });
            $('#filtro-estatus').select2({ placeholder: 'Uno o varios estatus', allowClear: true });

            cargarDepartamentos();
            cargarIngenieros();
            cargarRegiones();
            cargarDisponibilidad();

            // Refetch al cambiar filtros que afectan el conjunto de ingenieros / rango
            $('#filtro-area, #filtro-ingeniero, #filtro-region').on('change', cargarDisponibilidad);
            // El filtro de estatus solo re-renderiza (es por celda)
            $('#filtro-estatus').on('change', function() { renderizarGrid(ingenierosData, celdasData); });
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
        function cargarDepartamentos() {
            $.ajax({
                url: 'acciones_calendario.php', method: 'POST', dataType: 'json',
                data: { accion: 'departamentosLab' },
                success: function(data) {
                    if (data.status === 'success') {
                        var sel = $('#filtro-area');
                        data.departamentos.forEach(function(d) {
                            var deptoName = d.departamento.replace(' / Laboratorio', '').replace('/Laboratorio', '').trim();
                            sel.append('<option value="' + d.id + '">' + deptoName + '</option>');
                        });
                    }
                }
            });
        }
        function cargarIngenieros() {
            $.ajax({
                url: 'acciones_solicitud.php', method: 'POST', dataType: 'json',
                data: { opcion: 'empleados', soloServicio: 1 },
                success: function(data) {
                    var sel = $('#filtro-ingeniero');
                    data.forEach(function(ing) {
                        sel.append('<option value="' + ing.noEmpleado + '">' + ing.nombre + '</option>');
                    });
                }
            });
        }
        function cargarRegiones() {
            $.ajax({
                url: 'acciones_solicitud.php', method: 'POST', dataType: 'json',
                data: { opcion: 'consultarRegiones' },
                success: function(data) {
                    var sel = $('#filtro-region');
                    data.forEach(function(r) {
                        sel.append('<option value="' + r.id + '">' + r.region + '</option>');
                    });
                }
            });
        }

        // ================ CARGAR DISPONIBILIDAD ================
        function cargarDisponibilidad() {
            var fechaInicio = formatFecha(fechaBaseSemana);
            var fin = new Date(fechaBaseSemana);
            fin.setDate(fin.getDate() + 6);
            var fechaFin = formatFecha(fin);

            $('#contenedorGrid').html('<p class="text-muted"><i class="fas fa-info-circle"></i> Cargando disponibilidad...</p>');
            $.ajax({
                url: 'acciones_calendario.php', method: 'POST', dataType: 'json',
                data: {
                    accion: 'disponibilidadIngenieros',
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    departamento: $('#filtro-area').val() || '',
                    ingeniero: $('#filtro-ingeniero').val() || [],
                    region: $('#filtro-region').val() || []
                },
                success: function(data) {
                    if (data.status === 'success') {
                        ingenierosData = data.ingenieros || [];
                        celdasData = data.celdas || {};
                        renderizarGrid(ingenierosData, celdasData);
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
        function renderizarGrid(ingenieros, celdas) {
            if (!ingenieros || ingenieros.length === 0) {
                $('#contenedorGrid').html('<p class="text-muted">No hay ingenieros para los filtros seleccionados.</p>');
                return;
            }
            celdas = celdas || {};
            var filtroEstatus = $('#filtro-estatus').val() || [];
            var hoy = formatFecha(new Date());
            var fechaInicioStr = formatFecha(fechaBaseSemana);

            var fechas = [];
            for (var i = 0; i < 7; i++) {
                var d = new Date(fechaInicioStr + 'T12:00:00');
                d.setDate(d.getDate() + i);
                fechas.push(formatFecha(d));
            }

            var html = '<table class="grid-disp"><thead><tr><th class="col-ing"><i class="fas fa-user"></i> Ingeniero</th>';
            fechas.forEach(function(f, idx) {
                var d = new Date(f + 'T12:00:00');
                var label = nombreDias[idx] + ' ' + d.getDate() + '/' + (d.getMonth()+1);
                var esHoy = (f === hoy) ? ' style="background:#1cc88a;"' : '';
                html += '<th' + esHoy + '>' + label + '</th>';
            });
            html += '</tr></thead><tbody>';

            ingenieros.forEach(function(ing) {
                html += '<tr><td class="col-ing" title="' + ing.nombre + '">' + ing.nombre + '</td>';
                var celdasIng = celdas[ing.id_usuario] || {};
                fechas.forEach(function(f) {
                    var info = celdasIng[f];
                    var estatus = info ? info.estatus : 'disponible';
                    var meta = ESTATUS_META[estatus] || ESTATUS_META.disponible;
                    var claseHoy = (f === hoy) ? ' celda-hoy' : '';
                    var detalle = (info && info.detalle) ? info.detalle : '';

                    var visible = (filtroEstatus.length === 0) || (filtroEstatus.indexOf(estatus) !== -1);
                    if (!visible) {
                        html += '<td class="celda-disp celda-muted' + claseHoy + '"></td>';
                    } else {
                        var det = detalle ? '<small title="' + detalle.replace(/"/g,'&quot;') + '">' + detalle + '</small>' : '';
                        html += '<td class="celda-disp' + claseHoy + '" style="background:' + meta.bg + ';color:' + meta.fg + ';">' + meta.label + det + '</td>';
                    }
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            $('#contenedorGrid').html(html);
        }
    </script>
</body>
</html>
