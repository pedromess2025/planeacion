<?php
    include 'conn.php';
    if($_COOKIE['noEmpleado'] == '' || $_COOKIE['noEmpleado'] == null){
        echo '<script>window.location.assign("../loginMaster")</script>';
    }
?>
<style>        
    .text-bg-orange {
        --bs-bg-opacity: 1;
        background-color: #ff7300ff !important;
        color: #ffffffff !important;
    }
    .btn-logistica{
        --bs-bg-opacity: 1;
        background-color: #bf00ffff !important;
        color: #ffffffff !important;
    }
    /* Grupos colapsables del menú (Calendarios, Logística) */
    .menu-collapse-toggle { cursor: pointer; }
    .menu-collapse-toggle .caret-menu { transition: transform .2s ease; }
    .menu-collapse-toggle.abierto .caret-menu { transform: rotate(180deg); }
    /* Panel tipo tarjeta con los tonos de planeacion (se adapta a tema claro/oscuro) */
    .menu-grupo {
        background-color: var(--card-bg);
        border-radius: .35rem;
        margin: .25rem .8rem .5rem;
        padding: .4rem 0;
        box-shadow: 0 .15rem .5rem rgba(0, 0, 0, .15);
        overflow: hidden;
    }
    .menu-grupo .sub-item {
        display: block;
        color: var(--text) !important;
        text-decoration: none;
        padding: .45rem 1rem .45rem 1.25rem;
        font-size: .85rem;
        white-space: nowrap;
    }
    .menu-grupo .sub-item:hover { background-color: var(--card-soft); color: var(--accent) !important; text-decoration: none; }
    .menu-grupo .sub-item.active { color: var(--accent) !important; font-weight: 700; }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../messbook/inicio">
        <div class="sidebar-brand-icon rotate-n-1">
            <img class="sidebar-card-illustration mb-2" src="img/MESS_07_CuboMess_2.png" width="40" alt="Logo">
        </div>
    </a>
    <!-- Heading -->
    <div class="sidebar-heading">
        <span class="badge text-xl-white">Opciones</span>
    </div>
    <!-- Divider -->
    <hr class="sidebar-divider my-2 alert-light">
    <!-- Nav Item - Pages Collapse Menu -->

    <!--
    //USUARIOS QUE PUEDEN REGISTRAR ACTIVIDADES
    $usuariosRegistran = array(8, 14, 26, 42, 45, 81, 123, 147, 161, 177, 183, 189, 203, 206, 212, 276, 278, 288, 298, 360, 403, 435, 487, 489, 516, 521, 523, 525, 555);
    -->
    <li class="nav-item">
        <a id="verRegistrarActividades" class="nav-link" href="index" style="display:none;">
            <i class="fas fa-fw fa-check text-gray-400 mb-0"></i>
            <span>Registrar actividad</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="seguimiento_actividades">
            <i class="fas fa-fw fa-list text-gray-400"></i>
            <span>Seguimiento de actividades</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="pendientes">
            <i class="fas fa-fw fa-hourglass-half text-warning"></i>
            <span>Actividades por Vencer</span>
        </a>
    </li>
    <hr class="sidebar-divider my-0 alert-light">
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center menu-collapse-toggle" href="#" id="toggleLogistica" role="button" aria-expanded="false">
            <i class="fas fa-fw fa-truck-loading text-gray-400"></i>
            <span>Logística</span>
            <i class="fas fa-angle-down caret-menu ms-auto"></i>
        </a>
        <div id="grupoLogistica" class="menu-grupo" style="display:none;">
            <a class="sub-item" href="sol_logistica">
                <i class="fas fa-fw fa-car"></i> <span>Solicitudes Logística</span>
            </a>
            <a class="sub-item" id="menuEntregasLogistica" href="entregasLogistica" style="display:none;">
                <i class="fas fa-fw fa-truck text-gray-400"></i> <span>Entregas</span>
            </a>
        </div>
    </li>
    <hr class="sidebar-divider my-0 alert-light">
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center menu-collapse-toggle" href="#" id="toggleCalendarios" role="button" aria-expanded="false">
            <i class="fas fa-fw fa-calendar-alt text-gray-400"></i>
            <span>Calendarios</span>
            <i class="fas fa-angle-down caret-menu ms-auto"></i>
        </a>
        <div id="grupoCalendarios" class="menu-grupo" style="display:none;">
            <a class="sub-item" href="verActividadesPlaneadas">
                <i class="fas fa-fw fa-calendar"></i> <span>Actividades planeadas</span>
            </a>
            <a class="sub-item" id="menuCalendarioVentas" href="calendarioVentas" style="display:none;">
                <i class="fas fa-fw fa-store text-warning"></i> <span>Calendario Ventas</span>
            </a>
            <a class="sub-item" href="verActividades">
                <i class="fas fa-fw fa-calendar"></i> <span>Actividades planeadas SCOT</span>
            </a>
        </div>
    </li>
    <hr class="sidebar-divider my-0 alert-light">
    <li class="nav-item">
        <a class="nav-link" href="grafica_planeacion">
            <i class="fas fa-fw fa-chart-bar text-gray-400"></i>
            <span>Resumen por Área</span>
        </a>
    </li>

    <hr class="sidebar-divider my-0 alert-light">
    <li class="nav-item">
        <a class="nav-link" href="Manual Planeacion.pdf" target="_blank">
            <i class="fas fa-fw fa-book text-gray-400"></i>
            <span>Manual de usuario</span>
        </a>
    </li>

    <li class = "nav-item">
        <a class = "nav-link" href = "#" data-toggle = "modal" data-target = "#logoutModalN">
            <i class = "fas fa-sign-out-alt text-gray-100"></i>
            Salir
        </a>
    </li>

    <hr class="sidebar-divider my-1 alert-light">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button> 
    </div>
</ul>

<!-- Funciones Globales -->
<script src="../loginMaster/funcionesGlobales.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        verificarVistaMenu();
        // Mostrar Calendario Ventas solo para los departamentos de Ventas
        // (debe coincidir con DEPTOS_VENTAS en conn.php: 34=Ventas, 35=Ventas SLP, 36=Ventas Zona Nte)
        var deptoMenu = (new URLSearchParams(document.cookie.replace(/; /g, '&'))).get('departamento');
        if (['34', '35', '36'].includes(deptoMenu)) {
            $('#menuCalendarioVentas').show();
        }
        // Mostrar "Entregas / Enlaces" solo para Logística (debe coincidir con DEPTO_LOGISTICA en conn.php: 20)
        if (deptoMenu === '20') {
            $('#menuEntregasLogistica').show();
        }

        // Toggle de los grupos colapsables (Calendarios, Logística) con jQuery puro (evita el
        // conflicto de múltiples versiones de Bootstrap cargadas en las páginas host, que disparaba
        // el collapse 2 veces). Cada toggle alterna el .menu-grupo que le sigue.
        $('.menu-collapse-toggle').on('click', function(e) {
            e.preventDefault();
            var abierto = $(this).toggleClass('abierto').hasClass('abierto');
            $(this).attr('aria-expanded', abierto ? 'true' : 'false');
            $(this).next('.menu-grupo').stop(true, true).slideToggle(150);
        });

        // Abrir el grupo y marcar el ítem activo si estamos en una de sus páginas
        var paginaActual = window.location.pathname.split('/').pop().replace('.php', '');
        var $itemActivo = $('.menu-grupo .sub-item[href="' + paginaActual + '"]');
        if ($itemActivo.length) {
            $itemActivo.addClass('active');
            var $grupo = $itemActivo.closest('.menu-grupo');
            $grupo.show();
            $grupo.prev('.menu-collapse-toggle').addClass('abierto').attr('aria-expanded', 'true');
        }
    });

    // Funcion para validar si puede ver la opción de registro en el menú de actividades (solo encargados pueden verla)
    async function verificarVistaMenu() {
        // 1.Mandamos llamar nuestra función principal. Agregamos await para esperar la respuesta
        const respuesta = await validaOpciones('planeacion', 'verRegistrarActividades');
        
        // 2. Evaluamos la respuesta y aplicamos las acciones a realizar según el caso
        const cuantos = (respuesta && respuesta.status === 'success') 
                        ? parseInt(respuesta.data[0].cuantos) 
                        : 0;
        if (cuantos < 0) {            
            $("#verRegistrarActividades").hide(); // No tiene acceso, se oculta la opción
        }else {
            $("#verRegistrarActividades").show(); // Tiene acceso, se muestra la opción
        }
    }
</script>