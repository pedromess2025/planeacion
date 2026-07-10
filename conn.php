<?php
// Conexión a la base de datos mess_rrhh (usuario MySQL con grants cross-DB a mess_sivac/mess_control_vehicular).
$conn = new mysqli("localhost", "mess_incidencias", "Pipmytrade123", "mess_rrhh");
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
