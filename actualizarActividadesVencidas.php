<?php
include 'conn.php';
mysqli_set_charset($conn, "utf8");

$sqlUpdate = "UPDATE servicios_planeados_mess s
                        SET estatus = '$estatus'
                        WHERE  
                        estatus IN ('Fechareservadasininformación', 'Pendientedeinformacion') AND
                        start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 DAY) GROUP BY capturado_por";
        //echo $sqlUpdate;
        
        if ($conn->query($sqlUpdate) === TRUE) {
            $response = array('status' => 'success', 'message' => 'Actividad actualizada con éxito.');
        } else {
            $response = array('status' => 'error', 'message' => 'Error al actualizar la actividad: ' . $conn->error);
        }

?>