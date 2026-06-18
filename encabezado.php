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
    <!-- Boton de Toggle Tema -->
    <li class="nav-item d-flex align-items-center mr-2">
        <button class="btn theme-toggle-btn" type="button" id="btnToggleTheme" title="Cambiar tema">
            <i class="fas fa-moon"></i>
        </button>
    </li>
    <!-- Boton de Notificaciones -->
    <li class="nav-item">
        <button class="btn btn-link nav-link fw-bold text-dark" type="button" id="btnNotificaciones" onclick="mostrarNotificacionesFlotantes()">
            <span class="noti-icon-wrap">
                <i class="fas fa-bell text-dark"></i>
                <span id="badgeNotificaciones" class="badge rounded-pill bg-danger d-none noti-badge">0</span>
            </span>
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
        cargarNotificaciones(false);
        // Refrescar el badge cada 30 segundos
        setInterval(function() {
            cargarNotificaciones(false);
        }, 30000);
    });

    function getCookie(name) {
        const cookies = new URLSearchParams(document.cookie.replace(/; /g, '&'));
        return cookies.get(name) || undefined;
    }

    function escapeHtml(texto) {
        return String(texto || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Devuelve la clase FontAwesome según el sistema que originó la notificación
    function obtenerIconoNotificacion(sistema) {
        if (sistema === 'entradasEq') return 'fas fa-tools';
        if (sistema === 'planeacion') return 'fas fa-calendar-alt';
        return 'fas fa-bell';
    }

    function limpiarStackNotificaciones() {
        $('#notificationStack').empty();
    }

    function renderNotificacionFlotante(notificacion) {
        var stack = $('#notificationStack');
        var sistema   = escapeHtml(notificacion.sistema || 'General');
        var archivo   = escapeHtml(notificacion.archivo || '');
        var fecha     = escapeHtml(notificacion.fecha_actualizacion || notificacion.fecha || '');
        var recordar  = escapeHtml(notificacion.recordar || '');
        var creadoPor = escapeHtml(notificacion.usuario_actualiza_nombre || '');
        var iconoSistema = obtenerIconoNotificacion(sistema.toLowerCase());
        var id        = parseInt(notificacion.id, 10) || 0;
        var idRegistro = parseInt(notificacion.id_registro_referencia, 10) || 0;

        var html = '';
        html += '<div class="toast show border-0 shadow-sm mb-3" data-notificacion-id="' + id + '" role="alert" aria-live="assertive" aria-atomic="true">';
        html += '  <div class="toast-body p-2">';
        html += '      <div class="d-flex justify-content-between align-items-center">';
        html += '          <div class="d-flex align-items-center flex-wrap">';
        html += '              <span class="badge rounded-pill bg-primary text-white px-3 py-2 mr-2 mb-1">';
        html += '                  <i class="' + iconoSistema + ' mr-2"></i>' + sistema;
        html += '              </span>';
        html += '              <div class="mb-1">';
        html += '                  <span class="text-dark font-weight-bold mr-3" style="font-size: .95rem; line-height:1.1;">' + (creadoPor ? creadoPor + ' - ' : '') + recordar + '</span>';
        html += '                  <span class="text-muted" style="font-size: .90rem; white-space: nowrap;"><i class="far fa-calendar-alt mr-1"></i>' + fecha + '</span>';
        html += '              </div>';
        html += '          </div>';
        html += '          <button class="btn btn-sm btn-light border border-success text-success px-2 py-1" title="Marcar como leída" onclick="marcarNotificacionLeida(' + id + ', ' + idRegistro + ', \'' + archivo + '\')">';
        html += '              <i class="fas fa-check fa-sm"></i>';
        html += '          </button>';
        html += '      </div>';
        html += '  </div>';
        html += '</div>';

        var toast = $(html);
        stack.append(toast);

        // Auto-ocultar el toast después de 5 segundos
        setTimeout(function() {
            toast.fadeOut(1000, function() { $(this).remove(); });
        }, 5000);
    }

    function mostrarNotificacionesFlotantes() {
        cargarNotificaciones(true);
    }

    // Carga notificaciones del backend.
    // mostrarFlotantes=true: renderiza toasts. mostrarFlotantes=false: solo actualiza el badge.
    function cargarNotificaciones(mostrarFlotantes) {
        var badge = $('#badgeNotificaciones');
        $.ajax({
            url: 'acciones_notificaciones.php',
            method: 'POST',
            data: { accion: 'cargarNotificaciones', sistema: 'planeacion' },
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
                                recordar: 'No tienes nuevas notificaciones.',
                                fecha_actualizacion: ''
                            });
                        }
                    }
                }
            }
        });
    }

    // Marca la notificación como leída. No navega al registro porque las páginas
    // de planeación no usan URL con id individual por servicio.
    function marcarNotificacionLeida(idNotificacion, idRegistro, archivo) {
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
                }
            }
        });
    }
    </script>

    <script>
    (function() {
        var saved = localStorage.getItem('planeacion-theme');
        if (saved === 'dark') {
            document.body.classList.add('theme-dark');
            document.documentElement.classList.add('theme-dark');
        }
        function updateIcon() {
            var icon = document.querySelector('#btnToggleTheme i');
            if (!icon) return;
            var isDark = document.body.classList.contains('theme-dark');
            icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateIcon();
            var btn = document.getElementById('btnToggleTheme');
            if (btn) {
                btn.addEventListener('click', function() {
                    document.body.classList.toggle('theme-dark');
                    document.documentElement.classList.toggle('theme-dark');
                    var isDark = document.body.classList.contains('theme-dark');
                    localStorage.setItem('planeacion-theme', isDark ? 'dark' : 'light');
                    updateIcon();
                });
            }
        });
    })();
    </script>
</nav>
<!-- End of Topbar -->