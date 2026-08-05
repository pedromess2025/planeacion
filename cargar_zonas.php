<?php
// Aplica a `usuarios` las correcciones capturadas a mano en el CSV de pendientes.
// Uso (CLI):  php cargar_zonas.php [ruta.csv] [--aplicar]
//             (por defecto lee .claude/ZONAS_PENDIENTES.csv y solo SIMULA)
//
// Resuelve dos pendientes del módulo de Disponibilidad, ambos llaveados por `noEmpleado`:
//   ZONA        -> `usuarios.zona`, que alimenta el filtro "Área / Laboratorio" de los dos grids.
//                  Sin zona, el ingeniero solo aparece en "Todas" y es invisible bajo cualquier filtro.
//   ID_USUARIO  -> `usuarios.id_usuario`, la llave con la que se cruzan servicios, SCOT y la captura
//                  manual de laboratorio. Los que la traen vacía salen en el grid (por la clave
//                  sintética del endpoint) pero nunca reflejan servicios.
//
// Columnas del CSV: noEmpleado, nombre, departamento, tipo_usr, zona_actual, ZONA, id_usuario_actual, ID_USUARIO
// Solo se escriben las celdas ZONA / ID_USUARIO que estén llenas; lo vacío se ignora (recargable).
// Sin --aplicar no toca nada: imprime el plan para revisarlo antes.
if (php_sapi_name() !== 'cli') { die('Solo CLI'); }
require __DIR__ . '/conn.php';

$args    = array_slice($argv, 1);
$aplicar = in_array('--aplicar', $args, true);
$rutas   = array_values(array_filter($args, function ($a) { return strpos($a, '--') !== 0; }));
$ruta    = $rutas[0] ?? (__DIR__ . '/.claude/ZONAS_PENDIENTES.csv');
if (!is_file($ruta)) { fwrite(STDERR, "No existe el archivo: $ruta\n"); exit(1); }

$fh = fopen($ruta, 'r');
$headers = fgetcsv($fh);
if (!$headers) { fwrite(STDERR, "CSV vacío o ilegible.\n"); exit(1); }
$headers = array_map(function ($h) { return trim($h, " \t\r\n\xEF\xBB\xBF"); }, $headers);
$idx = array_flip($headers);
foreach (['noEmpleado', 'ZONA', 'ID_USUARIO'] as $req) {
    if (!isset($idx[$req])) { fwrite(STDERR, "Falta la columna '$req' en el CSV.\n"); exit(1); }
}
$col = function ($row, $name) use ($idx) {
    return isset($idx[$name]) && isset($row[$idx[$name]]) ? trim($row[$idx[$name]]) : '';
};

// Zonas válidas = las que YA existen en la población del grid. Si el CSV trae una zona nueva se
// avisa (no se bloquea): puede ser un área legítima que aún no tiene a nadie asignado.
$zonasOk = [];
if ($r = $conn->query("SELECT DISTINCT zona FROM usuarios
                       WHERE estatus = 1 AND tipo_usr IN ('ING','JEFE_ENCARGADO','JEFE_LAB')
                         AND zona IS NOT NULL AND TRIM(zona) <> ''")) {
    while ($z = $r->fetch_assoc()) { $zonasOk[mb_strtolower($z['zona'])] = $z['zona']; }
}

$stZona = $conn->prepare("UPDATE usuarios SET zona = ? WHERE noEmpleado = ?");
$stIdU  = $conn->prepare("UPDATE usuarios SET id_usuario = ? WHERE noEmpleado = ?");

$nZona = 0; $nId = 0; $avisos = []; $saltados = 0;
while (($row = fgetcsv($fh)) !== false) {
    if (count(array_filter($row, function ($x) { return trim((string)$x) !== ''; })) === 0) continue;
    $noEmp  = $col($row, 'noEmpleado');
    $nombre = $col($row, 'nombre');
    if ($noEmp === '' || !ctype_digit($noEmp)) { $saltados++; continue; }

    $zona = $col($row, 'ZONA');
    if ($zona !== '') {
        if (!isset($zonasOk[mb_strtolower($zona)])) {
            $avisos[] = "zona nueva '$zona' ($nombre) — se creará al aplicar";
        } elseif ($zonasOk[mb_strtolower($zona)] !== $zona) {
            // Reusa la grafía exacta ya existente: el filtro compara con `zona = ?` (match exacto)
            $zona = $zonasOk[mb_strtolower($zona)];
        }
        echo "  ZONA        $noEmp  " . str_pad(mb_substr($nombre, 0, 32), 34) . "-> $zona\n";
        if ($aplicar) { $stZona->bind_param('si', $zona, $noEmp); $stZona->execute(); }
        $nZona++;
    }

    $idU = $col($row, 'ID_USUARIO');
    if ($idU !== '') {
        if (!ctype_digit($idU)) { $avisos[] = "ID_USUARIO '$idU' de $nombre no es numérico — se omite"; continue; }
        // Un id_usuario repetido colapsaría los dos registros en un solo renglón del grid
        $chk = $conn->prepare("SELECT COUNT(*) c FROM usuarios WHERE id_usuario = ? AND noEmpleado <> ?");
        $chk->bind_param('ii', $idU, $noEmp);
        $chk->execute();
        $ya = $chk->get_result()->fetch_assoc()['c'];
        $chk->close();
        if ($ya > 0) { $avisos[] = "ID_USUARIO $idU ($nombre) YA lo usa otro usuario — se omite"; continue; }
        echo "  ID_USUARIO  $noEmp  " . str_pad(mb_substr($nombre, 0, 32), 34) . "-> $idU\n";
        if ($aplicar) { $stIdU->bind_param('ii', $idU, $noEmp); $stIdU->execute(); }
        $nId++;
    }
}
fclose($fh);

echo "\nArchivo: $ruta\n";
echo ($aplicar ? "APLICADO" : "SIMULACIÓN (usa --aplicar para escribir)") . "\n";
echo "  zonas: $nZona   ·   id_usuario: $nId" . ($saltados ? "   ·   renglones saltados: $saltados" : '') . "\n";
foreach ($avisos as $a) { echo "  ⚠ $a\n"; }
