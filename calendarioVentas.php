<?php
    session_start();
    include 'conn.php';
    if($_COOKIE['noEmpleado'] == '' || $_COOKIE['noEmpleado'] == null){
        echo '<script>window.location.assign("index")</script>';
    }
    if(!isset($_COOKIE['departamento']) || !in_array((string)$_COOKIE['departamento'], DEPTOS_VENTAS, true)){
        echo '<script>window.location.assign("verActividadesPlaneadas")</script>';
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Calendario Ventas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/planeacion.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .grid-ventas { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .grid-ventas th, .grid-ventas td { border: 1px solid #dee2e6; padding: 6px 8px; text-align: center; vertical-align: top; font-size: 13px; }
        .grid-ventas th { background: #4e73df; color: #fff; position: sticky; top: 0; z-index: 2; }
        .grid-ventas th.col-ing { width: 180px; min-width: 140px; text-align: left; }
        .grid-ventas td.col-ing { text-align: left; font-weight: 600; background: #f8f9fc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .grid-ventas tbody tr { border-bottom: 2px solid #dee2e6; }
        .celda-libre { background: #d4edda; cursor: pointer; transition: background 0.15s; min-height: 48px; border: 2px solid #000 !important; }
        .celda-libre:hover { background: #a3d9a5; }
        .celda-ocupada { background: #fff3cd; cursor: default; font-size: 11px; line-height: 1.3; }
        .celda-preregistro { background: #ffe0b3; cursor: default; font-size: 11px; line-height: 1.3; }
        .celda-confirmada { background: #cfe2ff; cursor: default; font-size: 11px; line-height: 1.3; }
        .prereg-editable { cursor: pointer; border-radius: 3px; padding: 1px 2px; transition: outline 0.1s; }
        .prereg-editable:hover { outline: 2px solid #8a4b00; }
        .celda-hoy { box-shadow: inset 0 0 0 2px #4e73df; }
        .nav-semana { display: flex; align-items: center; gap: 10px; }
        .nav-semana h5 { margin: 0; min-width: 250px; text-align: center; }
        .area-seleccionada { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 10px; }
    </style>
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow">
                                <div class="card-body">
                                    <p class="fs-4"><b><i class="fas fa-store"></i> CALENDARIO DE VENTAS</b></p>
                                    <hr>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="slcDepartamento"><b>Selecciona el &Aacute;rea:</b></label>
                                            <select id="slcDepartamento" class="form-select">
                                                <option value="">-- Selecciona un &aacute;rea --</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 d-flex align-items-end">
                                            <div class="nav-semana">
                                                <button class="btn btn-outline-primary btn-sm" onclick="cambiarSemana(-1)"><i class="fas fa-chevron-left"></i></button>
                                                <button class="btn btn-outline-secondary btn-sm" onclick="irAHoy()">Hoy</button>
                                                <h5 id="tituloSemana"></h5>
                                                <button class="btn btn-outline-primary btn-sm" onclick="cambiarSemana(1)"><i class="fas fa-chevron-right"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="contenedorGrid" style="overflow-x:auto;">
                                        <p class="text-muted"><i class="fas fa-info-circle"></i> Selecciona un &aacute;rea para ver la disponibilidad.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    <div id="contenedorModalVentas"></div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        var fechaBaseSemana = getLunes(new Date());
        var nombreDias = ['lun','mar','mié','jue','vie','sáb','dom'];

        $(document).ready(function() {
            cargarDepartamentos();
            actualizarTituloSemana();

            $('#slcDepartamento').on('change', function() {
                cargarDisponibilidad();
            });
        });

        // ================ NAVEGACION SEMANAL ================
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

        // ================ CARGAR DEPARTAMENTOS ================
        function cargarDepartamentos() {
            $.ajax({
                url: 'acciones_calendario.php',
                method: 'POST',
                dataType: 'json',
                data: { accion: 'departamentosLab' },
                success: function(data) {
                    if (data.status === 'success') {
                        var sel = $('#slcDepartamento');
                        data.departamentos.forEach(function(d) {
                            var deptoName = d.departamento.replace(' / Laboratorio', '').replace('/Laboratorio', '').trim();
                            sel.append('<option value="' + d.id + '">' + deptoName + '</option>');
                        });
                    }
                }
            });
        }

        // ================ CARGAR DISPONIBILIDAD ================
        function cargarDisponibilidad() {
            var depto = $('#slcDepartamento').val();
            if (!depto) {
                $('#contenedorGrid').html('<p class="text-muted"><i class="fas fa-info-circle"></i> Selecciona un área para ver la disponibilidad.</p>');
                return;
            }

            var fechaInicio = formatFecha(fechaBaseSemana);
            var fin = new Date(fechaBaseSemana);
            fin.setDate(fin.getDate() + 6);
            var fechaFin = formatFecha(fin);

            $.ajax({
                url: 'acciones_calendario.php',
                method: 'POST',
                dataType: 'json',
                data: { accion: 'disponibilidadVentas', departamento: depto, fechaInicio: fechaInicio, fechaFin: fechaFin },
                success: function(data) {
                    if (data.status === 'success') {
                        renderizarGrid(data.ingenieros, data.servicios, fechaInicio);
                    } else {
                        $('#contenedorGrid').html('<p class="text-danger">' + (data.message || 'Error') + '</p>');
                    }
                },
                error: function() {
                    $('#contenedorGrid').html('<p class="text-danger">Error al cargar la disponibilidad.</p>');
                }
            });
        }

        // Mapas globales para precargar el modal de edición
        var serviciosPorId = {};
        var ingenierosPorId = {};

        // ================ RENDERIZAR CUADRICULA ================
        function renderizarGrid(ingenieros, servicios, fechaInicioStr) {
            if (ingenieros.length === 0) {
                $('#contenedorGrid').html('<p class="text-muted">No hay ingenieros activos en esta área.</p>');
                return;
            }

            var deptoNombre = $.trim($('#slcDepartamento option:selected').text());
            var miNoEmpleado = getCookie('noEmpleado');
            var hoy = formatFecha(new Date());
            var fechas = [];
            for (var i = 0; i < 7; i++) {
                var d = new Date(fechaInicioStr + 'T12:00:00');
                d.setDate(d.getDate() + i);
                fechas.push(formatFecha(d));
            }

            serviciosPorId = {};
            ingenierosPorId = {};
            ingenieros.forEach(function(ing) { ingenierosPorId[ing.id_usuario] = ing.nombre; });
            var serviciosPorIngFecha = {};
            servicios.forEach(function(s) {
                serviciosPorId[s.id] = s;
                ['engineer','engineer2','engineer3'].forEach(function(campo) {
                    var idIng = s[campo];
                    if (idIng && idIng !== '0' && idIng !== '') {
                        if (!serviciosPorIngFecha[idIng]) serviciosPorIngFecha[idIng] = {};
                        if (!serviciosPorIngFecha[idIng][s.fecha]) serviciosPorIngFecha[idIng][s.fecha] = [];
                        serviciosPorIngFecha[idIng][s.fecha].push(s);
                    }
                });
            });

            var html = '<div class="area-seleccionada"><i class="fas fa-building"></i> Área seleccionada: <b>' + deptoNombre + '</b></div>';
            html += '<div style="margin-bottom:10px; font-size:12px;">';
            html += '<span class="badge" style="background:#d4edda;color:#155724;border:2px solid #000;">Disponible</span> ';
            html += '<span class="badge" style="background:#fff3cd;color:#856404;">Ocupado</span> ';
            html += '<span class="badge" style="background:#ffe0b3;color:#8a4b00;">Pre-registro</span> ';
            html += '<span class="badge" style="background:#cfe2ff;color:#084298;">Confirmado</span>';
            html += '</div>';
            html += '<table class="grid-ventas"><thead><tr><th class="col-ing"><i class="fas fa-user"></i> Ingeniero</th>';
            fechas.forEach(function(f, idx) {
                var d = new Date(f + 'T12:00:00');
                var label = nombreDias[idx] + ' ' + d.getDate() + '/' + (d.getMonth()+1);
                var esHoy = (f === hoy) ? ' style="background:#1cc88a;"' : '';
                html += '<th' + esHoy + '>' + label + '</th>';
            });
            html += '</tr></thead><tbody>';

            ingenieros.forEach(function(ing) {
                html += '<tr><td class="col-ing" title="' + ing.nombre + '">' + ing.nombre + '</td>';
                fechas.forEach(function(f) {
                    var servsDia = (serviciosPorIngFecha[ing.id_usuario] && serviciosPorIngFecha[ing.id_usuario][f]) || [];
                    var claseHoy = (f === hoy) ? ' celda-hoy' : '';

                    if (servsDia.length > 0) {
                        var contenido = '';
                        var esPreReg = false;
                        var esConfirmado = false;
                        servsDia.forEach(function(s) {
                            if (s.estatus === 'Solicitadoventas') esPreReg = true;
                            if (s.estatus === 'Servicioconfirmadoparasuejecucion' && s.origen_captura === 'ventas') esConfirmado = true;
                            // Pre-registro propio y pendiente => editable (clic abre modal de edición)
                            var editable = (s.estatus === 'Solicitadoventas' && s.origen_captura === 'ventas' && String(s.capturado_por) === String(miNoEmpleado));
                            contenido += editable
                                ? '<div class="mb-1 prereg-editable" data-prereg-id="' + s.id + '" title="Clic para editar o cancelar">'
                                : '<div class="mb-1">';
                            contenido += '<b>' + (s.ds_cliente || 'S/C') + '</b>';
                            if (editable) contenido += ' <i class="fas fa-pen" style="font-size:9px;opacity:0.6;"></i>';
                            contenido += '<br>';
                            contenido += '<span class="badge bg-info text-dark" style="font-size:10px;">' + (s.city || '') + '</span>';
                            if (s.estatus === 'Solicitadoventas') contenido += '<br><span class="badge bg-primary" style="font-size:9px;">PRE-REG</span>';
                            if (s.estatus === 'Servicioconfirmadoparasuejecucion' && s.origen_captura === 'ventas') contenido += '<br><span class="badge bg-success" style="font-size:9px;">CONFIRMADO</span>';
                            contenido += '</div>';
                        });
                        var claseCelda = esConfirmado ? 'celda-confirmada' : (esPreReg ? 'celda-preregistro' : 'celda-ocupada');
                        html += '<td class="' + claseCelda + claseHoy + '">' + contenido + '</td>';
                    } else {
                        html += '<td class="celda-libre' + claseHoy + '" data-fecha="' + f + '" data-ing-id="' + ing.id_usuario + '" data-ing-nombre="' + ing.nombre + '">';
                        html += '<i class="fas fa-plus-circle text-success" style="font-size:18px;opacity:0.5;"></i>';
                        html += '<br><small class="text-muted">Disponible</small>';
                        html += '</td>';
                    }
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            $('#contenedorGrid').html(html);
        }

        // ================ CLICK EN CELDA LIBRE (crear) ================
        $(document).on('click', '.celda-libre', function() {
            var fecha = $(this).data('fecha');
            var ingNombre = $(this).data('ing-nombre');
            var ingId = $(this).data('ing-id');
            var deptoTexto = $.trim($('#slcDepartamento option:selected').text());

            $('#contenedorModalVentas').load('modalPreRegistroVentas.php', function() {
                $('#formPreRegistroVentas')[0].reset();
                $('#regId').val('');
                $('#regArea').val(deptoTexto);
                $('#regEngineer').val(ingId);
                $('#regFechaBase').val(fecha);
                $('#regFecha').val(fecha);
                $('#regHora').val('08:00');
                $('#infoIngNombre').text(ingNombre);
                $('#infoArea').text(deptoTexto);
                $('#modalPreRegistroVentasLabel').html('<i class="fas fa-store"></i> Pre-registro de Servicio');
                $('#btnPreRegistroVentas').text('Registrar');
                $('#btnEliminarPreRegistro').hide();
                $('#btnPreRegistroVentas').off('click').on('click', guardarPreRegistroVentas);
                cargarCiudadesPreRegistro();
                $('#modalPreRegistroVentas').modal('show');
            });
        });

        // ================ CLICK EN PRE-REGISTRO PROPIO (editar / cancelar) ================
        $(document).on('click', '.prereg-editable', function() {
            var id = $(this).data('prereg-id');
            var s = serviciosPorId[id];
            if (!s) return;
            var deptoTexto = $.trim($('#slcDepartamento option:selected').text());

            $('#contenedorModalVentas').load('modalPreRegistroVentas.php', function() {
                $('#formPreRegistroVentas')[0].reset();
                $('#regId').val(s.id);
                $('#regArea').val(s.area || deptoTexto);
                $('#regEngineer').val(s.engineer || '');
                $('#regFechaBase').val(s.fecha);
                $('#regFecha').val(s.fecha);
                $('#regHora').val(s.hora || '08:00');
                $('#regCliente').val(s.ds_cliente || '');
                $('#regOV').val(s.order_code || '');
                $('#regComentarios').val((s.comment && s.comment !== 'Sin comentarios') ? s.comment : '');
                $('#infoIngNombre').text(ingenierosPorId[s.engineer] || 'Sin asignar');
                $('#infoArea').text(s.area || deptoTexto);
                $('#modalPreRegistroVentasLabel').html('<i class="fas fa-pen"></i> Editar Pre-registro');
                $('#btnPreRegistroVentas').text('Guardar cambios');
                $('#btnEliminarPreRegistro').show().off('click').on('click', cancelarPreRegistroExistente);
                $('#btnPreRegistroVentas').off('click').on('click', guardarPreRegistroVentas);
                cargarCiudadesPreRegistro(function() {
                    $('#regCiudad').val(s.city || '');
                });
                $('#modalPreRegistroVentas').modal('show');
            });
        });

        // ================ FUNCIONES AJAX ================
        function cargarCiudadesPreRegistro(callback) {
            $.ajax({
                type: "POST",
                url: "acciones_solicitud.php",
                data: { opcion: "consultarCiudades" },
                dataType: "json",
                success: function(respuesta) {
                    var select = $("#regCiudad");
                    select.find('option:not(:first)').remove();
                    respuesta.forEach(function(ciudad) {
                        select.append('<option value="' + ciudad.ciudad + '">' + ciudad.estado + '  -  ' + ciudad.ciudad + '</option>');
                    });
                    if (typeof callback === 'function') callback();
                }
            });
        }

        // Crea (sin regId) o edita (con regId) según el estado del modal
        function guardarPreRegistroVentas() {
            var id = ($('#regId').val() || '').trim();
            var cliente = ($('#regCliente').val() || '').trim();
            var ciudad = $('#regCiudad').val();
            var area = ($('#regArea').val() || '').trim();
            var fecha = $('#regFechaBase').val();
            var hora = $('#regHora').val();
            var ov = ($('#regOV').val() || '').trim();
            var engineer = ($('#regEngineer').val() || '').trim();
            var comentarios = ($('#regComentarios').val() || '').trim();

            var errores = [];
            if (cliente === '') errores.push('Ingresa un cliente');
            if (!ciudad) errores.push('Selecciona una ciudad');
            if (!hora) errores.push('Selecciona una hora');
            if (!area) errores.push('Área no detectada');

            if (errores.length > 0) {
                Swal.fire({ title: "Campos incompletos", html: "• " + errores.join('<br>• '), icon: "warning" });
                return;
            }

            var fechaCompleta = fecha + 'T' + hora;
            var esEdicion = (id !== '');
            var datos = esEdicion
                ? { opcion: 'editarPreRegistroVentas', id: id, cliente: cliente, ciudad: ciudad, area: area, fecha: fechaCompleta, ot: ov, comentarios: comentarios }
                : { opcion: 'preRegistroVentas', cliente: cliente, ciudad: ciudad, area: area, fecha: fechaCompleta, ot: ov, comentarios: comentarios, engineer: engineer };

            $('#btnPreRegistroVentas').prop('disabled', true);
            $.ajax({
                url: 'acciones_solicitud.php',
                method: 'POST',
                dataType: 'json',
                data: datos,
                success: function(data) {
                    $('#btnPreRegistroVentas').prop('disabled', false);
                    if (data.status === 'success') {
                        $('#modalPreRegistroVentas').modal('hide');
                        Swal.fire({ title: esEdicion ? "Pre-registro actualizado!" : "Pre-registro creado!", icon: "success", draggable: true });
                        cargarDisponibilidad();
                    } else {
                        Swal.fire({ title: "No se pudo guardar", text: data.message || '', icon: "error" });
                    }
                },
                error: function() {
                    $('#btnPreRegistroVentas').prop('disabled', false);
                    Swal.fire({ title: "Error al guardar", icon: "error" });
                }
            });
        }

        // Cancela el pre-registro abierto en el modal (pasa a CanceladaV)
        function cancelarPreRegistroExistente() {
            var id = ($('#regId').val() || '').trim();
            if (!id) return;
            Swal.fire({
                title: "¿Cancelar este pre-registro?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                confirmButtonText: "Sí, cancelar",
                cancelButtonText: "No"
            }).then(function(result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: 'acciones_solicitud.php',
                    method: 'POST',
                    dataType: 'json',
                    data: { opcion: 'cancelarPreRegistroVentas', id: id },
                    success: function(data) {
                        if (data.status === 'success') {
                            $('#modalPreRegistroVentas').modal('hide');
                            Swal.fire({ title: "Pre-registro cancelado", icon: "success" });
                            cargarDisponibilidad();
                        } else {
                            Swal.fire({ title: "No se pudo cancelar", text: data.message || '', icon: "error" });
                        }
                    },
                    error: function() {
                        Swal.fire({ title: "Error al cancelar", icon: "error" });
                    }
                });
            });
        }

        // ================ UTILIDADES ================
        function convertirTexto(e) {
            e.value = e.value.toUpperCase().normalize("NFD").replace(/[̀-ͯ]/g, "");
        }

        function getCookie(name) {
            const cookies = new URLSearchParams(document.cookie.replace(/; /g, '&'));
            return cookies.get(name) || undefined;
        }
    </script>
</body>
</html>
