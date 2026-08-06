<?php
// Conexión a la base de datos mess_rrhh (usuario MySQL con grants cross-DB a mess_sivac/mess_control_vehicular).
$conn = new mysqli("localhost", "mess_incidencias", "Pipmytrade123", "mess_rrhh");
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

/* ---------------------------- ACCESOS ESPECIALES ----------------------------
 * Permisos por opción, tabla `accesos_especiales` (mess_rrhh), la misma que usan Activos,
 * Entradas Eq., Control Vehicular, etc. Se administran desde loginMaster (modalAccesosEspeciales).
 * El front consulta lo mismo con validaOpciones(sistema, opcion) de funcionesGlobales.js, pero eso
 * solo esconde botones: el bloqueo de verdad es este, del lado del servidor.
 *
 * Estas funciones viven aquí porque conn.php es el único include que comparten las vistas y los
 * endpoints. Las vistas incluyen conn.php y además menu.php (que vuelve a incluirlo), así que este
 * archivo corre dos veces por página: sin el function_exists, la 2ª pasada truena por redeclarar.
 */
if (!function_exists('tieneAccesoEspecial')) {

    /** ¿El empleado tiene activo el acceso `sistema`/`opcion`? */
    function tieneAccesoEspecial($conn, $noEmpleado, $sistema, $opcion) {
        $noEmpleado = intval($noEmpleado);
        if ($noEmpleado <= 0) return false;
        $stmt = $conn->prepare("SELECT COUNT(*) AS cuantos FROM accesos_especiales
                                WHERE noEmpleado = ? AND sistema = ? AND opcion = ? AND estatus = 1");
        if (!$stmt) return false;
        $stmt->bind_param("iss", $noEmpleado, $sistema, $opcion);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row && intval($row['cuantos']) > 0;
    }

    /** noEmpleado de la sesión, o '' si no hay cookie de login. */
    function noEmpleadoSesion() {
        $noEmp = isset($_COOKIE['noEmpleado']) ? trim((string)$_COOKIE['noEmpleado']) : '';
        return ctype_digit($noEmp) ? $noEmp : '';
    }

    /**
     * Guard para VISTAS. Va ANTES de imprimir nada y corta con `exit`: sin el exit la página se
     * sigue enviando completa y el HTML llega al navegador aunque después redirija.
     */
    function exigeAccesoEspecial($conn, $sistema, $opcion) {
        $noEmp = noEmpleadoSesion();
        if ($noEmp === '') {
            echo '<script>window.location.assign("index")</script>';
            exit;
        }
        if (!tieneAccesoEspecial($conn, $noEmp, $sistema, $opcion)) {
            echo '<script>alert("No tienes acceso a esta sección."); window.location.assign("seguimiento_actividades");</script>';
            exit;
        }
    }

    /** Guard para ENDPOINTS que responden JSON: 401 si no hay sesión, 403 si no tiene la opción. */
    function exigeAccesoEspecialJson($conn, $sistema, $opcion) {
        $noEmp = noEmpleadoSesion();
        if ($noEmp === '') {
            http_response_code(401);
            echo json_encode(array('status' => 'error', 'message' => 'Sesión no válida. Vuelve a iniciar sesión.'));
            exit;
        }
        if (!tieneAccesoEspecial($conn, $noEmp, $sistema, $opcion)) {
            http_response_code(403);
            echo json_encode(array('status' => 'error', 'message' => 'No tienes permiso para usar este módulo.'));
            exit;
        }
    }
}
