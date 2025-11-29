<?php
// sys/email/email_enqueue.php
@session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../core/PDO.class.php';
require_once __DIR__ . '/lib_email.php';

$conn = DB::getInstance();
if (!$conn) { echo json_encode(['status'=>'error','message'=>'Sin conexión BD']); exit; }

$idUsuario = (int)($_SESSION['userid'] ?? 0);
$tipo = strtolower(trim($_POST['tipo'] ?? ''));
$id   = (int)($_POST['id'] ?? 0);
$modo = strtolower(trim($_POST['modo'] ?? 'todos')); // todos|solo_fallidos|solo_no_enviados|custom
$destinosRaw = trim($_POST['destinos'] ?? '');

if (!in_array($tipo, ['master','notificacion','recibo'], true) || $id<=0) {
  echo json_encode(['status'=>'error','message'=>'Parámetros inválidos']); exit;
}

// Doc + condo
$info = email_doc_info($conn, $tipo, $id);
if (!$info) { echo json_encode(['status'=>'error','message'=>'Documento no encontrado']); exit; }
$idCondo = (int)$info['id_condominio'];

// Seguridad: sólo usuarios con acceso al condominio del doc
if (!email_check_access($conn, $idUsuario, $idCondo)) {
  echo json_encode(['status'=>'error','message'=>'No autorizado para este condominio']); exit;
}

// Destinos custom (si aplica)
$custom = [];
if ($modo === 'custom' && $destinosRaw !== '') {
  $pieces = preg_split('/[;,]+/', $destinosRaw);
  foreach ($pieces as $p) {
    $e = trim($p);
    if ($e !== '') $custom[] = $e;
  }
}

try {
  $conn->beginTransaction();
  $res = email_enqueue_doc($conn, $tipo, $id, $modo, $custom, $idUsuario);
  $conn->commit();
  echo json_encode($res);
} catch (Throwable $e) {
  $conn->rollBack();
  echo json_encode(['status'=>'error','message'=>'Error encolando','detail'=>$e->getMessage()]);
}
