<?php
include 'conn.php';
mysqli_set_charset($conn, "utf8");

// 1️ Cancelar servicios con más de 2 días desde start_date
$sqlUpdate1 = "UPDATE servicios_planeados_mess
                SET estatus = 'Cancelada'
                WHERE estatus IN ('Fechareservadasininformación', 'Pendientedeinformacion')
                AND start_date <= DATE_SUB(NOW(), INTERVAL 2 DAY)";

$result1 = $conn->query($sqlUpdate1);

// 2️ Cerrar servicios confirmados que pasaron hace 2 días
$sqlUpdate2 = "UPDATE servicios_planeados_mess
                SET estatus = 'Cerrada'
                WHERE estatus LIKE '%Servicioconfirmadoparasuejecucion%'
                AND start_date <= DATE_SUB(NOW(), INTERVAL 2 DAY)";

$result2 = $conn->query($sqlUpdate2);

// 3 Generar respuesta combinada
if ($result1 && $result2) {
    $response = array('status' => 'success', 'message' => 'Actividades actualizadas con éxito.');
} else {
    $response = array(
        'status' => 'error',
        'message' => 'Error en la actualización: ' . $conn->error
    );
}

echo json_encode($response);
?>
