<?php
/************************************************************
 *  Archivo: generar_recibo.php
 *  Descripción: Genera un PDF con el/los recibo(s) de caja.
 *               Acepta:
 *                 • id_notificacion  (int)
 *                 • num_recibo       (varchar)
 *               El parámetro puede llegar por GET o POST.
 *               Si no llega ninguno, devuelve error 400.
 ************************************************************/

/*-------------------------------------------------------------
|  CONSTANTES DE ESTILO – AJUSTA AQUÍ
|--------------------------------------------------------------
|  • FUENTE_FAMILIA  →  Arial, Helvetica, Times… o Roboto*
|  • FUENTE_GRAL     →  tamaño base para texto normal
|  • FUENTE_TITULO   →  tamaño base para encabezados
|
|  *Para usar Roboto (u otra TTF) crea la carpeta /fonts
|   y coloca los archivos generados con makefont.php
|   Ej.: Roboto-Regular.php / Roboto-Regular.z,
|        Roboto-Bold.php    / Roboto-Bold.z
|   Si los archivos no existen, cae a Arial sin error.
--------------------------------------------------------------*/
define('FUENTE_FAMILIA',  'Arial');  // ‘Roboto’ para probar
define('FUENTE_GRAL',        9);     // texto normal
define('FUENTE_TITULO',     10);     // subtítulos / cabeceras

/*-------------------------------------------------------------
|  BOILERPLATE Y LOG
--------------------------------------------------------------*/
ob_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
error_log('Iniciando generar_recibo.php');

/*-------------------------------------------------------------
|  REQUISITOS MÍNIMOS
--------------------------------------------------------------*/
if (!extension_loaded('mbstring')) {
    error_log('Error: La extensión mbstring no está habilitada.');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'La extensión mbstring no está habilitada.']);
    exit;
}
if (!session_start()) {
    error_log('Error: No se pudo iniciar la sesión.');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al iniciar la sesión.']);
    exit;
}

/*-------------------------------------------------------------
|  CONEXIÓN A BD
--------------------------------------------------------------*/
require('fpdf.php');
require_once("./core/PDO.class.php");
$conn = DB::getInstance();
if ($conn === null) {
    error_log('Error: No se pudo conectar a la base de datos.');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos.']);
    exit;
}

/*-------------------------------------------------------------
|  CAPTURA SEGURA DE PARÁMETROS
--------------------------------------------------------------*/
$id_notificacion   = null;
$num_recibo        = null;
$id_recibo_directo = null;
$token_recibo = null;


/* prioridad: POST → GET */
if (isset($_POST['id_notificacion']) && ctype_digit($_POST['id_notificacion'])) {
    $id_notificacion = (int)$_POST['id_notificacion'];
} elseif (isset($_GET['id_notificacion']) && ctype_digit($_GET['id_notificacion'])) {
    $id_notificacion = (int)$_GET['id_notificacion'];
}

if (isset($_POST['num_recibo']) && trim($_POST['num_recibo']) !== '') {
    $num_recibo = trim($_POST['num_recibo']);
} elseif (isset($_GET['num_recibo']) && trim($_GET['num_recibo']) !== '') {
    $num_recibo = trim($_GET['num_recibo']);
}


if (isset($_GET['token']) && trim($_GET['token']) !== '') {
    $token_recibo = trim($_GET['token']);
} elseif (isset($_POST['token']) && trim($_POST['token']) !== '') {
    $token_recibo = trim($_POST['token']);
}





/* Validación: debe venir al menos uno */
if ($id_notificacion === null && $num_recibo === null && $token_recibo === null) {
    error_log('Error: No se recibió ni id_notificacion, num_recibo, ni un token.');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Debe proporcionar , token, id_notificacion o num_recibo.']);
    exit;
}



/*-------------------------------------------------------------
|  RUTA A)  Se recibió id_notificacion
--------------------------------------------------------------*/
$recibos     = [];
$id_inmueble = null;

if ($id_notificacion !== null) {
    /* inmueble desde notificacion */
    $sql = "SELECT id_inmueble 
            FROM notificacion_cobro 
            WHERE id_notificacion = :id_notificacion";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Notificación no encontrada.']);
        exit;
    }
    $id_inmueble = (int)$row['id_inmueble'];

    /* recibos asociados */
    $sql = "SELECT id_recibo 
            FROM recibo_destino_fondos 
            WHERE id_notificacion = :id_notificacion";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_notificacion', $id_notificacion, PDO::PARAM_INT);
    $stmt->execute();
    $recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($recibos)) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'No se encontraron recibos asociados a la notificación.']);
        exit;
    }
/*-------------------------------------------------------------
|  RUTA B)  Se recibió num_recibo
--------------------------------------------------------------*/
} 
if ($num_recibo !== null) {
    $sql = "SELECT id_recibo, id_inmueble 
            FROM recibo_cabecera 
            WHERE numero_recibo = :num_recibo 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':num_recibo', $num_recibo, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Número de recibo no encontrado.']);
        exit;
    }
    $id_recibo_directo = (int)$row['id_recibo'];
    $id_inmueble       = (int)$row['id_inmueble'];
    $recibos           = [['id_recibo' => $id_recibo_directo]];
}


/*-------------------------------------------------------------
|  RUTA TOKEN (prioridad máxima)
--------------------------------------------------------------*/


if ($token_recibo !== null) {
    $sql = "SELECT rc.id_recibo, rc.id_inmueble
              FROM recibo_cabecera rc
             WHERE rc.token = :token
             LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':token', $token_recibo, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Token de recibo no válido.']);
        exit;
    }

    $id_recibo_directo = (int)$row['id_recibo'];
    $id_inmueble       = (int)$row['id_inmueble'];
    $recibos           = [['id_recibo' => $id_recibo_directo]];
}









/*-------------------------------------------------------------
|  DATOS DEL CONDOMINIO (logos y líneas) vía id_inmueble
--------------------------------------------------------------*/
$sql = "SELECT 
            c.id_moneda,
            m.codigo           AS moneda_codigo,
            c.url_logo_izquierda,
            c.url_logo_derecha,
            COALESCE(NULLIF(c.linea_1, ''), 'JUNTA DE CONDOMINIOS DE LA URB. DE PRUEBA') AS linea_1,
            COALESCE(NULLIF(c.linea_2, ''), 'DIRECCIÓN CARACAS VENEZUELA')              AS linea_2,
            COALESCE(NULLIF(c.linea_3, ''), 'RIF. J-12345678-0')                         AS linea_3,
            i.alicuota
        FROM condominio c
        JOIN inmueble   i ON i.id_condominio = c.id_condominio
        JOIN moneda     m ON c.id_moneda     = m.id_moneda
        WHERE i.id_inmueble = :id_inmueble";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_inmueble', $id_inmueble, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Condominio no encontrado para el inmueble.']);
    exit;
}
$moneda_base_codigo = $row['moneda_codigo'];
$logo_izquierdo     = $row['url_logo_izquierda'] ?? null;
$logo_derecho       = $row['url_logo_derecha']   ?? null;
$linea_1            = $row['linea_1'];
$linea_2            = $row['linea_2'];
$linea_3            = $row['linea_3'];
$alicuota           = floatval($row['alicuota']) ?? 0.0;

/*-------------------------------------------------------------
|  OBTENER DATOS COMPLETOS DE CADA RECIBO
--------------------------------------------------------------*/
$recibos_data = [];

foreach ($recibos as $r) {
    $id_recibo = (int)$r['id_recibo'];

    /* cabecera del recibo */
    $sql = "SELECT
                rc.numero_recibo, rc.fecha_emision, rc.monto_total, rc.total_pagado, rc.observaciones,
                c.nombre AS condominio,
                i.identificacion AS inmueble,
                i.alicuota,
                CONCAT(p.nombre1, ' ', p.nombre2, ' ', p.apellido1, ' ', p.apellido2) AS propietario
            FROM recibo_cabecera rc
            LEFT JOIN condominio c ON rc.id_condominio = c.id_condominio
            LEFT JOIN propietario p ON rc.id_propietario = p.id_propietario
            LEFT JOIN inmueble   i ON rc.id_inmueble   = i.id_inmueble
            WHERE rc.id_recibo = :id_recibo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_recibo', $id_recibo, PDO::PARAM_INT);
    $stmt->execute();
    $recibo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$recibo) continue;

    /* origen de fondos */
    $sql = "SELECT 
                rof.tipo_origen, 
                rof.monto, 
                rof.monto_base, 
                rof.referencia, 
                rof.fecha_creacion,
                rof.id_moneda, 
                rof.tasa,
                m.codigo AS moneda
            FROM recibo_origen_fondos rof
            LEFT JOIN moneda m ON rof.id_moneda = m.id_moneda
            WHERE rof.id_recibo = :id_recibo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_recibo', $id_recibo, PDO::PARAM_INT);
    $stmt->execute();
    $origen_fondos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* destino de fondos */
    $sql = "SELECT rdf.id_notificacion, rdf.monto_aplicado, rdf.monto_base,
                   rdf.id_moneda, m.codigo AS moneda,
                   nc.fecha_emision, nc.descripcion,
                   nc.monto_total AS total_notificacion
            FROM recibo_destino_fondos rdf
            INNER JOIN notificacion_cobro nc ON rdf.id_notificacion = nc.id_notificacion
            LEFT JOIN moneda m ON rdf.id_moneda = m.id_moneda
            WHERE rdf.id_recibo = :id_recibo";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_recibo', $id_recibo, PDO::PARAM_INT);
    $stmt->execute();
    $destino_fondos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $recibos_data[] = [
        'recibo'         => $recibo,
        'origen_fondos'  => $origen_fondos,
        'destino_fondos' => $destino_fondos

    ];
}

if (empty($recibos_data)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'No se encontraron datos válidos para generar el PDF.']);
    exit;
}

/*-------------------------------------------------------------
|  CLASE PDF PERSONALIZADA
--------------------------------------------------------------*/
class MyPDF extends FPDF {
    private $logo_izq;
    private $logo_der;
    private $linea_1;
    private $linea_2;
    private $linea_3;

    public function setEncabezado($logoI, $logoD, $l1, $l2, $l3) {
        $this->logo_izq = $logoI;
        $this->logo_der = $logoD;
        $this->linea_1  = $l1;
        $this->linea_2  = $l2;
        $this->linea_3  = $l3;
    }

    public function Header() {
        $this->SetMargins(15, 15, 15);
        $this->SetFont(FUENTE_FAMILIA, 'B', FUENTE_TITULO + 1);

        if ($this->logo_izq && file_exists($this->logo_izq))  $this->Image($this->logo_izq, 15, 8, 25);
        if ($this->logo_der && file_exists($this->logo_der))  $this->Image($this->logo_der, 170, 8, 25);

        $this->SetXY(20, 10);
        $this->Cell(180, 8, mb_convert_encoding($this->linea_1, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

        $this->SetFont(FUENTE_FAMILIA, '', FUENTE_TITULO);
        $this->Cell(180, 7, mb_convert_encoding($this->linea_2, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->Cell(180, 7, mb_convert_encoding($this->linea_3, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont(FUENTE_FAMILIA, 'I', max(FUENTE_GRAL - 1, 6));
        $texto = 'Fecha: ' . date('d/m/Y H:i:s') .
                 ' | Usuario: ' . ($_SESSION['userid'] ?? 'N/A') .
                 ' | IP: ' . $_SERVER['REMOTE_ADDR'];
        $this->Cell(0, 10, mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        $this->Cell(0, 10, mb_convert_encoding('Página ' . $this->PageNo(), 'ISO-8859-1', 'UTF-8'), 0, 0, 'R');
    }
}

/*-------------------------------------------------------------
|  INICIALIZAR PDF Y REGISTRAR FUENTES
--------------------------------------------------------------*/
$pdf = new MyPDF();
/* Si la fuente no es core, intenta cargarla; si falla, usa Arial */
$fuentePdf = FUENTE_FAMILIA;
$coreFonts = ['Arial', 'Helvetica', 'Times', 'Courier', 'Symbol', 'ZapfDingbats'];
if (!in_array($fuentePdf, $coreFonts, true)) {
    $ruta = __DIR__ . "/fonts/{$fuentePdf}-Regular.php";
    if (file_exists($ruta)) {
        $pdf->AddFont($fuentePdf, '', "{$fuentePdf}-Regular.php");
        $pdf->AddFont($fuentePdf, 'B', "{$fuentePdf}-Bold.php");
    } else {
        $fuentePdf = 'Arial';
    }
}
$pdf->SetMargins(15, 15, 15);
$pdf->setEncabezado($logo_izquierdo, $logo_derecho, $linea_1, $linea_2, $linea_3);
$pdf->AddPage();

/*-------------------------------------------------------------
|  CONTENIDO
--------------------------------------------------------------*/
foreach ($recibos_data as $index => $data) {
    $recibo         = $data['recibo'];
    $origen_fondos  = $data['origen_fondos'];
    $destino_fondos = $data['destino_fondos'];

    /* Título Central */
    $pdf->Ln(12);
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO + 6);
    $pdf->SetFillColor(200,200,200);
    $pdf->SetDrawColor(100,100,100);
    $pdf->SetLineWidth(0.3);
    $pdf->Cell(180, 8, mb_convert_encoding('RECIBO DE CAJA', 'ISO-8859-1', 'UTF-8'), 1, 1, 'C', true);

    /* Bloque Básico */
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL);
    $pdf->SetFillColor(248,248,248);
    $pdf->SetDrawColor(100,100,100);
    $pdf->Cell(180, 0, '', 'T', 1, 'C');
    $pdf->Ln(0.2);

    /* --- FILA 1 --- */
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
    $pdf->Cell(35, 8, mb_convert_encoding('Número de Recibo:', 'ISO-8859-1', 'UTF-8'), 'L', 0, 'L', true);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL);
    $pdf->Cell(55, 8, $recibo['numero_recibo'], 0, 0, 'L', true);
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
    $pdf->Cell(45, 8, mb_convert_encoding('Fecha de Emisión:', 'ISO-8859-1', 'UTF-8'), 'L', 0, 'L', true);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL);
    $pdf->Cell(45, 8, date('d-m-Y', strtotime($recibo['fecha_emision'])), 'R', 1, 'L', true);

    /* --- FILA 2 --- */
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
    $pdf->Cell(35, 8, 'Propietario:', 'L', 0, 'L', true);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL);
    $pdf->Cell(55, 8, strtoupper(iconv('UTF-8', 'ISO-8859-1//TRANSLIT', ($recibo['propietario'] ?: 'N/A'))), 0, 0, 'L', true);
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
    $pdf->Cell(45, 8, 'Inmueble:', 'L', 0, 'L', true);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL);
    $pdf->Cell(45, 8, ($recibo['inmueble'] ?: 'N/A'), 'R', 1, 'L', true);

    /* --- FILA 3 --- */
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
    $pdf->Cell(35, 8, '', 'LB', 0, 'L', true);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL);
    $pdf->Cell(55, 8, ('' ?: ''), 'B', 0, 'L', true);
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
    $pdf->Cell(45, 8, 'Alicuota:', 'LB', 0, 'L', true);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL);
    $pdf->Cell(45, 8, isset($recibo['alicuota']) ? number_format($recibo['alicuota'], 7, ',', '.') : 'N/A', 'RB', 1, 'L', true);

    $pdf->Ln(10);

    /* ----------------------------------------------------
     * TABLA Origen de Fondos
     * ---------------------------------------------------*/
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO + 1);
    $pdf->Cell(180, 8, 'Detalle del Pago Recibido', 0, 1, 'L');

    $cols = [
        ['t' => 'Forma de Pago',    'w' => 30],
        ['t' => 'Referencia',       'w' => 29],
        ['t' => 'Fecha',            'w' => 20],
        ['t' => 'Monto',            'w' => 25],
        ['t' => 'Moneda',           'w' => 16],
        ['t' => 'Tasa Aplicada',    'w' => 30],
        ['t' => 'Monto'. ' ' . $moneda_base_codigo,         'w' => 30]
    ];
    $pdf->SetFillColor(200,200,200);
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO - 1);
    foreach ($cols as $c) $pdf->Cell($c['w'], 8, $c['t'], 1, 0, 'C', true);
    $pdf->Ln();

    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL - 1);
    $total_base_origen = 0; 
    $tot_mon = [];
    foreach ($origen_fondos as $idx => $o) {
        $fill = $idx % 2;
        $monto      = (float)$o['monto'];
        $monto_base = (float)$o['monto_base'];
        $moneda     = $o['moneda'] ?: 'N/A';
        $tasa       = $o['tasa'] ? (float)$o['tasa'] : null;
        $tot_mon[$moneda] = ($tot_mon[$moneda] ?? 0) + $monto;
        $total_base_origen += $monto_base;

        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell($cols[0]['w'], 8, $o['tipo_origen'], 1, 0, 'L', $fill);
        $pdf->Cell($cols[1]['w'], 8, ($o['referencia'] ?: 'N/A'), 1, 0, 'L', $fill);
        $pdf->Cell($cols[2]['w'], 8, date('d-m-Y', strtotime($o['fecha_creacion'])), 1, 0, 'C', $fill);
        $pdf->Cell($cols[3]['w'], 8, number_format($monto, 2, ',', '.'), 1, 0, 'R', $fill);
        $pdf->Cell($cols[4]['w'], 8, $moneda, 1, 0, 'C', $fill);
        $pdf->Cell($cols[5]['w'], 8, $tasa !== null ? number_format($tasa, 5, ',', '.') : 'N/A', 1, 0, 'R', $fill);
        $pdf->Cell($cols[6]['w'], 8, number_format($monto_base, 2, ',', '.') . ' ' . $moneda_base_codigo, 1, 1, 'R', $fill);
    }

    /* Subtotales origen */
    $pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
    $pdf->SetFillColor(230,230,230);
    $pdf->Cell(150, 8, 'Total en moneda base:', 1, 0, 'L', true);
    $pdf->Cell(30, 8, number_format($total_base_origen, 2, ',', '.') . ' ' . $moneda_base_codigo, 1, 1, 'R', true);
    $pdf->Ln(2);
    foreach ($tot_mon as $m => $tot) {
        $pdf->Cell(150, 8, 'Total en ' . $m . ':', 1, 0, 'L', true);
        $pdf->Cell(30, 8, number_format($tot, 2, ',', '.') . ' ' . $m, 1, 1, 'R', true);
    }

    $pdf->Ln(10);

 /* ----------------------------------------------------
 * TABLA Destino de Fondos
 * ---------------------------------------------------*/
$pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO + 1);
$pdf->Cell(180, 8, 'Notificaciones Pagadas', 0, 1, 'L');

$colsD = [
    ['t' => mb_convert_encoding('Notif.', 'ISO-8859-1', 'UTF-8'), 'w' => 19],
    ['t' => 'Fecha',        'w' => 19],
    ['t' => 'Concepto',     'w' => 37],
    ['t' => 'Total ', 'w' => 23],
    ['t' => 'Pagado', 'w' => 23],
    ['t' => '% pago',   'w' => 14],
    ['t' => 'Moneda',       'w' => 15],
    ['t' => 'Monto'. ' ' . $moneda_base_codigo, 'w' => 30]
];









$pdf->SetFillColor(200,200,200);
$pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO - 1);
foreach ($colsD as $c) $pdf->Cell($c['w'], 8, $c['t'], 1, 0, 'C', true);
$pdf->Ln();

$pdf->SetFont($fuentePdf, '', FUENTE_GRAL - 1);
$total_base_dest = 0; 
$tot2 = [];
foreach ($destino_fondos as $idx => $d) {
    $fill = $idx % 2;
    $monto = (float)$d['monto_aplicado'];
    $total_notificacion = (float)$d['total_notificacion'];
    $monto_base = (float)$d['monto_base'];
    $moneda = $d['moneda'] ?: 'N/A';
    $porcentaje_cubierto = $total_notificacion > 0 ? ($monto / $total_notificacion * 100) : 0;
    $tot2[$moneda] = ($tot2[$moneda] ?? 0) + $monto;
    $total_base_dest += $monto_base;

    $pdf->SetFillColor(245, 245, 245);
    $pdf->Cell($colsD[0]['w'], 8, $d['id_notificacion'], 1, 0, 'C', $fill);
    $pdf->Cell($colsD[1]['w'], 8, date('d-m-Y', strtotime($d['fecha_emision'])), 1, 0, 'C', $fill);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL - 3);
    $pdf->Cell($colsD[2]['w'], 8, $d['descripcion'], 1, 0, 'L', $fill);
    $pdf->SetFont($fuentePdf, '', FUENTE_GRAL - 1);
    $pdf->Cell($colsD[3]['w'], 8, number_format($total_notificacion, 2, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell($colsD[4]['w'], 8, number_format($monto, 2, ',', '.'), 1, 0, 'R', $fill);
    $pdf->Cell($colsD[5]['w'], 8, number_format($porcentaje_cubierto, 2, ',', '.') . '%', 1, 0, 'R', $fill);
    $pdf->Cell($colsD[6]['w'], 8, $moneda, 1, 0, 'C', $fill);
    $pdf->Cell($colsD[7]['w'], 8, number_format($monto_base, 2, ',', '.') . ' ' . $moneda_base_codigo, 1, 1, 'R', $fill);
}

$pdf->SetFont($fuentePdf, 'B', FUENTE_TITULO);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(150, 8, 'Total cubierto en moneda base:', 1, 0, 'L', true);
$pdf->Cell(30, 8, number_format($total_base_dest, 2, ',', '.') . ' ' . $moneda_base_codigo, 1, 1, 'R', true);
$pdf->Ln(2);
foreach ($tot2 as $m => $tot) {
    $pdf->Cell(150, 8, 'Total pagado en ' . $m . ':', 1, 0, 'L', true);   $pdf->Cell(30, 8, number_format($tot, 2, ',', '.') . ' ' . $m, 1, 1, 'R', true);
}

/* ----------------------------------------------------
 * Observaciones
 * ---------------------------------------------------*/
$pdf->Ln(10);
$pdf->SetFont($fuentePdf, '', 8);
$pdf->Cell(180, 8, 'Observaciones: ' . mb_convert_encoding($recibo['observaciones'] ?: 'N/A', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');

    /* Salto de página si quedan más */
    if ($index < count($recibos_data) - 1) $pdf->AddPage();
}

/*-------------------------------------------------------------
|  SALIDA
--------------------------------------------------------------*/
try {
    ob_end_clean();
    $nombre = $num_recibo ? 'recibo_'.$num_recibo.'.pdf'
                          : 'recibo_notificacion_'.$id_notificacion.'.pdf';
    $pdf->Output('I', $nombre);
} catch (Exception $e) {
    error_log('Error al generar el PDF: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al generar el PDF.']);
    exit;
}
?>
