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

<!-- Topbar Navbar -->
<ul class = "navbar-nav ml-auto">
    <!-- Boton de Notificaciones  -->
    <li class="nav-item">
        <button class="btn btn-link nav-link fw-bold text-dark position-relative" type="button" id="btnNotificaciones" onclick="mostrarNotificacionesFlotantes()">
            <i class="fas fa-bell text-dark"></i>
            <span id="badgeNotificaciones" class="position-absolute badge rounded-pill bg-danger d-none" style="top: 2px; right: 2px; font-size: .62rem; min-width: 1rem; padding: .2em .35em; line-height: 1; pointer-events: none;">0</span>
        </button>
    </li>
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
</ul>
    <div id="notificationStack" class="position-fixed top-0 end-0 p-3" style="z-index: 1080; max-width: 360px;"></div>
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
                <div class = "modal-body"><h5><b>¿Estas seguro?</b></h5></div>
                <div class = "modal-footer">
                    <button class = "btn btn-warning" type = "button" data-dismiss = "modal">Cancelar</button>
                    <a class = "btn btn-danger" href = "logout">Salir</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        cargarNotificaciones(false);
        setInterval(function() {
            cargarNotificaciones(false);
        }, 30000);
    });

    //Funcion para leer cookies
    function getCookie(name) {
        let value = "; " + document.cookie;
        let parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
        return null; // Si no encuentra la cookie, retorna null
    }

    function escapeHtml(texto) {
        return String(texto || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function limpiarStackNotificaciones() {
        $('#notificationStack').empty();
    }

    // Función para renderizar una notificación flotante
    function renderNotificacionFlotante(notificacion) {
        var stack = $('#notificationStack');
        var iniciales = escapeHtml(notificacion.iniciales || 'NA');
        var nota = escapeHtml(notificacion.nota || notificacion.mensaje || 'Sin nota');
        var accion = escapeHtml(notificacion.accion || '');
        var fecha = escapeHtml(notificacion.fecha_actualizacion || notificacion.fecha || '');
        var id = parseInt(notificacion.id, 10) || 0;
        var idRegistro = parseInt(notificacion.id_registro_referencia, 10) || 0;

        var html = '';
        html += '<div class="toast show border-0 shadow-sm mb-2" data-notificacion-id="' + id + '" role="alert" aria-live="assertive" aria-atomic="true">';
        html += '  <div class="toast-body p-2">';
        html += '      <div class="d-flex justify-content-between align-items-start gap-4">';
        html += '          <div class="d-flex align-items-start gap-4">';
        html += '              <span class="badge rounded-pill bg-primary mt-1">' + iniciales + '</span>';
        html += '              <div>';
        html += '                  <div class="small text-dark fw-semibold">' + accion + '</div>';
        html += '                  <div class="small text-muted">' + fecha + '</div>';
        html += '              </div>';
        html += '          </div>';
        html += '          <button class="btn btn-sm btn-outline-success" title="Marcar como leída" aria-label="Marcar como leída" onclick="marcarNotificacionLeida(' + id + ', ' + idRegistro + ')">';
        html += '              <i class="fas fa-check"></i>';
        html += '          </button>';
        html += '      </div>';
        html += '  </div>';
        html += '</div>';

        var toast = $(html);
        stack.append(toast);

        setTimeout(function() {
            toast.fadeOut(10000, function() {
                $(this).remove();
            });
        }, 5000);
    }

    function mostrarNotificacionesFlotantes() {
        cargarNotificaciones(true);
    }

    function cargarNotificaciones(mostrarFlotantes) {
        var badge = $('#badgeNotificaciones');
        $.ajax({
            url: 'acciones_notificaciones.php',
            method: 'POST',
            data: { accion: 'cargarNotificaciones' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var notificaciones = response.notificaciones;
                    var total = parseInt(response.total || 0, 10);

                    if (total > 0) {
                        badge.removeClass('d-none').text(total > 99 ? '99+' : total);
                    } else {
                        badge.addClass('d-none').text('0');
                    }

                    if (mostrarFlotantes === true) {
                        limpiarStackNotificaciones();
                        if (notificaciones.length > 0) {
                            notificaciones.forEach(function(notificacion) {
                                renderNotificacionFlotante(notificacion);
                            });
                        } else {
                            renderNotificacionFlotante({
                                id: 0,
                                iniciales: 'OK',
                                nota: 'No tienes nuevas notificaciones.',
                                fecha_actualizacion: ''
                            });
                        }
                    }
                }
            }
        });
    }

    function marcarNotificacionLeida(idNotificacion, idRegistro) {
        $.ajax({
            url: 'acciones_notificaciones.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'marcarLeida',
                idNotificacion: idNotificacion
            },
            success: function(response) {
                if (response.success) {
                    $('[data-notificacion-id="' + idNotificacion + '"]').fadeOut(200, function() {
                        $(this).remove();
                    });
                    cargarNotificaciones(false);
                    if (parseInt(idRegistro, 10) > 0) {
                        window.location.href = 'entradaTareas.php?id=' + parseInt(idRegistro, 10);
                    }
                }
            }
        });
    }
    </script>
</nav>
<!-- End of Topbar -->