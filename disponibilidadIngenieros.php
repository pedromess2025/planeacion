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
        .asig-badge { display: block; margin-top: 3px; font-weight: 500; font-size: 10px; line-height: 1.3; border-radius: 8px; padding: 1px 7px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .asig-servicio { color: #1b5e20; background: #e3f6e6; }
        .asig-laboratorio { color: #0b4f8a; background: #e3f0fb; }
        .asig-jefatura { color: #5a2d82; background: #f0e6fa; }
        .asig-administracion { color: #6c5300; background: #fbf3d6; }
        .asig-none { color: #adb5bd; background: #f1f3f5; font-style: italic; }
        .link-tablero { display: block; margin-top: 3px; font-size: 10px; line-height: 1.3; font-weight: 500; color: #0b5ed7; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .link-tablero:hover { text-decoration: underline; }
        .lab-ing { display: block; margin-top: 3px; font-size: 10px; line-height: 1.3; font-weight: 500; color: #495057; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lab-ing i { color: #6c757d; }
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
                        <div class="col-md-4">
                            <label for="filtro-area"><b>Área / Laboratorio:</b></label>
                            <select id="filtro-area" class="form-select">
                                <option value="">Todas</option>
                            </select>
                            <!-- 2º filtro en cascada: aparece solo al elegir un área con sub-departamentos (ej. "Lab Hugo") -->
                            <div id="cont-departamento" class="mt-2" style="display:none;">
                                <label for="filtro-departamento"><b>Departamento:</b></label>
                                <select id="filtro-departamento" class="form-select">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="filtro-ingeniero"><b>Ingeniero:</b></label>
                            <select id="filtro-ingeniero" class="form-select" multiple="multiple"></select>
                        </div>
                        <div class="col-md-4">
                            <label for="filtro-estatus"><b>Estatus:</b></label>
                            <select id="filtro-estatus" class="form-select" multiple="multiple">
                                <option value="disponible">Disponible</option>
                                <option value="vacaciones">Vacaciones</option>
                                <option value="capacitacion">Capacitación</option>
                                <option value="enlaboratorio">En laboratorio</option>
                                <option value="otinterna">OT interna</option>
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
                        <span class="badge" style="background:#eddcf9;color:#6a1b9a;">OT interna</span>
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
            otinterna:     { label: 'OT interna',     bg: '#eddcf9', fg: '#6a1b9a' },
            servicio:      { label: 'Servicio',       bg: '#e6d3c1', fg: '#5a3a1a' }
        };

        $(document).ready(function() {
            actualizarTituloSemana();
            $('#filtro-area').select2({ placeholder: 'Todas las áreas', allowClear: true });
            $('#filtro-ingeniero').select2({ placeholder: 'Uno o varios ingenieros', allowClear: true });
            $('#filtro-estatus').select2({ placeholder: 'Uno o varios estatus', allowClear: true });

            cargarZonas();
            cargarIngenieros();
            cargarDisponibilidad();

            // Refetch al cambiar filtros que afectan el conjunto de ingenieros / rango
            $('#filtro-ingeniero, #filtro-departamento').on('change', cargarDisponibilidad);
            // El filtro de Área además decide si aparece el 2º filtro (departamento) en cascada
            $('#filtro-area').on('change', function() {
                actualizarFiltroDepartamento();
                cargarDisponibilidad();
            });
            // El filtro de estatus solo re-renderiza (es por celda)
            $('#filtro-estatus').on('change', function() { renderizarGrid(ingenierosData, celdasData); });
        });

        // Filtro en cascada: al elegir un área agrupadora (hoy "Lab Hugo") se muestra un 2º select
        // con los departamentos de esa zona; en cualquier otra área se oculta y se limpia.
        function actualizarFiltroDepartamento() {
            var zona = $('#filtro-area').val() || '';
            $('#filtro-departamento').val('');
            if (zona.trim().toLowerCase() !== 'lab hugo') {
                $('#cont-departamento').hide();
                $('#filtro-departamento').html('<option value="">Todos</option>');
                return;
            }
            $.ajax({
                url: 'acciones_disponibilidad.php', method: 'POST', dataType: 'json',
                data: { accion: 'departamentosZona', zona: zona },
                success: function(data) {
                    var sel = $('#filtro-departamento').html('<option value="">Todos</option>');
                    if (data.status === 'success') {
                        data.departamentos.forEach(function(d) {
                            sel.append('<option value="' + d.id + '">' + d.nombre + '</option>');
                        });
                    }
                    $('#cont-departamento').show();
                }
            });
        }

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
        function cargarZonas() {
            $.ajax({
                url: 'acciones_disponibilidad.php', method: 'POST', dataType: 'json',
                data: { accion: 'zonasLab' },
                success: function(data) {
                    if (data.status === 'success') {
                        var sel = $('#filtro-area');
                        data.zonas.forEach(function(z) {
                            sel.append('<option value="' + z + '">' + z + '</option>');
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
                    accion: 'disponibilidadIngenieros',
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    zona: $('#filtro-area').val() || '',
                    departamento: $('#filtro-departamento').val() || '',
                    ingeniero: $('#filtro-ingeniero').val() || []
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

        // Badge con la Asignación (fija) del ingeniero, bajo su nombre
        function badgeAsignacion(asig) {
            if (!asig) return '<small class="asig-badge asig-none"><i class="fas fa-tag"></i> Sin asignación</small>';
            var a = asig.toLowerCase(), clase = 'asig-none';
            if (a.indexOf('servicio') !== -1)           clase = 'asig-servicio';
            else if (a.indexOf('laboratorio') !== -1)   clase = 'asig-laboratorio';
            else if (a.indexOf('jefatura') !== -1)      clase = 'asig-jefatura';
            else if (a.indexOf('administ') !== -1)       clase = 'asig-administracion';
            var txt = asig.replace(/</g,'&lt;').replace(/>/g,'&gt;');
            return '<small class="asig-badge ' + clase + '" title="Asignación: ' + txt + '"><i class="fas fa-tag"></i> ' + txt + '</small>';
        }

        // Link al tablero personal de PowerBI del ingeniero. Abre nuestra página interna (tras login)
        // en otra pestaña, con encabezado/pie; el embed vive ahí y la URL pública no se expone aquí.
        function linkTablero(enlace, idUsuario) {
            if (!enlace) return '';
            return '<a class="link-tablero" href="tableroIngeniero.php?ing=' + encodeURIComponent(idUsuario) +
                   '" target="_blank" rel="noopener" title="Ver tablero de PowerBI"><i class="fas fa-chart-line"></i> Ver tablero</a>';
        }

        // Lab (departamento) del ingeniero, bajo el nombre junto a "Ver tablero".
        function labIng(lab) {
            if (!lab) return '';
            var txt = String(lab).replace(/</g,'&lt;').replace(/>/g,'&gt;');
            return '<small class="lab-ing" title="Laboratorio / departamento: ' + txt + '"><i class="fas fa-flask"></i> ' + txt + '</small>';
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
                html += '<tr><td class="col-ing" title="' + ing.nombre + '">' + ing.nombre + badgeAsignacion(ing.asignacion) + linkTablero(ing.enlace, ing.id_usuario) + labIng(ing.lab) + '</td>';
                var celdasIng = celdas[ing.id_usuario] || {};
                fechas.forEach(function(f) {
                    // Fin de semana: SIEMPRE disponible, sin importar servicio/lab/vacaciones/base.
                    var diaSem = new Date(f + 'T12:00:00').getDay(); // 0=dom, 6=sáb
                    var esFinSemana = (diaSem === 0 || diaSem === 6);
                    var info = esFinSemana ? null : celdasIng[f];
                    // Sin evento en la celda -> estatus BASE del ingeniero (según su Asignación); default 'disponible'
                    var estatus = esFinSemana ? 'disponible' : (info ? info.estatus : (ing.base || 'disponible'));
                    var meta = ESTATUS_META[estatus] || ESTATUS_META.disponible;
                    var claseHoy = (f === hoy) ? ' celda-hoy' : '';
                    var detalle = (info && info.detalle) ? info.detalle : '';
                    var titulo = (info && info.titulo) ? info.titulo : '';

                    var visible = (filtroEstatus.length === 0) || (filtroEstatus.indexOf(estatus) !== -1);
                    if (!visible) {
                        html += '<td class="celda-disp celda-muted' + claseHoy + '"></td>';
                    } else {
                        // Con popup (servicio) -> tooltip Bootstrap HTML en la celda; sin popup -> title nativo en el detalle
                        var det = detalle ? '<small' + (titulo ? '' : ' title="' + detalle.replace(/"/g,'&quot;') + '"') + '>' + detalle + '</small>' : '';
                        var ttAttr = titulo ? ' data-toggle="tooltip" data-html="true" title="' + titulo.replace(/"/g,'&quot;') + '"' : '';
                        html += '<td class="celda-disp' + claseHoy + '"' + ttAttr + ' style="background:' + meta.bg + ';color:' + meta.fg + ';">' + meta.label + det + '</td>';
                    }
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            $('body > .tooltip').remove(); // limpia tooltips flotantes de un render anterior
            $('#contenedorGrid').html(html);
            // Popup (tooltip Bootstrap HTML on-hover) en las celdas de servicio, estilo Disponibilidad de Vehículos
            $('#contenedorGrid [data-toggle="tooltip"]').tooltip({ html: true, placement: 'top', container: 'body', trigger: 'hover' });
        }
    </script>
</body>
</html>
