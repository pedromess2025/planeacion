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
    .btn-orange{
        --bs-bg-opacity: 1;
        background-color: #ff7300ff !important;
        color: #ffffffff !important;
    }
    .btn-outline-orange{
        --bs-bg-opacity: 1;
        color: #ff7300ff !important;
        border-color: #ff7300ff !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="/loginMaster/inicio">
    <div class="sidebar-brand-icon rotate-n-1">
        <img class="sidebar-card-illustration mb-2" href="" src="img/MESS_07_CuboMess_2.png" width="40" alt="Logo">
    </div>
</a>
<!-- Heading -->
<div class="sidebar-heading">
    <span class="badge text-xl-white">Opciones</span>
</div>

<hr class="sidebar-divider my-0 alert-light">
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseEntradas" aria-expanded="false" aria-controls="collapseEntradas">
        <i class="fas fa-fw fa-inbox text-gray-400"></i>
        <span>Entradas</span>
        <i class="fas fa-angle-down float-end text-gray-400"></i>
    </a>
    <div id="collapseEntradas" class="collapse" aria-labelledby="headingEntradas" data-bs-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item disabled" href="entradaControlEquipos" id="verOpcionRegistroMenu" name="verOpcionRegistroMenu" >
                <i class="fas fa-fw fa-plus text-primary"></i> Registro
            </a>
            <a class="collapse-item" href="entradaDetalleEntradas">
                <i class="fas fa-fw fa-clipboard-list text-primary"></i> Ver Entradas
            </a>
        </div>
    </div>
</li>

<hr class="sidebar-divider my-0 alert-light">
<li class = "nav-item">
    <a class = "nav-link" href = "#" data-bs-toggle = "modal" data-bs-target = "#logoutModalN">
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
    });

    // Funcion para validar si puede ver la opción de registro en el menú de entradas (solo encargados pueden verla)
    async function verificarVistaMenu() {
        // 1.Mandamos llamar nuestra función principal. Agregamos await para esperar la respuesta
        const respuesta = await validaOpciones('entradasEq', 'verOpcionRegistroMenu');
        
        // 2. Evaluamos la respuesta y aplicamos las acciones a realizar según el caso
        const cuantos = (respuesta && respuesta.status === 'success') 
                        ? parseInt(respuesta.data[0].cuantos) 
                        : 0;
        if (cuantos <= 0) {            
            $("#verOpcionRegistroMenu").hide(); // No tiene acceso, se oculta la opción
        }else {
            $("#verOpcionRegistroMenu").show(); // Tiene acceso, se muestra la opción
        }
    }

</script>