<!DOCTYPE html>

<html>
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE = edge">
    <meta name="viewport" content="width = device-width, initial-scale = 1, shrink-to-fit = no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PLANEACION MESS</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">    

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
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
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="text-center mb-2">
                                <h2 class="fw-bold">Ingreso de Equipo</h2>
                            </div>

                            <div class="card card-minimal shadow-sm p-2">
                                <form action="guardar_entrada.php" method="POST" enctype="multipart/form-data">
                                    
                                    <div class="row mb-4">
                                        <div class="col-md-7">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Cliente</label>
                                            <input type="text" name="cliente" class="form-control" placeholder="Nombre de la empresa" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Área</label>
                                            <select name="area" class="form-select" required>
                                                <option value="SFG">SFG</option>                                                
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Marca</label>
                                            <input type="text" name="marca" class="form-control" placeholder="Marca">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Modelo</label>
                                            <input type="text" name="modelo" class="form-control" placeholder="Modelo">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-uppercase fw-bold text-muted">No. Serie</label>
                                            <input type="text" name="serie" class="form-control" placeholder="N/S" required>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label small text-uppercase fw-bold text-muted">Notas de Recepción / Diagnóstico Preliminar</label>
                                        <textarea name="diagnostico_inicial" class="form-control" rows="2" placeholder="Describa el estado visual o falla reportada..."></textarea>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Promesa de Entrega (Estimado)</label>
                                            <input type="date" name="fecha_estimada" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-uppercase fw-bold text-muted">Fotos del Equipo</label>
                                            <div class="upload-area" onclick="document.getElementById('fotos').click()">
                                                <i class="bi bi-camera-fill fs-3 text-muted"></i>
                                                <p class="mb-0 small text-muted">Haga clic para subir fotos</p>
                                                <input type="file" name="fotos[]" id="fotos" class="file" multiple accept="image/*">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-4">-
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-success text-uppercase">Registrar Entrada de Equipo</button>
                                        </div>                                        
                                    </div>

                                    <input type="hidden" name="estatus" value="ENTRADA">
                                </form>
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
        
        document.getElementById('selectTipoActivo').addEventListener('change', function() {
            var techDiv = document.getElementById('techFields');
            // Asumiendo que el texto de la opción seleccionada contiene 'COMPUTO'
            var textoSeleccionado = this.options[this.selectedIndex].text;
            
            if(textoSeleccionado.includes('COMPUTO')) {
                techDiv.classList.remove('d-none');
            } else {
                techDiv.classList.add('d-none');
            }
                
            //cargar empleados
            //empleadoSolicita('#slcRespoonsable');
            

            // Inicializa Select2 en el campo de responsable
            $('#slcRespoonsable').select2({            
                placeholder: "Seleccione...",
                width: '100%'
            });

            
        });

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
