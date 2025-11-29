<?php
/************************************************************
 * generar_notificacion_master.php — Acceso por TOKEN o por ID
 * - TOKEN (público): no requiere sesión.
 * - ID (privado): requiere usuario logueado y pertenencia al condominio del doc,
 *                 aun cuando no haya id_condominio en la sesión.
 ************************************************************/

/*-------------------------------------------------------------
|  CONSTANTES DE ESTILO (idénticas a recibo)
--------------------------------------------------------------*/
define('FUENTE_FAMILIA',  'Arial');  // o 'Roboto' si tienes la fuente
define('FUENTE_GRAL',        9);
define('FUENTE_TITULO',     10);
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
require_once  'fpdf.php';
require_once './core/PDO.class.php';
$conn = DB::getInstance();
if ($conn === null) {
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Sin conexión BD.']); exit;
}

$resolvedBy = null;
$id_master  = 0;
$token      = null;

/* -------- 1) TOKEN (público, sin sesión) -------- */
if (isset($_GET['token']))  $token = trim((string)$_GET['token']);
if (isset($_POST['token'])) $token = trim((string)$_POST['token']) ?: $token;

if ($token && preg_match('/^[a-f0-9]{32}$/i', $token)) {
  $st = $conn->prepare("SELECT id_notificacion_master, id_condominio
                        FROM notificacion_cobro_master
                        WHERE token = :t LIMIT 1");
  $st->execute([':t'=>$token]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Token no encontrado.']); exit; }
  $id_master    = (int)$row['id_notificacion_master'];
  $idCondoDoc   = (int)$row['id_condominio'];
  $resolvedBy   = 'token';
}

/* -------- 2) ID (privado, requiere usuario y pertenencia) -------- */
if (!$id_master) {
  if (isset($_GET['id_notificacion_master']) && ctype_digit($_GET['id_notificacion_master'])) {
    $id_master = (int)$_GET['id_notificacion_master'];
  } elseif (isset($_POST['id_notificacion_master']) && ctype_digit($_POST['id_notificacion_master'])) {
    $id_master = (int)$_POST['id_notificacion_master'];
  }
  if ($id_master > 0) $resolvedBy = 'id';
}

if (!$id_master) {
  http_response_code(400);
  echo json_encode(['status'=>'error','message'=>'Falta token válido o id_notificacion_master.']);
  exit;
}

/* -------- 3) Seguridad por camino -------- */
/* -------- 3) Seguridad por camino -------- */
if ($resolvedBy === 'id') {
  // Debe estar logueado
  $idUsuario = isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : 0;
  if ($idUsuario <= 0) {
    http_response_code(401);
    echo json_encode(['status'=>'error','message'=>'Sesion requerida.']); exit;
  }

  // Debe existir id_condominio en sesion (condominio activo)
  $idCondoSess = isset($_SESSION['id_condominio']) ? (int)$_SESSION['id_condominio'] : 0;
  if ($idCondoSess <= 0) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'No hay condominio activo en sesion.']); exit;
  }

  // Obtener el condominio del documento
  $st = $conn->prepare("SELECT id_condominio FROM notificacion_cobro_master WHERE id_notificacion_master = :id LIMIT 1");
  $st->execute([':id'=>$id_master]);
  $idCondoDoc = (int)$st->fetchColumn();
  if ($idCondoDoc <= 0) {
    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Documento no encontrado.']); exit;
  }

  // EXIGIR que el documento pertenezca EXACTAMENTE al condominio activo en sesion
  if ($idCondoDoc !== $idCondoSess) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'La notificacion no pertenece al condominio activo.']); exit;
  }
}

// A partir de aquí: usa $id_master y (si necesitas) $idCondoDoc para logos/membrete.
// ... resto de tu generación de PDF ...


$sql = "
SELECT  ncm.*,                                          
        m.codigo                AS moneda_notif,
        mb.codigo               AS moneda_base,
        c.url_logo_izquierda,
        c.url_logo_derecha,
        COALESCE(NULLIF(c.linea_1,'') , 'JUNTA DE CONDOMINIOS') AS linea_1,
        COALESCE(NULLIF(c.linea_2,'') , 'CARACAS - VENEZUELA')  AS linea_2,
        COALESCE(NULLIF(c.linea_3,'') , 'RIF.: N/A')            AS linea_3,
        c.nombre                AS nombre_condominio
FROM    notificacion_cobro_master ncm
JOIN    moneda m   ON m.id_moneda  = ncm.id_moneda
JOIN    condominio c ON c.id_condominio = ncm.id_condominio
JOIN    moneda mb  ON mb.id_moneda = c.id_moneda
WHERE   ncm.id_notificacion_master = :id
LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':id'=>$id_master]);
$h = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$h) { http_response_code(404); exit('No encontrada'); }

/* ===== 5.  DETALLE ======================================= */
$sql = "SELECT descripcion, monto
        FROM notificacion_cobro_detalle_master
        WHERE id_notificacion_master = :id
        ORDER BY id_detalle";
$stmt = $conn->prepare($sql);
$stmt->execute([':id'=>$id_master]);
$detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===== 6.  Helper para ISO-8859-1 ======================== */
function iso($txt){ return iconv('UTF-8','ISO-8859-1//TRANSLIT',$txt); }

/* ===== 7.  CLASE PDF ===================================== */
class PDFM extends FPDF{
  private $lI,$lD,$l1,$l2,$l3;
  function setEnc($a,$b,$c,$d,$e){$this->lI=$a;$this->lD=$b;$this->l1=$c;$this->l2=$d;$this->l3=$e;}
  function Header(){
    $this->SetMargins(15,15,15);
    $this->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO+1);
    if($this->lI && file_exists($this->lI)) $this->Image($this->lI,15,8,25);
    if($this->lD && file_exists($this->lD)) $this->Image($this->lD,170,8,25);
    $this->SetXY(20,10);
    $this->Cell(180,8,iso($this->l1),0,1,'C');
    $this->SetFont(FUENTE_FAMILIA,'',FUENTE_TITULO);
    $this->Cell(180,7,iso($this->l2),0,1,'C');
    $this->Cell(180,7,iso($this->l3),0,1,'C');
  }
  function Footer(){
    $this->SetY(-15);
    $this->SetFont(FUENTE_FAMILIA,'I',max(FUENTE_GRAL-1,6));
    $txt='Fecha: '.date('d/m/Y H:i:s').
         ' | Usuario: '.($_SESSION['userid']??'N/A').
         ' | IP: '.$_SERVER['REMOTE_ADDR'];
    $this->Cell(0,10,iso($txt),0,0,'L');
    $this->Cell(0,10,iso('Página '.$this->PageNo()),0,0,'R');
  }
}

/* ===== 8.  INICIAR PDF =================================== */
$pdf = new PDFM();
$pdf->setEnc($h['url_logo_izquierda'],$h['url_logo_derecha'],$h['linea_1'],$h['linea_2'],$h['linea_3'], $h['id_tipo']);
$pdf->AddPage();

/* ===== 9.  TÍTULO + SUBTÍTULO PERIODO ==================== */
$pdf->Ln(12);
$pdf->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO+6);
$pdf->SetFillColor(200,200,200);


if($h['id_tipo'] == 1) {
    $pdf->Cell(180,8,iso('NOTIFICACIÓN DE COBRO MAESTRA'),1,1,'C',true);
} else {
    $pdf->Cell(180,8,iso('RELACION DE INGRESOS Y EGRESOS'),1,1,'C',true);
}



/* Subtítulo – periodo mes/año (si mes ≠ 0) */
$pdf->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO);
$mesNom = ['', 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio',
           'Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$periodo = ($h['mes'] ? $mesNom[(int)$h['mes']] . ' ' : '').$h['anio'];
if(trim($periodo)!=='') {
  $pdf->Cell(180,7,iso($periodo),0,1,'C');
}
$pdf->Ln(3);

/* ===== 10.  CUADRO DE DATOS ============================== */
$pdf->SetFont(FUENTE_FAMILIA,'',FUENTE_GRAL);
$pdf->SetFillColor(248,248,248);
$pdf->SetDrawColor(100,100,100);

$pdf->Cell(180,0,'','T',1); $pdf->Ln(0.2);

function fila($pdf,$lab,$val,$break=false){
  $pdf->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO);
  $pdf->Cell(40,8,iso($lab),'L',0,'L',true);
  $pdf->SetFont(FUENTE_FAMILIA,'',FUENTE_GRAL);
  $pdf->Cell(50,8,iso($val),$break?'R':0, $break?1:0,'L',true);
}

fila($pdf,'Nº Maestra:',$h['id_notificacion_master']);
fila($pdf,'Fecha Emisión:',date('d-m-Y',strtotime($h['fecha_emision'])),true);

fila($pdf,'Estado:',ucfirst($h['estado']));
fila($pdf,'Moneda Base:',$h['moneda_base'],true);

fila($pdf,'Condominio:',iso($h['nombre_condominio']));
$tipoTxt = $h['id_tipo']==1 ? 'Presupuesto' : 'Relación';
fila($pdf,'Tipo:',$tipoTxt,true);
$pdf->Cell(180,0,'','B',1);     // línea horizontal de cierre

$pdf->Ln(10);

/* ===== 11.  DETALLE ====================================== */
$pdf->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO+1);
$pdf->Cell(180,8,'Detalle de Conceptos',0,1,'L');

$pdf->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO-1);
$pdf->SetFillColor(200,200,200);
$pdf->Cell(120,8,iso('Descripción'),1,0,'C',true);
$pdf->Cell(60,8,'Monto ('.$h['moneda_notif'].')',1,1,'C',true);

$pdf->SetFont(FUENTE_FAMILIA,'',FUENTE_GRAL-1);
$total=0;$fill=0;
foreach($detalle as $d){
  $fill=1-$fill;
  $pdf->SetFillColor($fill?245:255,$fill?245:255,$fill?245:255);
  $pdf->Cell(120,8,iso($d['descripcion']),1,0,'L',$fill);
  $pdf->Cell(60,8,number_format($d['monto'],2,',','.'),1,1,'R',$fill);
  $total+=(float)$d['monto'];
}

/* ===== 12.  TOTALES ====================================== */
$pdf->SetFont(FUENTE_FAMILIA,'B',FUENTE_TITULO);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(120,8,'TOTAL GENERAL:',1,0,'R',true);
$pdf->Cell(60,8,number_format($total,2,',','.').' '.$h['moneda_notif'],1,1,'R',true);

/* ===== 13.  DESCRIPCIÓN LIBRE ============================ */
$pdf->Ln(8);
$pdf->SetFont(FUENTE_FAMILIA,'',8);
$lbl = mb_convert_encoding('Descripción: ','ISO-8859-1','UTF-8');
$pdf->MultiCell(180,5,$lbl.
     mb_convert_encoding($h['descripcion'] ?? 'N/A','ISO-8859-1','UTF-8'),
     0,'L');

/* ===== 14.  SALIDA ======================================= */
ob_end_clean();
$nom = 'notif_master_'.$h['id_notificacion_master'].'_'.$periodo.'.pdf';
$pdf->Output('I',$nom);
?>
