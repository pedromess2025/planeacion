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
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
<!-- Sidebar - Brand -->
<a class="sidebar-brand d-flex align-items-center justify-content-center" href="inicio">
    <div class="sidebar-brand-icon rotate-n-1">
        <img class="sidebar-card-illustration mb-2" href="" src="img/MESS_07_CuboMess_2.png" width="40" alt="Logo">
    </div>
</a>
<!-- Heading -->
<div class="sidebar-heading">
    <span class="badge text-xl-white">Opciones</span>
</div>
<!-- Divider -->
<?php
//USUARIOS ENCARGADOS QUE PUEDEN REGISTRAR ENTRADAS Y ASIGNAR/MODIFICAR INGENIEROS
// 523-SEBAS, 45-SERGIO, 177-ZAYI, 276-PEDRO, 183-AMRAM, 555-LIZ
$usuariosEncargados = array(523, 45, 177, 276, 183, 555);

if (in_array($_COOKIE['noEmpleado'], $usuariosEncargados)) {
?>
<hr class="sidebar-divider my-0 alert-light">
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseEntradas" aria-expanded="false" aria-controls="collapseEntradas">
        <i class="fas fa-fw fa-inbox text-gray-400"></i>
        <span>Entradas</span>
        <i class="fas fa-angle-down float-end text-gray-400"></i>
    </a>
    <div id="collapseEntradas" class="collapse" aria-labelledby="headingEntradas" data-bs-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="entradaControlEquipos">
                <i class="fas fa-fw fa-plus text-primary"></i> Registro
            </a>
            <a class="collapse-item" href="entradaDetalleEntradas">
                <i class="fas fa-fw fa-clipboard-list text-primary"></i> Ver Entradas
            </a>
        </div>
    </div>
</li>
<?php
} else {
    // Ingenieros regulares: solo pueden ver entradas
?>
<hr class="sidebar-divider my-0 alert-light">
<li class="nav-item">
    <a class="nav-link" href="entradaDetalleEntradas">
        <i class="fas fa-fw fa-inbox text-gray-400"></i>
        <span>Ver Entradas</span>
    </a>
</li>
<?php
}
?>
<hr class="sidebar-divider my-0 alert-light">
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