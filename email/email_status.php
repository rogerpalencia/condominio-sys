<?php
// sys/email/email_status.php
@session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../core/PDO.class.php';

$conn = DB::getInstance();
if (!$conn) { echo json_encode(['status'=>'error','message'=>'Sin conexión']); exit; }

$tipo = strtolower(trim($_GET['tipo'] ?? $_POST['tipo'] ?? ''));
$id   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if (!in_array($tipo, ['master','notificacion','recibo'], true) || $id<=0) {
  echo json_encode(['status'=>'error','message'=>'Parámetros inválidos']); exit;
}

// enviados / fallidos
$st = $conn->prepare("
  SELECT
    SUM(CASE WHEN e.estado='enviado' THEN 1 ELSE 0 END) AS enviados,
    SUM(CASE WHEN e.estado='fallido' THEN 1 ELSE 0 END) AS fallidos
  FROM email.envio e
  WHERE e.tipo_documento=:t AND e.id_documento=:id
");
$st->execute([':t'=>$tipo, ':id'=>$id]);
$agg = $st->fetch(PDO::FETCH_ASSOC) ?: ['enviados'=>0,'fallidos'=>0];
$enviados = (int)$agg['enviados'];
$fallidos = (int)$agg['fallidos'];

// abiertos (distintos)
$st = $conn->prepare("
  SELECT COUNT(DISTINCT ev.id_envio)
  FROM email.envio_evento ev
  JOIN email.envio e ON e.id_envio = ev.id_envio
  WHERE e.tipo_documento=:t AND e.id_documento=:id AND ev.tipo_evento='apertura'
");
$st->execute([':t'=>$tipo, ':id'=>$id]);
$abiertos = (int)$st->fetchColumn();

// pendientes en cola
$st = $conn->prepare("
  SELECT COUNT(*) FROM email.cola
  WHERE tipo_documento=:t AND id_documento=:id AND estado='pendiente'
");
$st->execute([':t'=>$tipo, ':id'=>$id]);
$pendientes = (int)$st->fetchColumn();

echo json_encode(['status'=>'ok','enviados'=>$enviados,'abiertos'=>$abiertos,'fallidos'=>$fallidos,'pendientes'=>$pendientes]);
