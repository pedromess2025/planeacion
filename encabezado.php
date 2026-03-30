<!-- Topbar -->
<nav class = "navbar navbar-expand navbar-light bg-white topbar mb-2 static-top shadow">
<!-- Enlace a Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Enlace a Bootstrap JS (necesario para el funcionamiento del modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Enlace a FontAwesome para los íconos (si usas íconos) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    #btnNotificaciones {
        padding: 0.05rem 0.12rem !important;
        margin: 0 !important;
        min-width: 0;
        line-height: 1 !important;
        border-radius: 0.2rem;
        border: 0;
        background: transparent;
    }

    #btnNotificaciones:focus,
    #btnNotificaciones:focus-visible {
        outline: none;
        box-shadow: 0 0 0 0.12rem rgba(255, 0, 0, 0.25);
    }

    .noti-icon-wrap {
        position: relative;
        display: inline-block;
        line-height: 1;
    }

    .noti-badge {
        position: absolute;
        top: -8px;
        right: -9px;
        min-width: 12px;
        height: 12px;
        padding: 0 2px !important;
        font-size: 0.42rem !important;
        line-height: 12px;
        font-weight: 700;
        pointer-events: none;
    }
</style>

<!-- Topbar Navbar -->
<ul class = "navbar-nav ml-auto">
    <!-- Boton de Notificaciones 
    <li class="nav-item">
        <button class="btn btn-link nav-link fw-bold text-dark" type="button" data-bs-toggle="modal" data-bs-target="#notificacionesModal">
            <span class="noti-icon-wrap">
                <i class="fas fa-bell text-dark"></i>
                <span id="badgeNotificaciones" class="badge rounded-pill bg-danger d-none noti-badge">0</span>
            </span>
        </button>
    </li>
    -->
    <!-- Nav Item - Search Dropdown (Visible Only XS) -->
    <li class = "nav-item dropdown no-arrow d-sm-none">
        <a class = "nav-link dropdown-toggle" href = "#" id = "searchDropdown" role = "button"
            data-toggle = "dropdown" aria-haspopup = "true" aria-expanded = "false">
            <i class = "fas fa-search fa-fw"></i>
        </a>
        
    </li>    
    <!-- Nav Item - User Information -->
    <li class = "nav-item dropdown no-arrow">
        <a class = "nav-link dropdown-toggle" href = "#" id = "userDropdown" role = "button"
            data-toggle = "dropdown" aria-haspopup = "true" aria-expanded = "false">
            <span class = "mr-2 d-none d-lg-inline text-gray-600 small">
                <?php echo $_COOKIE['nombredelusuario']?>
            </span>
            <?php
            $currentURL = $_SERVER['REQUEST_URI']; // Obtiene la ruta actual de la URL

            if (strpos($currentURL, "/incidencias/SalasDeJuntas/") !== false || 
                strpos($currentURL, "/incidencias/inicio") !== false) {
                echo '<img class="img-profile rounded-circle" 
                    src="/incidencias/img/undraw_profile.svg" 
                    style="width: 100%;">';
            } else {
                echo '<img class="img-profile rounded-circle" 
                    src="/incidencias/img/undraw_profile.svg"  
                    style="width: 100%;">';
            }
            ?>
        </a>
        <!-- Dropdown - User Information -->
        <div class = "dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby = "userDropdown">
            <div class = "dropdown-divider"></div>
            <a class = "dropdown-item" href = "#" data-toggle = "modal" data-target = "#logoutModalN">
                <i class = "fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                Salir
            </a>
        </div>
    </li>
    <!-- Modal de Notificaciones -->
    <div class = "modal fade" id = "notificacionesModal" tabindex = "-1" role = "dialog" aria-labelledby = "notificacionesModalLabel" aria-hidden = "true">
        <div class = "modal-dialog" role = "document">
            <div class = "modal-content">
                <div class = "modal-header">
                    <h5 class = "modal-title" id = "notificacionesModalLabel">Notificaciones</h5>
                    <button class = "btn-close" type = "button" data-bs-dismiss = "modal" aria-label = "Close"></button>
                </div>
                <div class = "modal-body">
                    <div id="notificacionesContenido">No tienes nuevas notificaciones.</div>
                </div>
                <div class = "modal-footer">
                    <button class = "btn btn-secondary" type = "button" data-bs-dismiss = "modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</ul>
    <!-- Logout Modal-->
    <div class = "modal fade" id = "logoutModalN" tabindex = "-1" role = "dialog" aria-labelledby = "exampleModalLabel"aria-hidden = "true">
        <div class = "modal-dialog" role = "document">
            <div class = "modal-content border-left-danger">
                <div class = "modal-header">
                    <h4 class = "modal-title" id = "exampleModalLabel"> Cerrar sesión </h4>
                    <button class = "close" type = "button" data-dismiss = "modal" aria-label = "Close">
                        <span aria-hidden = "true">X</span>
                    </button>
                </div>
                <div class = "modal-body"><h5><b>¿Estás seguro?</b></h5></div>
                <div class = "modal-footer">
                    <button class = "btn btn-warning" type = "button" data-dismiss = "modal">Cancelar</button>
                    <a class = "btn btn-danger" href = "logout">Salir</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        cargarTotalNotificaciones();

        var notificacionesModal = document.getElementById('notificacionesModal');
        if (notificacionesModal) {
            notificacionesModal.addEventListener('show.bs.modal', function () {
                cargarNotificaciones();
            });
        }
    });

    //Funcion para leer cookies
    function getCookie(name) {
        let value = "; " + document.cookie;
        let parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null; // Si no encuentra la cookie, retorna null
    }

    // Funcion para mostrar el modal de notificaciones
    function mostrarNotificaciones() {
        var modalElement = document.getElementById('notificacionesModal');
        if (!modalElement) {
            return;
        }

        var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
    }

    function cargarNotificaciones() {
        $.ajax({
            url: 'acciones_notificaciones',
            method: 'POST',
            data: { accion: 'cargarNotificaciones' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var notificaciones = response.notificaciones;
                    var contenido = '';

                    if (notificaciones.length > 0) {
                        notificaciones.forEach(function(notificacion) {
                            contenido += '<div class="alert alert-info d-flex justify-content-between align-items-center">';
                            contenido += '<span>' + notificacion.mensaje + '</span>';
                            contenido += '<button class="btn btn-sm btn-primary" onclick="marcarNotificacionLeida(' + notificacion.id + ')">Marcar como leída</button>';
                            contenido += '</div>';
                        });
                    } else {
                        contenido = 'No tienes nuevas notificaciones.';
                    }

                    $('#notificacionesContenido').html(contenido);
                } else {
                    $('#notificacionesContenido').html('Error al cargar notificaciones.');
                }
            },
            error: function() {
                $('#notificacionesContenido').html('Error al cargar notificaciones.');
            }
        });
    }

    function cargarTotalNotificaciones() {
        var badge = $('#badgeNotificaciones');

        $.ajax({
            url: 'acciones_notificaciones',
            method: 'POST',
            dataType: 'json',
            data: { accion: 'contarNotificaciones' },
            success: function(response) {
                if (!response.success) {
                    badge.addClass('d-none').text('0');
                    return;
                }

                var total = parseInt(response.total, 10) || 0;
                if (total > 0) {
                    badge.removeClass('d-none').text(total > 99 ? '99+' : total);
                } else {
                    badge.addClass('d-none').text('0');
                }
            },
            error: function() {
                badge.addClass('d-none').text('0');
            }
        });
    }

    function marcarNotificacionLeida(idNotificacion) {
        $.ajax({
            url: 'acciones_notificaciones',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'marcarLeida',
                idNotificacion: idNotificacion
            },
            success: function(response) {
                if (response.success) {
                    cargarNotificaciones();
                    cargarTotalNotificaciones();
                }
            }
        });
    }
    </script>
</nav>
<!-- End of Topbar -->