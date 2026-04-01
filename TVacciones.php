<?php
// Conexi贸n a la base de datos
include 'conn.php';
mysqli_set_charset($conn, "utf8");

$opcion = $_GET["opcion"];

if ($opcion == "semanaActual") {
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, u.nombre, IFNULL(u2.nombre,'') AS nombre2, IFNULL(u3.nombre,'') AS nombre3, 
                    comment_logistic, estatus_logistic,
                    (SELECT departamento FROM usuarios WHERE noEmpleado = ot.capturado_por) as depto, reprogramado, motivo_reprogramacion, motivo_cancelacion, fecha_captura
            FROM servicios_planeados_mess ot
            inner join usuarios u on ot.engineer = u.id_usuario 
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario             
            WHERE ot.start_date >= CURDATE() AND ot.start_date < DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY ot.start_date ASC";

    $result = mysqli_query($conn, $sql);
    $datos = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $datos[] = $row;
    }

    echo json_encode($datos);
}

if ($opcion == "semanaProx") {
    $sql = "SELECT ot.*, DATE(ot.start_date) as FechaPlaneadaInicioDate, u.nombre, IFNULL(u2.nombre,'') AS nombre2, IFNULL(u3.nombre,'') AS nombre3, 
                    comment_logistic, estatus_logistic,
                    (SELECT departamento FROM usuarios WHERE noEmpleado = ot.capturado_por) as depto, reprogramado, motivo_reprogramacion, motivo_cancelacion, fecha_captura
            FROM servicios_planeados_mess ot
            inner join usuarios u on ot.engineer = u.id_usuario 
            LEFT join usuarios u2 on ot.engineer2 = u2.id_usuario
            LEFT join usuarios u3 on ot.engineer3 = u3.id_usuario             
            WHERE ot.start_date >= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND ot.start_date < DATE_ADD(CURDATE(), INTERVAL 14 DAY)
            ORDER BY ot.start_date ASC";

    $result = mysqli_query($conn, $sql);
    $datos = array();

    while ($row = mysqli_fetch_assoc($result)) {
        $datos[] = $row;
    }

    echo json_encode($datos);
}
mysqli_close($conn);