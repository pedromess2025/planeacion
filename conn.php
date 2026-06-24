<?php
// Conexión única a la base de datos (objeto mysqli; sirve tanto para llamadas OO $conn->...
// como para las funciones procedurales mysqli_*($conn, ...) que usa el resto del sistema).
$conn = new mysqli("localhost", "mess_incidencias", "Pipmytrade123", "mess_rrhh");

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
?>
