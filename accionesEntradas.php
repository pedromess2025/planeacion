<?php
include_once 'conn.php';

    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
    $usuario = isset($_COOKIE['id_usuarioL']) ? $_COOKIE['id_usuarioL'] : ''; // Usuario que registra la entrada
    //REGISTRO DE NUEVA ENTRADA
    $cliente = isset($_POST['cliente']) ? $_POST['cliente'] : '';
    $area  = isset($_POST['area']) ? $_POST['area'] : '';   
    $marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $modelo = isset($_POST['modelo']) ? $_POST['modelo'] : '';
    $no_serie = isset($_POST['no_serie']) ? $_POST['no_serie'] : '';
    $diagnostico_inicial  = isset($_POST['diagnostico_inicial']) ? $_POST['diagnostico_inicial'] : '';
    $fecha_estimada  = isset($_POST['fecha_estimada']) ? $_POST['fecha_estimada'] : '';
    $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
    $demo = isset($_POST['demo']) ? 1 : 0; // Demo: 1 si está marcado, 0 si no
    $contacto_nombre = isset($_POST['nombre_cliente']) ? $_POST['nombre_cliente'] : '';
    $contacto = isset($_POST['contacto']) ? $_POST['contacto'] : '';
    
    // CAPTURAR IDs DE INGENIEROS
    $slcRespoonsable = (isset($_POST['slcRespoonsable']) && $_POST['slcRespoonsable'] != '0') ? intval($_POST['slcRespoonsable']) : '';
    $slcRespoonsable2 = (isset($_POST['slcRespoonsable2']) && $_POST['slcRespoonsable2'] != '0') ? intval($_POST['slcRespoonsable2']) : '';
    $slcRespoonsable3 = (isset($_POST['slcRespoonsable3']) && $_POST['slcRespoonsable3'] != '0') ? intval($_POST['slcRespoonsable3']) : '';
    
    // Concatenar IDs de ingenieros (opcional, solo si existen)
    $ingenieros = array_filter([$slcRespoonsable, $slcRespoonsable2, $slcRespoonsable3]);

    //INGENIERO ASIGNADO
    $id_registro = isset($_POST['id_registro']) ? intval($_POST['id_registro']) : 0;
    $nota = isset($_POST['notas_ingeniero']) ? trim($_POST['notas_ingeniero']) : '';
    $estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';
    $fecha_seguimiento = !empty($_POST['fecha_seguimiento']) ? $_POST['fecha_seguimiento'] : null;
    
    //ACTUALIZACION DE EQUIPO
    $equipo_id = isset($_POST['equipo_id']) ? $_POST['equipo_id'] : '';
    $ingeniero_id = isset($_POST['ingeniero_id']) ? $_POST['ingeniero_id'] : '';
    $nuevo_estatus = isset($_POST['nuevo_estatus']) ? $_POST['nuevo_estatus'] : '';
    $fecha_termino = isset($_POST['fecha_termino']) ? $_POST['fecha_termino'] : null;

    // REGISTRO EQUIPOS
    if ($accion == 'nuevaEntrada') {
        $sqlInsert = "INSERT INTO entrada_registros (cliente, area, marca, modelo, no_serie, notas_recepcion, fecha_promesa_entrega, estatus, fecha_registro, fechaTermino, demo, contacto_nombre, contacto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NULL, ?, ?, ?)";
        $stmt = $conn->prepare($sqlInsert);
        
        // "s" = string, "i" = integer
        $stmt->bind_param("ssssssssiss", 
            $cliente, $area, $marca, $modelo, $no_serie, $diagnostico_inicial, $fecha_estimada, $estatus, $demo, $contacto_nombre, $contacto
        );
        
        if ($stmt->execute()) {
            $ultimoId = $conn->insert_id; // Obtenemos ID para las fotos
            $errorFotos = false;
            $errorIngenieros = false;

            // 1. REGISTRAR INGENIEROS EN TABLA entrada_log_ingenieros
            if (!empty($ingenieros)) {
                $sqlIngeniero = "INSERT INTO entrada_log_ingenieros (id_registro, id_ing, fecha, estatus, id_asigno) VALUES (?, ?, NOW(), 'ASIGNADO', ?)";
                $stmtIng = $conn->prepare($sqlIngeniero);
                
                foreach ($ingenieros as $id_ing) {
                    $stmtIng->bind_param("iii", $ultimoId, $id_ing, $usuario);
                    if (!$stmtIng->execute()) {
                        $errorIngenieros = true;
                    }
                }
                $stmtIng->close();
                
                // Actualizar fecha_asignacion si hay ingenieros asignados
                $stmtFechaAsig = $conn->prepare("UPDATE entrada_registros SET fecha_asignacion = NOW() WHERE id_registro = ?");
                $stmtFechaAsig->bind_param("i", $ultimoId);
                $stmtFechaAsig->execute();
                $stmtFechaAsig->close();
            }

            // 2. MANEJO DE FOTOS
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
                            // Obtener fecha actual
                            $fechaActual = date('Y-m-d H:i:s');
                            $sqlFoto = "INSERT INTO entrada_fotos (id_regEntrada, ruta, fecha) VALUES (?, ?, ?)";
                            $stmtFoto = $conn->prepare($sqlFoto);  
                            $stmtFoto->bind_param("iss", $ultimoId, $rutaDestino, $fechaActual);
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
                'message' => 'Entrada registrada con éxito.' . ($errorFotos ? ' (Hubo error al subir algunas fotos)' : '') . ($errorIngenieros ? ' (Hubo error al registrar algunos ingenieros)' : '')
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
                    ent.fecha_promesa_entrega AS fecha_compromiso, 
                    ent.notas_recepcion AS diagnostico_inicial, 
                    ent.estatus,
                    (
                        SELECT GROUP_CONCAT(DISTINCT eli.id_ing SEPARATOR ',')
                        FROM entrada_log_ingenieros eli
                        WHERE eli.id_registro = ent.id_registro
                    ) AS ids_ingenieros,
                    (
                        SELECT GROUP_CONCAT(DISTINCT us.nombre SEPARATOR ', ')
                        FROM entrada_log_ingenieros eli
                        INNER JOIN usuarios us ON (us.id = eli.id_ing OR us.id_usuario = eli.id_ing)
                        WHERE eli.id_registro = ent.id_registro
                    ) AS nombres_ingenieros,
                    CONCAT('#MET-', YEAR(ent.fecha_registro), '-', LPAD(ent.id_registro, 2, '0')) AS folio
                FROM entrada_registros ent
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
        $sql = "SELECT nombre, id_usuario FROM `usuarios` ORDER BY nombre ASC";
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
        // 1. Insertar en entrada_log_ingenieros
        $sqlInsert = "INSERT INTO entrada_log_ingenieros (id_registro, id_ing, fecha, estatus, id_asigno) VALUES (?, ?, NOW(), 'ASIGNADO', ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("iii", $equipo_id, $ingeniero_id, $usuario);
        
        if ($stmtInsert->execute()) {
            // 2. Actualizar estatus del equipo si es necesario
            // Solo actualizar fecha_asignacion si aún no existe (es NULL)
            $stmtUpd = $conn->prepare("UPDATE entrada_registros SET estatus = 'Recibido', fecha_asignacion = IF(fecha_asignacion IS NULL, NOW(), fecha_asignacion) WHERE id_registro = ?");
            $stmtUpd->bind_param("i", $equipo_id);
            $stmtUpd->execute();
            $stmtUpd->close();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $stmtInsert->error]);
        }
        $stmtInsert->close();
        exit;
    }

    // CARGAR AREAS
    if ($accion == 'obtenerAreas') {
        $sql = "SELECT id, AREA FROM areas ORDER BY AREA ASC";
        $result = $conn->query($sql);
        $areas = [];
        
        while ($row = $result->fetch_assoc()) {
            $areas[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $areas]);
        exit;
    }

    // GUARDAR SEGUIMIENTO / ACTUALIZACIÓN DE TRABAJO
    if ($accion == 'guardarSeguimiento') {
        // Insertar nota si viene
        if ($nota !== '') {
            $stmtSeg = $conn->prepare("INSERT INTO entrada_seguimiento (id_registro, id_usuario_nota, nota, fecha_seguimiento, estatus) VALUES (?, ?, ?, NOW(), ?)");
            $stmtSeg->bind_param('iiss', $id_registro, $usuario, $nota, $nuevo_estatus);
            $stmtSeg->execute();
            $id_seguimiento = $conn->insert_id; // Obtener ID del seguimiento insertado
            $stmtSeg->close();
        }
        
        // MANEJO DE FOTOS DE SALIDA (SEGUIMIENTO)
        if (isset($_FILES['fotos_salida']) && isset($id_seguimiento)) {
            $fotos = $_FILES['fotos_salida'];
            $totalArchivos = count($fotos['name']);
            
            // Crear carpeta si no existe
            $directorio = 'imgEntradas/';
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }
            
            for ($i = 0; $i < $totalArchivos; $i++) {
                // Verificar que no hubo error en la subida y que tiene nombre
                if ($fotos['error'][$i] === UPLOAD_ERR_OK && !empty($fotos['name'][$i])) {
                    
                    $tmpName = $fotos['tmp_name'][$i];
                    
                    // Generar nombre único: seguimiento_ID_TIMESTAMP_index
                    $nuevoNombre = 'seguimiento_' . $id_registro . '_' . time() . '_' . $i . '.' . pathinfo($fotos['name'][$i], PATHINFO_EXTENSION);
                    $rutaDestino = $directorio . $nuevoNombre;
                    
                    if (move_uploaded_file($tmpName, $rutaDestino)) {
                        // Insertar ruta en BD
                        $sqlFoto = "UPDATE entrada_seguimiento SET ruta_foto = ? WHERE id_seguimiento = ?";
                        $stmtFoto = $conn->prepare($sqlFoto);
                        $stmtFoto->bind_param("si", $rutaDestino, $id_seguimiento);
                        $stmtFoto->execute();
                        $stmtFoto->close();
                    }
                }
            }
        }
        
        // Actualizar estatus y fecha de término
        if ($nuevo_estatus !== '') {
            $stmtUpd = $conn->prepare("UPDATE entrada_registros SET estatus = ?, fechaTermino = IFNULL(?, fechaTermino) WHERE id_registro = ?");
            $stmtUpd->bind_param('ssi', $nuevo_estatus, $fecha_termino, $id_registro);
            $stmtUpd->execute();
            $stmtUpd->close();
        }
        /*
        // Actualizar fecha_actualizacion en entrada_log_ingenieros
        $stmtIngActual = $conn->prepare("UPDATE entrada_log_ingenieros SET fecha_actualizacion = NOW() WHERE id_registro = ?");
        $stmtIngActual->bind_param('i', $id_registro);
        $stmtIngActual->execute();
        $stmtIngActual->close();*/
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    // OBTENER DETALLES DE EQUIPO
    if ($accion == 'obtenerDetalleEquipo') {
        
        $sql = "SELECT ent.*,
                (
                    SELECT GROUP_CONCAT(DISTINCT eli.id_ing SEPARATOR ',')
                    FROM entrada_log_ingenieros eli
                    WHERE eli.id_registro = ent.id_registro
                ) AS ids_ingenieros,
                (
                    SELECT GROUP_CONCAT(DISTINCT us.nombre SEPARATOR ', ')
                    FROM entrada_log_ingenieros eli
                    INNER JOIN usuarios us ON (us.id = eli.id_ing OR us.id_usuario = eli.id_ing)
                    WHERE eli.id_registro = ent.id_registro
                ) AS ingeniero_nombre
                FROM entrada_registros ent
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
            
            // Buscar fotos desde BD (entrada_fotos) - solo fotos de entrada (patrón: entrada_x_xxx_x)
            $sqlFotos = "SELECT ruta FROM entrada_fotos WHERE id_regEntrada = ? AND ruta LIKE 'imgEntradas/entrada_%' ORDER BY id ASC";
            $stmtFotos = $conn->prepare($sqlFotos);
            $stmtFotos->bind_param('i', $equipo['id_registro']);
            $stmtFotos->execute();
            $resultFotos = $stmtFotos->get_result();
            $fotos = [];
            while ($fotoRow = $resultFotos->fetch_assoc()) {
                if (!empty($fotoRow['ruta'])) {
                    $fotos[] = $fotoRow['ruta'];
                }
            }
            $stmtFotos->close();
            $equipo['fotos'] = $fotos;
            
            // Buscar seguimientos/comentarios
            $sqlSeguimientos = "SELECT es.id_seguimiento, es.id_registro, es.id_usuario_nota, es.nota, 
                                       es.fecha_seguimiento, es.ruta_foto, es.estatus,
                                       us.nombre AS nombre_usuario
                                FROM entrada_seguimiento es
                                INNER JOIN usuarios us ON us.id_usuario = es.id_usuario_nota
                                WHERE es.id_registro = ?
                                ORDER BY es.fecha_seguimiento DESC";
            $stmtSeg = $conn->prepare($sqlSeguimientos);
            $stmtSeg->bind_param('i', $id_registro);
            $stmtSeg->execute();
            $resultSeg = $stmtSeg->get_result();
            
            $seguimientos = [];
            while ($row = $resultSeg->fetch_assoc()) {
                // Formatear fecha
                $fecha = date('d/m/Y H:i', strtotime($row['fecha_seguimiento']));
                
                // Obtener iniciales del nombre
                $palabras = explode(' ', $row['nombre_usuario']);
                $iniciales = '';
                foreach ($palabras as $palabra) {
                    if (!empty($palabra)) {
                        $iniciales .= strtoupper(substr($palabra, 0, 1));
                    }
                }

                // Construir la estructura: "fecha-iniciales-estatus-nota" (fecha e iniciales en negrita, azul si tiene fotos)
                $tieneImagenes = !empty($row['ruta_foto']);
                $estilo = $tieneImagenes ? 'style="color: #0d6efd; cursor: pointer;"' : '';
                $onclick = $tieneImagenes ? 'onclick="mostrarFotosSeguimiento(INDEX_PLACEHOLDER)"' : '';
                $icono_img = $tieneImagenes ? ' <i class="fas fa-image"></i>' : '';
                $estatus_label = !empty($row['estatus']) ? ' - ' . $row['estatus'] : '';
                
                $seguimientos[] = [
                    'html' => $icono_img . '<strong ' . $estilo . ' ' . $onclick . '>' . $fecha . ' - ' . $iniciales . $estatus_label . '</strong> - ' . $row['nota'],
                    'tieneImagenes' => $tieneImagenes,
                    'fotos' => $tieneImagenes ? [$row['ruta_foto']] : [] // Convertir a array
                ];
            }
            $stmtSeg->close();
            
            // Unir todas las notas con saltos de línea dobles, o mensaje por defecto si no hay
            $equipo['notas_seguimiento'] = !empty($seguimientos) ? $seguimientos : null;
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $equipo]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Equipo no encontrado']);
        }
        exit;
    }
?>