<?php
include_once 'conn.php';

    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    //REGISTRO DE NUEVA ENTRADA
    $cliente = isset($_POST['cliente']) ? $_POST['cliente'] : '';
    $area  = isset($_POST['area']) ? $_POST['area'] : '';   
    $marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $modelo = isset($_POST['modelo']) ? $_POST['modelo'] : '';
    $no_serie = isset($_POST['no_serie']) ? $_POST['no_serie'] : '';
    $diagnostico_inicial  = isset($_POST['diagnostico_inicial']) ? $_POST['diagnostico_inicial'] : '';
    $fecha_estimada  = isset($_POST['fecha_estimada']) ? $_POST['fecha_estimada'] : '';
    $fotos = isset($_POST['fotos']) ? $_POST['fotos'] : '';
    $usuario = isset($_COOKIE['id_usuarioL']) ? $_COOKIE['id_usuarioL'] : ''; // Usuario que registra la entrada
    $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';

    //INGENIERO ASIGNADO
    $id_registro = isset($_POST['id_registro']) ? intval($_POST['id_registro']) : 0;
    $nota = isset($_POST['notas_ingeniero']) ? trim($_POST['notas_ingeniero']) : '';
    $estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
    $fecha_seguimiento = !empty($_POST['fecha_seguimiento']) ? $_POST['fecha_seguimiento'] : null;
    
    //ACTUALIZACION DE EQUIPO
    $equipo_id = $_POST['equipo_id'];
    $ingeniero_id = $_POST['ingeniero_id'];
    $nuevo_estatus = isset($_POST['nuevo_estatus']) ? $_POST['nuevo_estatus'] : '';
    $fecha_termino = isset($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;

    // REGISTRO EQUIPOS
    if ($accion == 'nuevaEntrada') {
        $sqlInsert = "INSERT INTO entrada_registros (cliente, area, marca, modelo, no_serie, notas_recepcion, fecha_promesa_entrega, fotos_ruta, estatus, fecha_registro, fechaTermino)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sqlInsert);
        
        // "s" = string, "i" = integer, "d" = double (decimales)
        $stmt->bind_param("ssssssssss", 
                        $cliente, $area, $marca, $modelo, $no_serie, $diagnostico_inicial, $fecha_estimada, $fotos, $estatus, $fecha_estimada
        );
        
        if ($stmt->execute()) {
            $ultimoId = $conn->insert_id; // Obtenemos ID para las fotos
            $errorFotos = false;

            // 1. MANEJO DE FOTOS 
            if (isset($_FILES['fotos'])) {
                $fotos = $_FILES['fotos'];
                // Contamos cuántos archivos vienen
                $totalArchivos = count($fotos['name']);

                // Creamos carpeta si no existe
                $directorio = 'imgEntradas/';
                if (!file_exists($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                for ($i = 0; $i < $totalArchivos; $i++) {
                    // Verificar que no hubo error en la subida y que tiene nombre
                    if ($fotos['error'][$i] === UPLOAD_ERR_OK && !empty($fotos['name'][$i])) {
                        
                        $nombreOriginal = $fotos['name'][$i];
                        $tmpName        = $fotos['tmp_name'][$i];
                        
                        // Generar nombre único: entrada_15_TIMESTAMP_nombre.jpg
                        $nuevoNombre = 'entrada_' . $ultimoId . '_' . time() . '_' . $i;
                        $rutaDestino = $directorio . $nuevoNombre;

                        if (move_uploaded_file($tmpName, $rutaDestino)) {
                            // Insertar ruta en BD
                            // Aquí podemos usar query normal o prepare (prepare es mejor)
                            $sqlFoto = "INSERT INTO entrada_fotos (id_regEntrada, ruta) VALUES (?, ?)";
                            $stmtFoto = $conn->prepare($sqlFoto);  
                            $stmtFoto->bind_param("is", $ultimoId, $rutaDestino);
                            $stmtFoto->execute();
                            $stmtFoto->close();
                        } else {
                            $errorFotos = true;
                        }
                    }
                }
            }
            $response = array(
                'status' => 'success', 
                'message' => 'Entrada registrada con éxito.' . ($errorFotos ? ' (Hubo error al subir algunas fotos)' : '')
            );
        } else {
            $response = array(
                'status' => 'error', 
                'message' => 'Error al guardar en BD: ' . $stmt->error
            );
        }
        $stmt->close();
        // Devolver JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit; // Terminar script
    }

    // CARGAS DE REGISTROS DE ENTRADAS
    if ($accion == 'obtenerEquipos') {
        $sql = "SELECT ent.id_registro, ent.cliente, ent.area, ent.marca, ent.modelo, ent.no_serie, 
                    ent.fecha_promesa_entrega as fecha_compromiso, 
                    ent.notas_recepcion as diagnostico_inicial, 
                    ent.estatus, ent.id_usuario_asignado, us.nombre,
                    CONCAT('#MET-', YEAR(ent.fecha_registro), '-', LPAD(ent.id_registro, 2, '0')) as folio
                FROM entrada_registros ent
                LEFT JOIN usuarios us ON us.id = ent.id_usuario_asignado   
                WHERE ent.estatus != 'Terminado'
                ORDER BY ent.fecha_promesa_entrega ASC";
        
        $result = $conn->query($sql);
        $equipos = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['id'] = $row['id_registro']; // Compatibilidad con frontend
            $equipos[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $equipos]);
        exit;
    }

    // CARGAR INGENIEROS
    if ($accion == 'obtenerIngenieros') {
        $sql = "SELECT id, nombre, id_usuario FROM `usuarios` ORDER BY nombre ASC";
        $result = $conn->query($sql);
        $ingenieros = [];
        
        while ($row = $result->fetch_assoc()) {
            $ingenieros[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $ingenieros]);
        exit;
    }

    // ASIGNAR EQUIPO A INGENIERO
    if ($accion == 'asignarIngeniero') {
        $sqlUpdate = "UPDATE entrada_registros 
                    SET id_usuario_asignado = $ingeniero_id, estatus = 'Recibido', fecha_asignacion = NOW()
                    WHERE id_registro = $equipo_id";
        
        if ($conn->query($sqlUpdate)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        exit;
    }

    // GUARDAR SEGUIMIENTO / ACTUALIZACIÓN DE TRABAJO
    if ($accion == 'guardarSeguimiento') {
        // Insertar nota si viene
        if ($nota !== '') {
            $stmtSeg = $conn->prepare("INSERT INTO entrada_seguimiento (id_registro, id_usuario_nota, nota, fecha_seguimiento) VALUES (?, ?, ?, NOW())");
            $stmtSeg->bind_param('iis', $id_registro, $usuario, $nota);
            $stmtSeg->execute();
            $stmtSeg->close();
        }
        // Actualizar estatus y fecha de término
        if ($nuevo_estatus !== '') {
            $stmtUpd = $conn->prepare("UPDATE entrada_registros SET estatus = ?, fechaTermino = IFNULL(?, fechaTermino) WHERE id_registro = ?");
            $stmtUpd->bind_param('ssi', $nuevo_estatus, $fecha_termino, $id_registro);
            $stmtUpd->execute();
            $stmtUpd->close();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // OBTENER DETALLES DE EQUIPO
    if ($accion == 'obtenerDetalleEquipo') {
        
        $sql = "SELECT ent.*, us.nombre as ingeniero_nombre
                FROM entrada_registros ent
                LEFT JOIN usuarios us ON us.id = ent.id_usuario_asignado
                WHERE ent.id_registro = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_registro);
        $stmt->execute();
        $result = $stmt->get_result();
        $equipo = $result->fetch_assoc();
        $stmt->close();
        
        if ($equipo) {
            // Generar folio
            $folio = '#MET-' . date('Y', strtotime($equipo['fecha_registro'])) . '-' . str_pad($equipo['id_registro'], 2, '0', STR_PAD_LEFT);
            $equipo['folio'] = $folio;
            
            // Buscar fotos
            $fotosDirPattern = 'imgEntradas/entrada_' . $equipo['id_registro'] . '_*';
            $fotos = glob($fotosDirPattern);
            $equipo['fotos'] = $fotos ?: [];
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $equipo]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Equipo no encontrado']);
        }
        exit;
    }
?>