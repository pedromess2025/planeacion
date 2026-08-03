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