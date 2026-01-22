<?php
include 'conn.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// Validación de usuario (otro caso)
$id_usuario = isset($_POST['id_usuarioPla']) ? $_POST['id_usuarioPla'] : '';
$nombredelusuario = isset($_POST['nombredelusuarioPla']) ? $_POST['nombredelusuarioPla'] : '';
$noEmpleado = isset($_POST['noEmpleadoPla']) ? $_POST['noEmpleadoPla'] : '';
$rol = isset($_POST['rolPla']) ? $_POST['rolPla'] : '';
$usuario = isset($_POST['correoPla']) ? $_POST['correoPla'] : '';

if (empty($id_usuario) || empty($noEmpleado)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
} else {
    $Qempresas  =  "SELECT  *, TIMESTAMPDIFF(YEAR,fechaIngreso,CURDATE()) AS antiguedad, rol FROM usuarios WHERE usuario  = '".$usuario."' AND estatus = 1";
    $res2 =  mysqli_query( $conn, $Qempresas ) or die (mysqli_error($conn));
    $nr = mysqli_num_rows($res2);

    while ($row2 = mysqli_fetch_array($res2)){
        $nombreEmpleado = $row2["nombre"];
        $noEmpleado = $row2["noEmpleado"];
        $antiguedad = $row2["antiguedad"];
        $diasD = $row2["diasdisponibles"];
        $rol = $row2["rol"];
        $departamento = $row2["departamento"];
    }

    if($nr == 1){
        // Establecer cookies con SameSite=Lax
        echo '<script>document.cookie = "id_usuario='.$id_usuario.';expires=" + new Date(Date.now() + 86400000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>document.cookie = "antiguedad='.$antiguedad.';expires=" + new Date(Date.now() + 86400000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>document.cookie = "nombredelusuario='.$nombreEmpleado.';expires=" + new Date(Date.now() + 86400000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>document.cookie = "noEmpleado='.$noEmpleado.';expires=" + new Date(Date.now() + 86400000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>document.cookie = "diasD='.$diasD.';expires=" + new Date(Date.now() + 86400000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>document.cookie = "departamento='.$departamento.';expires=" + new Date(Date.now() + 86400000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>document.cookie = "rol='.$rol.';expires=" + new Date(Date.now() + 86400000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>document.cookie = "SesionLogin=LoginMaster; expires=" + new Date(Date.now() + 99999000).toUTCString() + ";SameSite=Lax;";</script>';
        echo '<script>window.location.assign("seguimiento_actividades.php")</script>';                

        session_start();
        $_SESSION['nombredelusuario'] = $nombreEmpleado;
        $_SESSION['noEmpleado'] = $noEmpleado;
        $_SESSION['rol'] = $rol;
        $_SESSION['correo'] = $usuario;
        $_SESSION['id_usuario'] = $id_usuario;

        echo json_encode(['success' => true]);        
        exit;
    }
    // Si no hay usuario válido
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no válido.',
    ]);
    exit;
}
?>