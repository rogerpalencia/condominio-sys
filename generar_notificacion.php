<?php
/************************************************************
 *  Archivo: generar_notificacion.php
 *  Descripción: Genera un PDF con la Notificación de Cobro
 ************************************************************/

/*-------------------------------------------------------------
|  CONSTANTES DE ESTILO (idénticas a recibo)
--------------------------------------------------------------*/
define('FUENTE_FAMILIA',  'Arial');  // o 'Roboto' si tienes la fuente
define('FUENTE_GRAL',        9);
define('FUENTE_TITULO',     10);

/*-------------------------------------------------------------
|  INICIALIZACIÓN BÁSICA
--------------------------------------------------------------*/
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (!extension_loaded('mbstring')) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Extensión mbstring no habilitada.']); exit;
}
session_start();

/*-------------------------------------------------------------
|  CONEXIÓN Y LIBRERÍAS
--------------------------------------------------------------*/
require_once('fpdf.php');
require_once './core/PDO.class.php';
$conn = DB::getInstance();      // ← tu singleton
if ($conn === null) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Sin conexión BD.']); exit;
}

/*-------------------------------------------------------------
|  INPUT: id_notificacion (GET|POST)
--------------------------------------------------------------*/
/*-------------------------------------------------------------
|  INPUT: id_notificacion o token (GET|POST)
|  - Si llega id_notificacion => se usa directo.
|  - Si NO llega, pero llega token (hex-32) => se busca el id.
--------------------------------------------------------------*/




/*-------------------------------------------------------------
|  INPUT: token (preferido) o id_notificacion (GET|POST)
|  - Con token: acceso público sin sesión.
|  - Con id_notificacion: validar que pertenezca al condominio en sesión.
--------------------------------------------------------------*/
$id_notif = null;
$resolvedBy = null;

// 1) Intentar por token (prioridad)
$token = null;
if (isset($_POST['token'])) $token = trim((string)$_POST['token']);
if (isset($_GET['token']))  $token = trim((string)$_GET['token']) ?: $token;

if ($token !== null && preg_match('/^[a-f0-9]{32}$/i', $token)) {
    try {
        $st = $conn->prepare("SELECT id_notificacion FROM notificacion_cobro WHERE token = :t LIMIT 1");
        $st->execute([':t' => $token]);
        $tmp = $st->fetchColumn();
        if ($tmp) {
            $id_notif   = (int)$tmp;
            $resolvedBy = 'token';
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Error consultando token','detail'=>$e->getMessage()]);
        exit;
    }
}

// 2) Si no hubo token válido, intentar por id_notificacion
if (!$id_notif) {
    if (isset($_POST['id_notificacion']) && ctype_digit($_POST['id_notificacion'])) {
        $id_notif   = (int)$_POST['id_notificacion'];
        $resolvedBy = 'id';
    } elseif (isset($_GET['id_notificacion']) && ctype_digit($_GET['id_notificacion'])) {
        $id_notif   = (int)$_GET['id_notificacion'];
        $resolvedBy = 'id';
    }
}

// 3) Validar que tengamos algo
if (!$id_notif) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Falta token válido o id_notificacion.']);
    exit;
}

// 4) Si se resolvió por ID, validar que pertenezca al condominio en sesión
if ($resolvedBy === 'id') {
    $id_condo_sesion = (int)($_SESSION['id_condominio'] ?? 0);
    if ($id_condo_sesion <= 0) {
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'No hay condominio en sesión para validar el documento.']);
        exit;
    }

    try {
        $sqlChk = "
            SELECT i.id_condominio
            FROM notificacion_cobro n
            JOIN inmueble i ON i.id_inmueble = n.id_inmueble
            WHERE n.id_notificacion = :id
            LIMIT 1
        ";
        $st = $conn->prepare($sqlChk);
        $st->execute([':id' => $id_notif]);
        $id_condo_doc = (int)$st->fetchColumn();

        if (!$id_condo_doc) {
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'Documento no encontrado.']);
            exit;
        }
        if ($id_condo_doc !== $id_condo_sesion) {
            http_response_code(403);
            echo json_encode(['status'=>'error','message'=>'No autorizado para este documento.']);
            exit;
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['status'=>'error','message'=>'Error validando condominio','detail'=>$e->getMessage()]);
        exit;
    }
}

// A partir de aquí, $id_notif es seguro:
// - si vino por token: acceso público;
// - si vino por id: validado contra el condominio en sesión.







/*-------------------------------------------------------------
|  CONSULTA: cabecera notificación + condominio/inmueble
--------------------------------------------------------------*/
$sql = "
SELECT
    nc.id_notificacion,
    nc.fecha_emision,
    nc.fecha_vencimiento,
    nc.monto_total,
    nc.pronto_pago,
    nc.id_moneda,
    m.codigo              AS moneda_notif,
    i.id_inmueble,
    i.identificacion      AS inmueble_ident,
    i.alicuota,
    c.id_condominio,
    c.id_moneda           AS id_moneda_base,
    mb.codigo             AS moneda_base,
    c.url_logo_izquierda,
    c.url_logo_derecha,
    COALESCE(NULLIF(c.linea_1,'') , 'JUNTA DE CONDOMINIOS')  AS linea_1,
    COALESCE(NULLIF(c.linea_2,'') , 'CARACAS - VENEZUELA')    AS linea_2,
    COALESCE(NULLIF(c.linea_3,'') , 'RIF.: N/A')              AS linea_3,
    p.id_propietario,
    CONCAT(p.nombre1,' ',p.apellido1)                        AS propietario
FROM notificacion_cobro            nc
JOIN inmueble                      i  ON nc.id_inmueble   = i.id_inmueble
JOIN condominio                    c  ON nc.id_condominio = c.id_condominio
JOIN moneda                        m  ON nc.id_moneda     = m.id_moneda
JOIN moneda                        mb ON c.id_moneda      = mb.id_moneda
LEFT JOIN propietario_inmueble     pi ON pi.id_inmueble   = i.id_inmueble
LEFT JOIN propietario              p  ON pi.id_propietario = p.id_propietario
WHERE nc.id_notificacion = :id
LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':id'=>$id_notif]);
$head = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$head) {
    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Notificación no encontrada.']); exit;
}

/*-------------------------------------------------------------
|  CONSULTA: detalle notificación
--------------------------------------------------------------*/
$sql = "
SELECT
    d.descripcion,
    dm.monto            AS monto_general,
    d.monto             AS monto_inmueble
FROM notificacion_cobro_detalle            d
LEFT JOIN notificacion_cobro_detalle_master dm
       ON dm.id_detalle = d.id_detalle_origen
WHERE d.id_notificacion = :id
ORDER BY d.id_detalle";

$det = $conn->prepare($sql);
$det->execute([':id'=>$id_notif]);
$detalle = $det->fetchAll(PDO::FETCH_ASSOC);
if (!$detalle) $detalle = [];

/*-------------------------------------------------------------
|  CLASE PDF
--------------------------------------------------------------*/
if (!class_exists('FPDF')) {
    require_once('fpdf.php');
}
class PDFNotif extends FPDF {
    private $lI; private $lD; private $l1; private $l2; private $l3;
    function setEnc($lI,$lD,$l1,$l2,$l3){$this->lI=$lI;$this->lD=$lD;$this->l1=$l1;$this->l2=$l2;$this->l3=$l3;}
    function Header(){
        $this->SetMargins(15,15,15);
        $this->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO+1);
        if($this->lI && file_exists($this->lI)) $this->Image($this->lI,15,8,25);
        if($this->lD && file_exists($this->lD)) $this->Image($this->lD,170,8,25);
        $this->SetXY(20,10);
        $this->Cell(180,8,mb_convert_encoding($this->l1,'ISO-8859-1','UTF-8'),0,1,'C');
        $this->SetFont(FUENTE_FAMILIA,'',FUENTE_TITULO);
        $this->Cell(180,7,mb_convert_encoding($this->l2,'ISO-8859-1','UTF-8'),0,1,'C');
        $this->Cell(180,7,mb_convert_encoding($this->l3,'ISO-8859-1','UTF-8'),0,1,'C');
    }
    function Footer(){
        $this->SetY(-15);
        $this->SetFont(FUENTE_FAMILIA,'I',max(FUENTE_GRAL-1,6));
        $txt = 'Fecha: '.date('d/m/Y H:i:s').' | Usuario: '.($_SESSION['userid']??'N/A').' | IP: '.$_SERVER['REMOTE_ADDR'];
        $this->Cell(0,10,mb_convert_encoding($txt,'ISO-8859-1','UTF-8'),0,0,'L');
        $this->Cell(0,10,mb_convert_encoding('Página '.$this->PageNo(),'ISO-8859-1','UTF-8'),0,0,'R');
    }
}

/*-------------------------------------------------------------
|  INICIAR PDF
--------------------------------------------------------------*/
$fuente = FUENTE_FAMILIA;
$core   = ['Arial','Helvetica','Times','Courier','Symbol','ZapfDingbats'];
$pdf = new PDFNotif();
if(!in_array($fuente,$core,true)){
    $ruta = __DIR__."/fonts/{$fuente}-Regular.php";
    if(file_exists($ruta)){
        $pdf->AddFont($fuente,'', "{$fuente}-Regular.php");
        $pdf->AddFont($fuente,'B',"{$fuente}-Bold.php");
    } else $fuente='Arial';
}
$pdf->setEnc($head['url_logo_izquierda'],$head['url_logo_derecha'],
             $head['linea_1'],$head['linea_2'],$head['linea_3']);
$pdf->AddPage();
/* ----------------------------------------------------
 *  TÍTULO + CUADRO DE DATOS GENERALES (estilo recibo)
 * ---------------------------------------------------*/
$pdf->Ln(12);                                   // mismo salto que el recibo
$pdf->SetFont($fuente,'B',FUENTE_TITULO + 6);
$pdf->SetFillColor(200,200,200);
$pdf->SetDrawColor(100,100,100);
$pdf->SetLineWidth(0.3);
$pdf->Cell(180,8,
    mb_convert_encoding('NOTIFICACIÓN DE COBRO','ISO-8859-1','UTF-8'),
    1,1,'C',true);

$pdf->SetFont($fuente,'',FUENTE_GRAL);
$pdf->SetFillColor(248,248,248);
$pdf->SetDrawColor(100,100,100);
$pdf->Cell(180,0,'','T',1,'C');     // línea horizontal fina
$pdf->Ln(0.2);

/* Fila 1 */
$pdf->SetFont($fuente,'B',FUENTE_TITULO);
$pdf->Cell(35,8,mb_convert_encoding('Nº Notificación:','ISO-8859-1','UTF-8'),'L',0,'L',true);
$pdf->SetFont($fuente,'',FUENTE_GRAL);
$pdf->Cell(55,8,$head['id_notificacion'],0,0,'L',true);

$pdf->SetFont($fuente,'B',FUENTE_TITULO);
$pdf->Cell(45,8,mb_convert_encoding('Fecha Emisión:','ISO-8859-1','UTF-8'),'L',0,'L',true);
$pdf->SetFont($fuente,'',FUENTE_GRAL);
$pdf->Cell(45,8,date('d-m-Y',strtotime($head['fecha_emision'])),'R',1,'L',true);

/* Fila 2 */
$pdf->SetFont($fuente,'B',FUENTE_TITULO);
$pdf->Cell(35,8,mb_convert_encoding('Fecha Vencimiento:','ISO-8859-1','UTF-8'),'L',0,'L',true);
$pdf->SetFont($fuente,'',FUENTE_GRAL);
$pdf->Cell(55,8,date('d-m-Y',strtotime($head['fecha_vencimiento'])),0,0,'L',true);

$pdf->SetFont($fuente,'B',FUENTE_TITULO);
$pdf->Cell(45,8,'Inmueble:','L',0,'L',true);
$pdf->SetFont($fuente,'',FUENTE_GRAL);
$pdf->Cell(45,8,$head['inmueble_ident'],'R',1,'L',true);

/* Fila 3 */
$pdf->SetFont($fuente,'B',FUENTE_TITULO);
$pdf->Cell(35,8,'Propietario:','LB',0,'L',true);
$pdf->SetFont($fuente,'',FUENTE_GRAL);
$pdf->Cell(55,8,
    mb_convert_encoding($head['propietario']??'N/A','ISO-8859-1','UTF-8'),
    'B',0,'L',true);

$pdf->SetFont($fuente,'B',FUENTE_TITULO);
$pdf->Cell(45,8,'Alicuota:','LB',0,'L',true);
$pdf->SetFont($fuente,'',FUENTE_GRAL);
$pdf->Cell(45,8,
    number_format($head['alicuota'],7,',','.').'%',
    'RB',1,'L',true);

$pdf->Ln(10);



/*-------------------------------------------------------------
|  DETALLE
--------------------------------------------------------------*/
$pdf->Ln(5);
$pdf->SetFont($fuente,'B',FUENTE_TITULO+1);
$pdf->Cell(180,8,'Detalle de Conceptos',0,1,'L');

$pdf->SetFont($fuente,'B',FUENTE_TITULO-1);
$pdf->SetFillColor(200,200,200);
$pdf->Cell(120,8,mb_convert_encoding('Descripción','ISO-8859-1','UTF-8'),1,0,'C',true);


   

$pdf->Cell(30,8,'Monto Gral.',1,0,'C',true);
$pdf->Cell(30,8,mb_convert_encoding('Su Obligación','ISO-8859-1','UTF-8'),1,1,'C',true);

$pdf->SetFont($fuente,'',FUENTE_GRAL-1);
$total = 0; $fill=0;
foreach($detalle as $d){
$fill = 1-$fill;
$pdf->SetFillColor($fill?245:255, $fill?245:255, $fill?245:255);
$pdf->Cell(120,8,mb_convert_encoding($d['descripcion'],'ISO-8859-1','UTF-8'),1,0,'L',$fill);

$pdf->Cell(30,8,number_format($d['monto_general'],2,',','.'),1,0,'R',$fill);
$pdf->Cell(30,8,number_format($d['monto_inmueble'],2,',','.'),1,1,'R',$fill);
$total += (float)$d['monto_inmueble'];
}

/*-------------------------------------------------------------
|  TOTALES
--------------------------------------------------------------*/
$pdf->SetFont($fuente,'B',FUENTE_TITULO);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(120,8,'Subtotal:',1,0,'R',true);
$pdf->Cell(60,8,number_format($total,2,',','.').' '.$head['moneda_notif'],1,1,'R',true);

if((float)$head['pronto_pago']>0){
    $pdf->Cell(120,8,'Descuento pronto-pago:',1,0,'R',true);
    $pdf->Cell(60,8,'-'.number_format($head['pronto_pago'],2,',','.').' '.$head['moneda_notif'],1,1,'R',true);
    $total -= (float)$head['pronto_pago'];
}

$pdf->Cell(120,8,'TOTAL A PAGAR:',1,0,'R',true);
$pdf->Cell(60,8,number_format($total,2,',','.').' '.$head['moneda_notif'],1,1,'R',true);

/*-------------------------------------------------------------
|  OBSERVACIONES
--------------------------------------------------------------*/
$pdf->Ln(8);
$pdf->SetFont($fuente,'',8);
$pdf->MultiCell(180,5,'Observaciones: '.mb_convert_encoding(($head['observaciones']??'N/A'),'ISO-8859-1','UTF-8'),0,'L');

/*-------------------------------------------------------------
|  SALIDA
--------------------------------------------------------------*/
ob_end_clean();
$fname = 'notif_'.$head['id_notificacion'].'_'.$head['inmueble_ident'].'.pdf';
$pdf->Output('I',$fname);
?>