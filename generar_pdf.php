<?php
header('Access-Control-Allow-Origin: *');

require('conn.php');
require(__DIR__ . '/fpdf/fpdf.php');

// Obtener el ID del registro
$id_registro = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_registro == 0) {
    die('ID de registro no valido');
}

// Consultar la información del equipo
$query = "SELECT 
    ent.*,
    (
        SELECT GROUP_CONCAT(DISTINCT us.nombre SEPARATOR ', ')
        FROM entrada_log_ingenieros eli
        INNER JOIN usuarios us ON us.id_usuario = eli.id_ing
        WHERE eli.id_registro = ent.id_registro
        AND eli.estatus = 'ASIGNADO'
    ) AS nombres_ingenieros
FROM entrada_registros ent
WHERE ent.id_registro = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_registro);
$stmt->execute();
$result = $stmt->get_result();
$equipo = $result->fetch_assoc();

if (!$equipo) {
    die('No se encontro el registro');
}

// Obtener notas de seguimiento
$query_seguimiento = "SELECT 
    es.nota as notas_ingeniero,
    es.fecha_actualizacion,
    es.estatus,
    us.nombre as ingeniero
FROM entrada_seguimiento es
LEFT JOIN usuarios us ON es.id_usuario_nota = us.id_usuario
WHERE es.id_registro = ?
ORDER BY es.fecha_actualizacion DESC";

$stmt_seg = $conn->prepare($query_seguimiento);
$stmt_seg->bind_param("i", $id_registro);
$stmt_seg->execute();
$result_seg = $stmt_seg->get_result();

$notas_seguimiento = '';
while ($seg = $result_seg->fetch_assoc()) {
    $nota = $seg['notas_ingeniero'] ?? '';
    $nota = preg_replace('/^.*(FPDF error:|Fatal error:|Uncaught Exception:|Call Stack).*$/mi', '', $nota);
    $nota = trim($nota);
    if ($nota === '') {
        continue;
    }

    $notas_seguimiento .= "[" . date('d/m/Y', strtotime($seg['fecha_actualizacion'])) . "] ";
    $notas_seguimiento .= "(" . $seg['estatus'] . ") - ";
    $notas_seguimiento .= $nota . "\n\n";
}

if (empty($notas_seguimiento)) {
    $notas_seguimiento = 'Sin notas de seguimiento registradas.';
}

// Obtener total de horas trabajadas
$query_horas_total = "SELECT COALESCE(SUM(hrs_trabajadas), 0) AS total_horas
                      FROM entrada_seguimiento
                      WHERE id_registro = ?";
$stmt_horas_total = $conn->prepare($query_horas_total);
$stmt_horas_total->bind_param("i", $id_registro);
$stmt_horas_total->execute();
$result_horas_total = $stmt_horas_total->get_result();
$row_horas_total = $result_horas_total->fetch_assoc();
$total_horas = floatval($row_horas_total['total_horas'] ?? 0);
$stmt_horas_total->close();

// Obtener horas por ingeniero
$query_horas_ing = "SELECT es.id_usuario_nota,
                           COALESCE(MAX(u.nombre), CONCAT('Ing #', es.id_usuario_nota)) AS ingeniero,
                           COALESCE(SUM(es.hrs_trabajadas), 0) AS horas
                    FROM entrada_seguimiento es
                    LEFT JOIN usuarios u ON (u.id_usuario = es.id_usuario_nota OR u.id = es.id_usuario_nota)
                    WHERE es.id_registro = ?
                    GROUP BY es.id_usuario_nota
                    ORDER BY ingeniero ASC";
$stmt_horas_ing = $conn->prepare($query_horas_ing);
$stmt_horas_ing->bind_param("i", $id_registro);
$stmt_horas_ing->execute();
$result_horas_ing = $stmt_horas_ing->get_result();
$horas_por_ingeniero = [];
while ($row_horas_ing = $result_horas_ing->fetch_assoc()) {
    $horas_por_ingeniero[] = $row_horas_ing;
}
$stmt_horas_ing->close();

// Obtener refacciones utilizadas
$query_refacciones = "SELECT refaccion, precio_refaccion
                      FROM entrada_seguimiento
                      WHERE id_registro = ?
                      AND refaccion IS NOT NULL
                      AND TRIM(refaccion) <> ''
                      ORDER BY fecha_actualizacion ASC";
$stmt_ref = $conn->prepare($query_refacciones);
$stmt_ref->bind_param("i", $id_registro);
$stmt_ref->execute();
$result_ref = $stmt_ref->get_result();
$refacciones_pdf = [];
while ($row_ref = $result_ref->fetch_assoc()) {
    $refacciones_pdf[] = [
        'refaccion'        => trim((string)$row_ref['refaccion']),
        'precio_refaccion' => intval($row_ref['precio_refaccion'])
    ];
}
$stmt_ref->close();

// Preparar datos para el PDF
$folio = '#ENT-' . ($equipo['area'] ?? '00') . '-' . 
         date('Y', strtotime($equipo['fecha_registro'] ?? date('Y-m-d'))) . '-' . 
         str_pad($equipo['id_registro'] ?? '0', 2, '0', STR_PAD_LEFT);
$ingenieros = $equipo['nombres_ingenieros'] ?? 'No asignado';
$equipo_info = 'Equipo: ' . ($equipo['marca'] ?? 'N/A') . ' - ' . 
               ($equipo['modelo'] ?? 'N/A') . ' - ' . 
               ($equipo['no_serie'] ?? 'S/N');
$cliente_info = 'Cliente: ' . ($equipo['cliente'] ?? 'N/A');
$contacto_info = 'Contacto: ' . ($equipo['contacto'] ?? 'N/A');
$notas_recepcion = $equipo['notas_recepcion'] ?? 'Sin diagnostico inicial';
$estatus = $equipo['estatus'] ?? 'RECIBIDO';
$fecha = $equipo['fecha_registro'] ?? date('Y-m-d');

// Crear instancia de PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->AddPage();

// ========================================
// ENCABEZADO DEL DOCUMENTO
// ========================================

// Logo o título principal (color:#4000A0)
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, utf8_decode('FOLIO DE ENTRADA:'), 0, 1, 'L');

$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 8, $folio, 0, 1, 'L');

// Línea separadora
$pdf->SetDrawColor(13, 110, 253); // Color primario Bootstrap
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// ========================================
// FOLIO DE ENTRADA
// ========================================


// ========================================
// INGENIERO(S) ASIGNADO(S)
// ========================================

$pdf->SetTextColor(0, 0, 0);
$leftX = 15;
$rightX = 105;
$colW = 90;
$yInicioBloque = $pdf->GetY();

// Columna izquierda: ingenieros
$pdf->SetXY($leftX, $yInicioBloque);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($colW, 5, utf8_decode('INGENIERO(S):'), 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$lista_ingenieros = explode(',', $ingenieros);
foreach($lista_ingenieros as $ingeniero) {
    $nombreIng = trim($ingeniero);
    if ($nombreIng === '') {
        continue;
    }
    $pdf->SetX($leftX + 5);
    $pdf->Cell($colW - 5, 5, utf8_decode('- ' . $nombreIng), 0, 1, 'L');
}
$yFinalIzq = $pdf->GetY();

// Columna derecha: total de horas trabajadas
$pdf->SetXY($rightX, $yInicioBloque);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($colW, 5, utf8_decode('TOTAL HORAS TRABAJADAS:'), 0, 1, 'L');

$pdf->SetFont('Arial', '', 10);
$pdf->SetX($rightX + 5);
$pdf->Cell($colW - 5, 5, utf8_decode('- ' . number_format($total_horas, 2) . ' hrs'), 0, 1, 'L');
$yFinalDer = $pdf->GetY();

$pdf->SetY(max($yFinalIzq, $yFinalDer) + 3);

// Línea separadora
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.3);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// ========================================
// ESTATUS Y FECHA
// ========================================

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, utf8_decode('INFORMACIÓN DEL SERVICIO'), 0, 1, 'L');
$pdf->Ln(2);

// Coordenadas de estatus y fecha (sin tabla)
$left = 15;
$colWidth = 90;
$rowHeight = 7;
$yStatus = $pdf->GetY();

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetXY($left, $yStatus);
$pdf->Cell($colWidth, $rowHeight, utf8_decode('ESTATUS ACTUAL'), 0, 0, 'C');
$pdf->SetXY($left + $colWidth, $yStatus);
$pdf->Cell($colWidth, $rowHeight, utf8_decode('FECHA DE ENTRADA'), 0, 0, 'C');
$pdf->SetFont('Arial', '', 10);
$estatus_text = $estatus;
switch($estatus) {
    case 'RECIBIDO': $estatus_text = 'Entrada'; break;
    case 'DIAGNOSTICO': $estatus_text = 'En Diagnostico'; break;
    case 'REPARACION': $estatus_text = 'En Reparacion'; break;
    case 'REFACCIONES': $estatus_text = 'Espera de Refacciones'; break;
    case 'CALIBRACION': $estatus_text = 'En Calibracion'; break;
    case 'TERMINADO': $estatus_text = 'Terminado'; break;
    case 'SINENVIAR': $estatus_text = 'Terminado Sin Enviar'; break;
}

$pdf->SetXY($left, $yStatus + $rowHeight);
$pdf->Cell($colWidth, $rowHeight, utf8_decode($estatus_text), 0, 0, 'C');
$pdf->SetXY($left + $colWidth, $yStatus + $rowHeight);
$pdf->Cell($colWidth, $rowHeight, date('d/m/Y', strtotime($fecha)), 0, 0, 'C');

$pdf->SetY($yStatus + ($rowHeight * 2) + 3);

// Línea separadora
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// ========================================
// INFORMACIÓN DEL EQUIPO Y CLIENTE
// ========================================

$leftX  = 15;
$rightX = 120;
$colWL  = 100; // ancho columna izquierda
$colWR  = 75;  // ancho columna derecha
$lineH  = 5;
$yEncabezado = $pdf->GetY(); // Y del encabezado de sección

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell($colWL, 6, utf8_decode('DATOS DEL EQUIPO Y CLIENTE'), 0, 0, 'L');

// Encabezado derecho a la misma altura
if (!empty($refacciones_pdf)) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetXY($rightX, $yEncabezado);
    $pdf->Cell($colWR, 6, utf8_decode('REFACCIONES UTILIZADAS:'), 0, 0, 'R');
}
$pdf->Ln(8);

$equipo_val = preg_replace('/^Equipo:\s*/', '', $equipo_info);
$cliente_val = preg_replace('/^Cliente:\s*/', '', $cliente_info);
$contacto_val = preg_replace('/^Contacto:\s*/', '', $contacto_info);

$pdf->SetTextColor(0, 0, 0);
$yDatosInicio = $pdf->GetY();

// --- Columna izquierda: Equipo / Cliente / Contacto ---
$pdf->SetXY($leftX, $yDatosInicio);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(18, $lineH, utf8_decode('Equipo:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell($colWL - 18, $lineH, utf8_decode($equipo_val), 0, 'L');

$pdf->SetX($leftX);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(18, $lineH, utf8_decode('Cliente:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell($colWL - 18, $lineH, utf8_decode($cliente_val), 0, 'L');

$pdf->SetX($leftX);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(18, $lineH, utf8_decode('Contacto:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell($colWL - 18, $lineH, utf8_decode($contacto_val), 0, 'L');
$yFinalIzqDatos = $pdf->GetY();

// --- Columna derecha: Refacciones (datos) ---
$yDerRef = $yDatosInicio;
if (!empty($refacciones_pdf)) {
    foreach ($refacciones_pdf as $ref) {
        $pdf->SetXY($rightX, $yDerRef);
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell($colWR, $lineH, utf8_decode('* ' . $ref['refaccion']), 0, 'R');
        $yDerRef = $pdf->GetY();
        if ($ref['precio_refaccion'] > 0) {
            $precioFmt = '$ ' . number_format($ref['precio_refaccion'], 0, '.', ',');
            $pdf->SetXY($rightX, $yDerRef);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(0, 128, 0);
            $pdf->Cell($colWR, $lineH, utf8_decode($precioFmt), 0, 1, 'R');
            $pdf->SetTextColor(0, 0, 0);
            $yDerRef = $pdf->GetY();
        }
    }
}

$pdf->SetY(max($yFinalIzqDatos, $yDerRef) + 2);
$pdf->Ln(1);

// Línea separadora
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(5);

// ========================================
// NOTAS DE RECEPCIÓN
// ========================================

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, utf8_decode('NOTAS DE RECEPCIÓN'), 0, 1, 'L');
$pdf->Ln(2);

// Cuadro con fondo gris claro
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetFillColor(248, 249, 250); // bg-light
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0);

$x = $pdf->GetX();
$y = $pdf->GetY();

// Calcular altura necesaria para el texto
$pdf->SetXY($x + 2, $y + 2);
$altura_notas_recep = $pdf->GetStringWidth($notas_recepcion) > 170 ? 20 : 15;
$pdf->Rect($x, $y, 180, $altura_notas_recep, 'FD');
$pdf->SetXY($x + 3, $y + 3);
$pdf->MultiCell(174, 5, utf8_decode($notas_recepcion), 0, 'J');
$pdf->Ln($altura_notas_recep - 5);

$pdf->Ln(5);

// ========================================
// PIE DE PÁGINA
// ========================================


$pdf->SetDrawColor(13, 110, 253);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 5, utf8_decode('Copyright © MESS 2026'), 0, 1, 'C');

// Generar PDF
$nombre_archivo = 'reporte_servicio_' . str_replace(['#', '/', '-'], '_', $folio) . '.pdf';
$pdf->Output('I', $nombre_archivo);
?>
