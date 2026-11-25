<?php
// Funciones puras para la subida de "Análisis de servicios IA".
// Sin BD, sin sesión, sin salida: testeables por CLI con php -l / php.
// Modo RECHAZAR lo peligroso, scripts internos permitidos: un HTML con
// código peligroso no se guarda nada; validarArchivoAnalisisIA()
// devuelve ok=false con el motivo, sin el contenido. Los reportes de BI
// (p. ej. tablero-de-capacidad-de-laboratorios.html) necesitan <script>
// para sus filtros y gráficas, así que <script> inline o con src relativo /
// messbook.com.mx está permitido; lo que se rechaza es el script que roba
// sesión, sale del frame o carga código de un dominio externo.
//
// Extensiones permitidas exactas: xls, csv, html (minúsculas). NO xlsx.
// Tamaño máximo: 50 MB (lo verifica el endpoint y también esta lib).
// No hay historial ni sufijos: si el nombre sanitizado ya existe en el
// destino, el endpoint lo sobrescribe (el historial vive en otro repo).

const ANALISIS_IA_MAX_BYTES = 50 * 1024 * 1024; // 50 MB
const ANALISIS_IA_EXT_PERMITIDAS = ['xls', 'csv', 'html'];
// Extensiones que nunca se permiten, en ninguna parte del nombre.
const ANALISIS_IA_EXT_PELIGROSAS = [
    'php', 'php3', 'php4', 'php5', 'php7', 'phps', 'phtml', 'phar',
    'cgi', 'pl', 'py', 'js', 'jsp', 'asp', 'aspx', 'sh', 'exe', 'com', 'bat',
];

// Limpia un nombre de archivo subido. Devuelve '' si no queda nada válido.
// - basename (quita directorios y ../), quita bytes nulos.
// - Espacios a _, solo [A-Za-z0-9._-], colapsa puntos seguidos.
// - No permite nombre vacío ni que empiece por punto.
function sanitizarNombreArchivo($nombre) {
    $nombre = (string)$nombre;
    $nombre = str_replace("\0", '', $nombre);
    $nombre = str_replace('\\', '/', $nombre);
    $nombre = basename($nombre);
    $nombre = trim($nombre);
    if ($nombre === '' || $nombre[0] === '.') {
        return '';
    }
    $nombre = str_replace(' ', '_', $nombre);
    $nombre = preg_replace('/[^A-Za-z0-9._-]/u', '_', $nombre);
    $nombre = preg_replace('/\.{2,}/', '.', $nombre);
    $nombre = trim($nombre, '.');
    if ($nombre === '' || $nombre[0] === '.') {
        return '';
    }
    return $nombre;
}


// ¿Es una URL externa a messbook.com.mx? Relativa = interna.
// "//evil.example/x" y "http(s)://otro-dominio/..." = externa.
// "https://messbook.com.mx/..." y subdominios = interna.
function analisis_ia_esUrlExterna($url) {
    $url = trim((string)$url);
    if ($url === '') return false;
    $u = strtolower($url);
    if (strpos($u, 'javascript:') === 0 || strpos($u, 'data:') === 0) return true;
    if (strpos($u, '//') === 0) return true;
    if (preg_match('~^https?://([^/?#]+)~', $u, $m)) {
        $host = strtolower($m[1]);
        $host = explode(':', $host)[0];
        if ($host === 'messbook.com.mx') return false;
        if (substr($host, -16) === '.messbook.com.mx') return false;
        return true;
    }
    return false;
}

// Revisa el contenido de un <script>. Devuelve el motivo o ''.
// Patrones peligrosos (insensible a mayúsculas, con espacios):
// robo de sesión, salida del frame, eval/Function, popups, y
// llamadas de red a URLs externas (fetch / XHR / import / location).
function analisis_ia_scriptPeligroso($js) {
    $js = (string)$js;
    $reglas = [
        ['/document\.cookie/i', 'document.cookie'],
        ['/\b(localStorage|sessionStorage)\b/i', 'acceso a almacenamiento del navegador (localStorage / sessionStorage)'],
        ['/\b(window\.top|top\.location|parent\.location|parent\.document|window\.parent)\b/i', 'acceso al frame padre (window.top / parent)'],
        ['/\beval\s*\(/i', 'eval()'],
        ['/\bnew\s+Function\s*\(/i', 'new Function()'],
        ['/\bwindow\.open\s*\(/i', 'window.open()'],
        ['/\bfetch\s*\(\s*[\'"]\s*(https?:)?\/\//i', 'fetch() a una URL externa'],
        ['/\.open\s*\(\s*[\'"][A-Z]+[\'"]\s*,\s*[\'"]\s*(https?:)?\/\//i', 'petición (XMLHttpRequest) a una URL externa'],
        ['/\bimport\s*\(\s*[\'"]\s*(https?:)?\/\//i', 'import() de una URL externa'],
        ['/(?:location|window\.location|top\.location)\s*\.\s*href\s*=\s*[\'"]\s*(https?:)?\/\//i', 'redirección (location.href) a una URL externa'],
        ['/location\s*\.\s*(?:assign|replace)\s*\(\s*[\'"]\s*(https?:)?\/\//i', 'redirección (location.assign/replace) a una URL externa'],
        ['/document\.write\s*\([^)]*(https?:)?\/\//i', 'document.write() con una URL externa'],
    ];
    foreach ($reglas as $r) {
        if (preg_match($r[0], $js)) {
            return 'El script contiene código peligroso (' . $r[1] . ').';
        }
    }
    return '';
}

// Revisa el src de un <script>. Devuelve el motivo o ''.
function analisis_ia_scriptSrcPeligroso($src) {
    $src = trim((string)$src);
    if ($src === '') return '';
    if (stripos($src, 'javascript:') === 0) {
        return 'El <script> usa un src javascript:.';
    }
    if (stripos($src, 'data:') === 0) {
        return 'El <script> usa un src data:.';
    }
    if (analisis_ia_esUrlExterna($src)) {
        return 'El <script> carga código de un dominio externo (' . $src . ').';
    }
    return '';
}

// Valida un archivo ya recibido en $rutaTmp.
// $nombre es el nombre completo (sanitizado) del archivo; la extensión se
// deriva de él. $contenido, si se pasa, se usa en vez de leer el archivo
// (útil para pruebas CLI); si no, se lee de $rutaTmp.
// Devuelve ['ok' => bool, 'motivo' => string]. El motivo nombra lo que se
// encontró, sin volcar el contenido del archivo.
function validarArchivoAnalisisIA($rutaTmp, $nombre, $contenido = null) {
    $nombre = sanitizarNombreArchivo($nombre);
    if ($nombre === '') {
        return ['ok' => false, 'motivo' => 'Nombre de archivo no válido.'];
    }

    $partes = explode('.', $nombre);
    if (count($partes) < 2) {
        return ['ok' => false, 'motivo' => 'El archivo no tiene extensión. Solo se permiten .xls, .csv y .html.'];
    }
    $ext = strtolower(end($partes));
    // Doble extensión / extensión peligrosa en cualquier parte del nombre:
    // "informe.php.html" o "informe.html.php" se rechazan igual.
    foreach (array_slice($partes, 1) as $p) {
        if (in_array(strtolower($p), ANALISIS_IA_EXT_PELIGROSAS, true)) {
            return ['ok' => false, 'motivo' => 'Extensión no permitida en el nombre del archivo (.' . strtolower($p) . ').'];
        }
    }
    if (!in_array($ext, ANALISIS_IA_EXT_PERMITIDAS, true)) {
        return ['ok' => false, 'motivo' => 'Solo se permiten archivos .xls, .csv y .html (recibí ".' . $ext . '").'];
    }

    if ($contenido === null) {
        if (!is_file($rutaTmp)) {
            return ['ok' => false, 'motivo' => 'No se pudo leer el archivo subido.'];
        }
        $tam = filesize($rutaTmp);
        if ($tam === false || $tam > ANALISIS_IA_MAX_BYTES) {
            return ['ok' => false, 'motivo' => 'El archivo excede el tamaño máximo de 50 MB.'];
        }
        $contenido = file_get_contents($rutaTmp);
        if ($contenido === false) {
            return ['ok' => false, 'motivo' => 'No se pudo leer el archivo subido.'];
        }
    } else {
        if (strlen($contenido) > ANALISIS_IA_MAX_BYTES) {
            return ['ok' => false, 'motivo' => 'El archivo excede el tamaño máximo de 50 MB.'];
        }
    }

    // Código de servidor disfrazado, en cualquier tipo permitido.
    if (stripos($contenido, '<?php') !== false || stripos($contenido, '<?=') !== false
        || stripos($contenido, '<%') !== false) {
        return ['ok' => false, 'motivo' => 'El archivo contiene código de servidor (<?php / <?= / <%).'];
    }

    if ($ext === 'csv') {
        if (stripos($contenido, '<script') !== false) {
            return ['ok' => false, 'motivo' => 'El CSV contiene una etiqueta <script>.'];
        }
        return ['ok' => true, 'motivo' => ''];
    }

    if ($ext === 'xls') {
        // Un .xls real es OLE: D0 CF 11 E0 A1 B1 1A E1.
        $magia = substr($contenido, 0, 8);
        if ($magia !== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
            return ['ok' => false, 'motivo' => 'El archivo .xls no tiene el formato esperado de Excel (no es OLE).'];
        }
        if (stripos(substr($contenido, 0, 8192), '<?php') !== false) {
            return ['ok' => false, 'motivo' => 'El archivo .xls contiene código de servidor (<?php).'];
        }
        return ['ok' => true, 'motivo' => ''];
    }

    // html: modo RECHAZAR lo peligroso, scripts internos permitidos.
    // <script> inline o con src relativo / messbook.com.mx está permitido
    // (los reportes lo necesitan para filtros y gráficas); se rechaza el
    // script que roba sesión, sale del frame o carga código externo.
    // Etiquetas peligrosas: script ya NO está en esta lista.
    $etiquetasPeligrosas = ['iframe', 'object', 'embed', 'form', 'meta', 'link', 'base'];
    // Fallback si el servidor no tiene la extensión php-xml (DOMDocument):
    // mismo rechazo, por regex, sin morir con un fatal.
    if (!class_exists('DOMDocument')) {
        if (preg_match('/<\s*(iframe|object|embed|form|meta|link|base)\b/i', $contenido, $m)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene una etiqueta no permitida <' . strtolower($m[1]) . '>.'];
        }
        // Scripts: revisa cada <script> (src y contenido).
        if (preg_match_all('/<script\b([^>]*)>(.*?)<\/script\s*>/is', $contenido, $bloques, PREG_SET_ORDER)) {
            foreach ($bloques as $b) {
                if (preg_match('/\bsrc\s*=\s*[\'"]([^\'"]*)[\'"]/i', $b[1], $s)) {
                    $mot = analisis_ia_scriptSrcPeligroso($s[1]);
                    if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
                }
                $mot = analisis_ia_scriptPeligroso($b[2]);
                if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            }
        }
        // <script src="..."> sin cierre (también hay que revisarlo).
        if (preg_match_all('/<script\b[^>]*\bsrc\s*=\s*[\'"]([^\'"]*)[\'"]/i', $contenido, $srcs)) {
            foreach ($srcs[1] as $s) {
                $mot = analisis_ia_scriptSrcPeligroso($s);
                if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            }
        }
        if (preg_match('/\s(on\w+)\s*=/i', $contenido, $m)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene un atributo de evento no permitido (' . strtolower($m[1]) . ').'];
        }
        if (preg_match('/\b(href|src|action|formaction)\s*=\s*[\'"]?\s*javascript\s*:/i', $contenido)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene un enlace javascript:.'];
        }
        if (preg_match('/\b(href|src|action|formaction)\s*=\s*[\'"]?\s*data\s*:\s*text\/html/i', $contenido)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene un enlace data:text/html.'];
        }
        if (preg_match('/<style[^>]*>.*?javascript\s*:/is', $contenido)
            || preg_match('/<style[^>]*>.*?expression\s*\(/is', $contenido)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene CSS con código (javascript: / expression()).'];
        }
        return ['ok' => true, 'motivo' => ''];
    }
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $cargado = $doc->loadHTML($contenido, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    if (!$cargado) {
        return ['ok' => false, 'motivo' => 'El HTML no se pudo analizar.'];
    }
    foreach ($doc->getElementsByTagName('*') as $el) {
        $tag = strtolower($el->tagName);
        if ($tag === 'script') {
            // Script permitido, pero revisado: src externo o contenido peligroso.
            if ($el->hasAttribute('src')) {
                $mot = analisis_ia_scriptSrcPeligroso($el->getAttribute('src'));
                if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            }
            $mot = analisis_ia_scriptPeligroso($el->textContent);
            if ($mot !== '') return ['ok' => false, 'motivo' => $mot];
            continue;
        }
        if (in_array($tag, $etiquetasPeligrosas, true)) {
            return ['ok' => false, 'motivo' => 'El HTML contiene una etiqueta no permitida <' . $tag . '>.'];
        }
        if ($el->hasAttributes()) {
            foreach ($el->attributes as $attr) {
                $an = strtolower($attr->name);
                $av = trim(strtolower($attr->value));
                if (strpos($an, 'on') === 0) {
                    return ['ok' => false, 'motivo' => 'El HTML contiene un atributo de evento no permitido (' . $an . ').'];
                }
                if (strpos($av, 'javascript:') === 0) {
                    return ['ok' => false, 'motivo' => 'El HTML contiene un enlace javascript: en el atributo ' . $an . '.'];
                }
                if (strpos($av, 'data:text/html') === 0) {
                    return ['ok' => false, 'motivo' => 'El HTML contiene un enlace data:text/html en el atributo ' . $an . '.'];
                }
            }
        }
    }
    // <style> con javascript: o expression() tampoco pasa.
    if (preg_match('/<style[^>]*>.*?javascript\s*:/is', $contenido)
        || preg_match('/<style[^>]*>.*?expression\s*\(/is', $contenido)) {
        return ['ok' => false, 'motivo' => 'El HTML contiene CSS con código (javascript: / expression()).'];
    }

    return ['ok' => true, 'motivo' => ''];
}
