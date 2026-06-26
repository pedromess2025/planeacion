<?php
// Conexión única a la base de datos (objeto mysqli; sirve tanto para llamadas OO $conn->...
// como para las funciones procedurales mysqli_*($conn, ...) que usa el resto del sistema).
$conn = new mysqli("localhost", "mess_incidencias", "Pipmytrade123", "mess_rrhh");

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

// Departamentos considerados "Ventas" (catálogo: 34=Ventas, 35=Ventas SLP, 36=Ventas Zona Nte).
// Lista única para todo el gating de Ventas (pre-registro y Calendario de Ventas) — no duplicar.
if (!defined('DEPTOS_VENTAS')) {
    define('DEPTOS_VENTAS', ['34', '35', '36']);
}

// Departamento de Logística (catálogo: 20 = Logística). Gating de la vista de Entregas/Enlaces.
if (!defined('DEPTO_LOGISTICA')) {
    define('DEPTO_LOGISTICA', '20');
}
?>
