<!DOCTYPE html>

<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>ACTIVOS MESS</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', system-ui, sans-serif; }
        .card-equipment { border: 1px solid #e0e6ed; border-radius: 12px; background: #fff; transition: all 0.2s; }
        .card-equipment:hover { border-color: #0d6efd; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        
        /* Estilos de información */
        .info-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 700; }
        .info-value { font-size: 0.85rem; color: #2d3436; font-weight: 600; display: block; }
        
        /* Badges de Área */
        .area-tag { font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: #f8f9fa; border: 1px solid #dee2e6; }
        
        /* Thumbnail */
        .img-container { position: relative; width: 80px; height: 80px; }
        .img-preview { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; }
        .photo-count { position: absolute; bottom: -5px; right: -5px; background: #000; color: #fff; font-size: 0.6rem; padding: 2px 5px; border-radius: 50%; }
    </style>
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
                    
                    <div class="row mb-4 align-items-end">
                        <div class="col">
                            <h2 class="fw-bold text-dark mb-1">Panel de Control</h2>
                            <p class="text-muted mb-0">Tienes <strong>3 equipos</strong> pendientes de asignación hoy.</p>
                        </div>
                        <div class="col-auto">
                            <a href="registro_simple.php" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Nuevo Registro</a>
                        </div>
                    </div>

                    <div class="row g-3">
                        
                        <div class="col-12">
                            <div class="card card-equipment p-3">
                                <div class="row align-items-center">
                                    <div class="col-auto text-center border-end pe-4">
                                        <span class="info-label">Folio</span>
                                        <span class="d-block fw-bold text-primary">#MET-2025-01</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="img-container">
                                            <img src="https://images.unsplash.com/photo-1581092160562-40aa08e78837?auto=format&fit=crop&q=80&w=200" class="img-preview">
                                            <span class="photo-count">2</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <span class="info-label">Cliente</span>
                                                <span class="info-value">AeroEstructuras de México</span>
                                                <span class="area-tag"><i class="bi bi-rulers"></i> Dimensional</span>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="info-label">Equipo / Marca / Modelo</span>
                                                <span class="info-value">Calibrador Vernier - Mitutoyo</span>
                                                <span class="small text-muted">Mdl: 500-196-30 | S/N: 2204551</span>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="info-label">Fecha Compromiso</span>
                                                <span class="info-value text-danger"><i class="bi bi-clock-history"></i> 20 Dic 2025</span>
                                                <small class="text-muted italic">"El botón de encendido falla"</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto border-start ps-4">
                                        <button class="btn btn-outline-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalAsignar" onclick="asignar(1)">
                                            Asignar Ing.
                                        </button>
                                        <button class="btn btn-light btn-sm w-100">Ver Ficha</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card card-equipment p-3 border-start border-4 border-warning">
                                <div class="row align-items-center">
                                    <div class="col-auto text-center border-end pe-4">
                                        <span class="info-label">Folio</span>
                                        <span class="d-block fw-bold text-primary">#MET-2025-02</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="img-container">
                                            <img src="https://images.unsplash.com/photo-1590218126049-06628867c293?auto=format&fit=crop&q=80&w=200" class="img-preview">
                                            <span class="photo-count">3</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <span class="info-label">Cliente</span>
                                                <span class="info-value">Energía Limpia del Norte</span>
                                                <span class="area-tag"><i class="bi bi-lightning-charge"></i> Eléctrica</span>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="info-label">Equipo / Marca / Modelo</span>
                                                <span class="info-value">Multímetro Digital - Fluke</span>
                                                <span class="small text-muted">Mdl: 87V | S/N: FL-992011</span>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="info-label">Fecha Compromiso</span>
                                                <span class="info-value"><i class="bi bi-calendar-check"></i> 24 Dic 2025</span>
                                                <small class="text-muted italic">"Requiere calibración con certificado trazable"</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto border-start ps-4">
                                        <button class="btn btn-outline-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalAsignar" onclick="asignar(2)">
                                            Asignar Ing.
                                        </button>
                                        <button class="btn btn-light btn-sm w-100">Ver Ficha</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card card-equipment p-3">
                                <div class="row align-items-center">
                                    <div class="col-auto text-center border-end pe-4">
                                        <span class="info-label">Folio</span>
                                        <span class="d-block fw-bold text-primary">#MET-2025-03</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="img-container">
                                            <img src="https://images.unsplash.com/photo-1584036561566-baf8f5f1b144?auto=format&fit=crop&q=80&w=200" class="img-preview">
                                            <span class="photo-count">1</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <span class="info-label">Cliente</span>
                                                <span class="info-value">Farmacéutica Global</span>
                                                <span class="area-tag"><i class="bi bi-thermometer-half"></i> Temperatura</span>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="info-label">Equipo / Marca / Modelo</span>
                                                <span class="info-value">Termohigrómetro - Testo</span>
                                                <span class="small text-muted">Mdl: 608-H1 | S/N: T-8822</span>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="info-label">Fecha Compromiso</span>
                                                <span class="info-value text-success"><i class="bi bi-calendar-check"></i> 05 Ene 2026</span>
                                                <small class="text-muted italic">"Sensor de humedad parece descalibrado"</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto border-start ps-4">
                                        <button class="btn btn-outline-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalAsignar" onclick="asignar(33)">
                                            Asignar Ing.
                                        </button>
                                        <button class="btn btn-light btn-sm w-100">Ver Ficha</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal fade" id="modalAsignar" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <form action="actualizar_estatus.php" method="POST">
                                <div class="modal-body p-4">
                                    <h5 class="fw-bold mb-3">Asignar Responsable</h5>
                                    
                                    <input type="hidden" name="equipo_id" id="equipo_id_modal">

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">INGENIERO ASIGNADO</label>
                                        <select name="ingeniero_id" class="form-select border-primary" required>
                                            <option value="">Seleccione un técnico...</option>
                                            <option value="1">Ing. Alberto García</option>
                                            <option value="2">Ing. María Rodríguez</option>
                                            <option value="3">Ing. Carlos López</option>
                                        </select>
                                    </div>

                                    <p class="small text-muted">
                                        <i class="bi bi-info-circle"></i> Al confirmar, el estatus cambiará automáticamente a <strong>DIAGNÓSTICO</strong>.
                                    </p>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary px-4">Confirmar Asignación</button>
                                </div>
                            </form>
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
        function asignar(idEquipo) {
            // 1. Buscamos el campo oculto dentro del modal por su ID
            const inputId = document.getElementById('equipo_id_modal');
            
            // 2. Asignamos el ID del equipo que recibimos del botón
            inputId.value = idEquipo;
            
            // 3. Opcional: Podrías cambiar el título del modal dinámicamente
            console.log("Preparando asignación para el equipo ID: " + idEquipo);
            
            // El modal de Bootstrap se abre automáticamente por el atributo data-bs-toggle
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

    </script>
</body>

</html>
