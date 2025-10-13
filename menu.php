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
<hr class="sidebar-divider my-0">

<!-- Divider -->
<hr class="sidebar-divider">
<!-- Heading -->
<div class="sidebar-heading">
    <span class="badge text-xl-white">Opciones</span>
</div>
<!-- Nav Item - Pages Collapse Menu -->
<?php
//81 203 8  usuarios norte
//USUARIOS QUE PUEDEN REGISTRAR ACTIVIDADES
$usuariosRegistran = array(212, 14, 42, 161, 403, 183, 521, 276, 26, 147, 189, 177, 45, 26, 525, 435, 489, 523, 298, 81, 203, 8, 278, 206);

if (in_array($_COOKIE['noEmpleado'], $usuariosRegistran)) {
?>
    <li class="nav-item">
    <a class="nav-link" href="index">
        <i class="fas fa-fw fa-check"></i>
        <span>Registrar actividad</span>
    </a>
</li>
<?php
}
?>


<li class="nav-item">
    <a class="nav-link" href="seguimiento_actividades">
        <i class="fas fa-fw fa-home"></i>
        <span>Seguimiento actividades</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="verActividadesPlaneadas">
        <i class="fas fa-fw fa-calendar"></i>
        <span>Actividades planeadas</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="verActividades">
        <i class="fas fa-fw fa-calendar"></i>
        <span>Actividades planeadas SCOT</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link" href="Manual Planeacion.pdf" target="_blank">
        <i class="fas fa-fw fa-book"></i>
        <span>Manual de usuario</span>
    </a>
</li>

<hr class="sidebar-divider d-none d-md-block">

<div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button> 
</div>
</ul>