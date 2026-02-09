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

// Preparar datos para el PDF
$folio = '#MET-' . ($equipo['area'] ?? '00') . '-' . 
         date('Y', strtotime($equipo['fecha_registro'] ?? date('Y-m-d'))) . '-' . 
         str_pad($equipo['id_registro'] ?? '0', 2, '0', STR_PAD_LEFT);
$ingenieros = $equipo['nombres_ingenieros'] ?? 'No asignado';
$equipo_info = 'Equipo: ' . ($equipo['marca'] ?? 'N/A') . ' - ' . 
               ($equipo['modelo'] ?? 'N/A') . ' - S/N: ' . 
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

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 5, utf8_decode('INGENIERO(S):'), 0, 1, 'L');

// Dividir ingenieros por comas
$lista_ingenieros = explode(',', $ingenieros);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
foreach($lista_ingenieros as $ingeniero) {
    $pdf->Cell(5);
    $pdf->Cell(0, 5, utf8_decode('- ' . trim($ingeniero)), 0, 1, 'L');
}
$pdf->Ln(3);

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

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, utf8_decode('DATOS DEL EQUIPO Y CLIENTE'), 0, 1, 'L');
$pdf->Ln(2);

$equipo_val = preg_replace('/^Equipo:\s*/', '', $equipo_info);
$cliente_val = preg_replace('/^Cliente:\s*/', '', $cliente_info);
$contacto_val = preg_replace('/^Contacto:\s*/', '', $contacto_info);

$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(18, 5, utf8_decode('Equipo:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 5, utf8_decode($equipo_val), 0, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(18, 5, utf8_decode('Cliente:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 5, utf8_decode($cliente_val), 0, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(18, 5, utf8_decode('Contacto:'), 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->MultiCell(0, 5, utf8_decode($contacto_val), 0, 'L');
$pdf->Ln(3);

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
