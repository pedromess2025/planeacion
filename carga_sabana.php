<?php
    session_start();
    include 'conn.php';
    // Acceso especial (accesos_especiales, planeacion/verCargaArchivos). Lista corta a propósito:
    // esta pantalla hace TRUNCATE de la sábana operativa y reemplaza el reporte de OT internas.
    exigeAccesoEspecial($conn, 'planeacion', 'verCargaArchivos');

    // Estado del reporte de OT internas, para que se vea de entrada si está al día.
    // El grid de Disponibilidad solo pinta las OT que estén en esta tabla: si nadie recarga el
    // reporte, el morado desaparece del tablero sin ningún aviso (ya pasó).
    $otEstado = ['existe' => false, 'filas' => 0, 'min' => null, 'max' => null, 'dias' => null];
    if ($resOt = @$conn->query("SELECT COUNT(*) filas, MIN(DATE(fecha_inicio)) mn, MAX(DATE(fecha_inicio)) mx,
                                       DATEDIFF(CURDATE(), MAX(DATE(fecha_inicio))) dias
                                FROM planeacion_ot_interna")) {
        $otEstado['existe'] = true;
        if ($f = $resOt->fetch_assoc()) {
            $otEstado['filas'] = (int)$f['filas'];
            $otEstado['min']   = $f['mn'];
            $otEstado['max']   = $f['mx'];
            $otEstado['dias']  = $f['dias'];
        }
    }
    $eOt = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>CARGA - SÁBANA OPERATIVA</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <?php include 'menu.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'encabezado.php'; ?>
                
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-xl-10">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-upload"></i> Generador de Sábana Operativa (2 Archivos)</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">Sube los archivos maestros para cruzar la información y actualizar la base de datos.</p>
                                    
                                    <form id="form-sabana" enctype="multipart/form-data">
                                        <div class="row mb-3">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-weight-bold text-primary">1. Archivo Maestro (REOT)</label>
                                                <input class="form-control" type="file" id="archivo_reot" name="archivo_reot" accept=".csv" required>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label font-weight-bold text-info">2. Archivo Financiero (INFOOTOVFACT)</label>
                                                <input class="form-control" type="file" id="archivo_info" name="archivo_info" accept=".csv" required>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center mt-4">
                                            <button type="submit" id="btn-procesar" class="btn btn-primary btn-lg px-5">
                                                <i class="fas fa-cogs"></i> Procesar y Generar Sábana
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Contenedor de la Barra de Progreso -->
                                    <div id="contenedor-progreso" class="mt-5 text-center" style="display: none;">
                                        <h5 id="texto-progreso" class="font-weight-bold text-start text-dark">Preparando archivos en el servidor... <span class="float-end">0%</span></h5>
                                        <div class="progress mb-2" style="height: 25px;">
                                            <div id="barra-progreso" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%; font-size: 1rem;"></div>
                                        </div>
                                        <p id="detalle-registros" class="text-muted font-weight-bold">Por favor, no cierres esta ventana.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- ============ REPORTE DE OT INTERNAS (SERV INTERNOS) ============ -->
                            <!-- Proceso independiente del de la sábana: otro archivo, otra tabla y otro
                                 destino. Alimenta las celdas moradas "OT interna" del tablero de
                                 Disponibilidad de Ingenieros. Recargar NO duplica (UPSERT por orderCode). -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clipboard-list"></i> Reporte de OT internas (Servicios internos)</h6>
                                    <a href="disponibilidadIngenieros" class="small"><i class="fas fa-user-clock"></i> Ver tablero</a>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">
                                        Es lo que pinta las celdas moradas <b>"OT interna"</b> en Disponibilidad de Ingenieros.
                                        Sube el CSV tal cual sale del sistema de OTs; volver a cargarlo <b>no duplica</b>:
                                        actualiza las que ya existen y agrega las nuevas.
                                    </p>

                                    <?php
                                        $claseOt = 'border-left: 4px solid #4e73df; background:#f8f9fc;';
                                        if (!$otEstado['existe'] || $otEstado['filas'] === 0)          $claseOt = 'border-left: 4px solid #e74a3b; background:#fdf3f2;';
                                        elseif ($otEstado['dias'] !== null && $otEstado['dias'] > 7)   $claseOt = 'border-left: 4px solid #e0a800; background:#fffbf0;';
                                    ?>
                                    <div class="mb-3 p-3 rounded" style="<?php echo $claseOt; ?>">
                                        <?php if (!$otEstado['existe']): ?>
                                            <b class="text-danger"><i class="fas fa-exclamation-triangle"></i> La tabla no existe en esta base de datos.</b><br>
                                            <span class="text-muted">Hay que crearla antes de poder cargar el reporte. Mientras tanto el tablero no pinta ninguna OT interna.</span>
                                        <?php elseif ($otEstado['filas'] === 0): ?>
                                            <b class="text-danger"><i class="fas fa-exclamation-triangle"></i> No hay ningún reporte cargado.</b><br>
                                            <span class="text-muted">El tablero no está mostrando ninguna OT interna.</span>
                                        <?php else: ?>
                                            <b><i class="fas fa-database"></i> Reporte actual:</b>
                                            <?php echo $otEstado['filas']; ?> OT
                                            &nbsp;·&nbsp; fechas de inicio del <b><?php echo $eOt($otEstado['min']); ?></b>
                                            al <b><?php echo $eOt($otEstado['max']); ?></b>
                                            <?php if ($otEstado['dias'] !== null && $otEstado['dias'] > 7): ?>
                                                <br><span class="text-warning-emphasis">
                                                    <i class="fas fa-clock"></i> <b>Lleva <?php echo (int)$otEstado['dias']; ?> días sin actualizarse.</b>
                                                    Las OT posteriores a esa fecha no aparecen en el tablero.
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <form id="form-ot" enctype="multipart/form-data">
                                        <div class="row align-items-end">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label font-weight-bold text-primary">Archivo de OT internas (SERV INTERNOS)</label>
                                                <input class="form-control" type="file" id="archivo_ot" name="archivo" accept=".csv" required>
                                                <small class="text-muted">Encabezados en inglés: <code>orderCode</code>, <code>status</code>, <code>Engineers</code>, <code>startDate</code>, <code>dueDate</code>…</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <button type="submit" id="btn-ot" class="btn btn-primary w-100">
                                                    <i class="fas fa-cloud-upload-alt"></i> Cargar OT internas
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div id="resultado-ot" class="mt-2" style="display:none;"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto"><span>Copyright &copy; MESS 2026</span></div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>    
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="js/sb-admin-2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        $('#form-sabana').on('submit', function(e) {
            e.preventDefault(); 
            
            let formData = new FormData(this);
            formData.append('accion', 'preparar'); 
            
            let btn = $('#btn-procesar');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Subiendo y Analizando...');
            $('#contenedor-progreso').slideDown();

            $.ajax({
                url: 'procesar_sabana.php',
                type: 'POST',
                data: formData,
                contentType: false, 
                processData: false, 
                dataType: 'json',
                success: function(res) {
                    if(res.status === 'success') {
                        $('#texto-progreso').html(`Cruzando y calculando información... <span class="float-end">0%</span>`);
                        procesarLote(1, res.total_lineas, 500); 
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        btn.prop('disabled', false).html('<i class="fas fa-cogs"></i> Procesar y Generar Sábana');
                        $('#contenedor-progreso').hide();
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Hubo un problema de comunicación con el servidor.', 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-cogs"></i> Procesar y Generar Sábana');
                    $('#contenedor-progreso').hide();
                }
            });
        });

        function procesarLote(offset, total, limit) {
            $.ajax({
                url: 'procesar_sabana.php',
                type: 'POST',
                dataType: 'json',
                data: { accion: 'procesar', offset: offset, limit: limit },
                success: function(res) {
                    if(res.status === 'success') {
                        let nuevoOffset = offset + res.procesados;
                        let porcentaje = Math.round((nuevoOffset / total) * 100);
                        if(porcentaje > 100) porcentaje = 100;

                        $('#barra-progreso').css('width', porcentaje + '%');
                        $('#texto-progreso span').text(porcentaje + '%');
                        $('#detalle-registros').text(`${nuevoOffset} de ${total} registros insertados`);

                        if (nuevoOffset <= total) {
                            procesarLote(nuevoOffset, total, limit);
                        } else {
                            finalizarProceso();
                        }
                    }
                }
            });
        }

        // ============ REPORTE DE OT INTERNAS ============
        // Proceso aparte del de la sábana: es un solo archivo chico, así que va de un tiro
        // (sin lotes ni barra de progreso). La importación real vive en lib_ot_interna.php,
        // compartida con el comando `php cargar_ot_interna.php`.
        function escOt(s) {
            return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                                             .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        $('#form-ot').on('submit', function(e) {
            e.preventDefault();
            var btn = $('#btn-ot').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
            $('#resultado-ot').hide();

            $.ajax({
                url: 'procesar_ot_interna.php', type: 'POST', dataType: 'json',
                data: new FormData(this), contentType: false, processData: false,
                success: function(res) {
                    if (res.status !== 'success') {
                        Swal.fire('No se pudo cargar', res.message || 'Error desconocido', 'error');
                        return;
                    }
                    pintarResultadoOt(res);
                    Swal.fire({
                        icon: 'success', title: 'Reporte cargado',
                        html: '<b>' + res.filas + '</b> OT internas cargadas.<br>Ya se reflejan en el tablero de Disponibilidad.'
                    });
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.status === 401 ? 'Tu sesión expiró. Vuelve a iniciar sesión.'
                                                          : 'Hubo un problema de comunicación con el servidor.', 'error');
                },
                complete: function() {
                    $('#btn-ot').prop('disabled', false).html('<i class="fas fa-cloud-upload-alt"></i> Cargar OT internas');
                }
            });
        });

        function pintarResultadoOt(res) {
            var h = '<div class="alert alert-success mb-2"><b><i class="fas fa-check-circle"></i> ' +
                    res.filas + ' OT cargadas</b> desde <i>' + escOt(res.archivo) + '</i>';
            if (res.rango && res.rango.min) {
                h += '<br>Fechas de inicio: <b>' + escOt(res.rango.min) + '</b> al <b>' + escOt(res.rango.max) + '</b>';
            }
            if (res.omitidas > 0)  { h += '<br><span class="text-muted">Renglones omitidos (sin orderCode): ' + res.omitidas + '</span>'; }
            if (res.sin_fecha > 0) { h += '<br><span class="text-muted">OT sin fecha de inicio (no se pintan): ' + res.sin_fecha + '</span>'; }
            h += '</div>';

            // Los nombres que no casan son la falla silenciosa de este módulo: el cruce con el
            // ingeniero es por texto, así que un typo deja esa OT sin pintarse y nadie se entera.
            var sm = res.sin_match || {}, nombres = Object.keys(sm);
            if (nombres.length) {
                h += '<div class="alert alert-warning mb-2"><b><i class="fas fa-exclamation-triangle"></i> ' +
                     nombres.length + ' nombre(s) del reporte no existen en el sistema.</b>' +
                     '<div class="small mb-2">Esas OT se guardaron, pero <b>no se le pintan a nadie</b> en el tablero. ' +
                     'Casi siempre es un typo en el reporte, o alguien que no está dado de alta como ingeniero activo.</div>' +
                     '<ul class="small mb-0" style="max-height:200px; overflow-y:auto;">';
                nombres.forEach(function(n) { h += '<li>' + escOt(n) + ' <span class="text-muted">(' + sm[n] + ' OT)</span></li>'; });
                h += '</ul></div>';
            }
            $('#resultado-ot').html(h).show();
        }

        function finalizarProceso() {
            $.ajax({
                url: 'procesar_sabana.php',
                type: 'POST',
                dataType: 'json',
                data: { accion: 'finalizar' },
                success: function(res) {
                    $('#barra-progreso').removeClass('progress-bar-animated progress-bar-striped');
                    $('#texto-progreso').html(`¡Cruce Completado! <span class="float-end">100%</span>`);
                    $('#btn-procesar').prop('disabled', false).html('<i class="fas fa-check"></i> Proceso Terminado');
                    $('#form-sabana')[0].reset(); 
                    
                    Swal.fire('¡Sábana Generada!', 'Recuerda ejecutar tu UPDATE posterior para la Fecha OV.', 'success');
                }
            });
        }
    });
    </script>
</body>
</html>