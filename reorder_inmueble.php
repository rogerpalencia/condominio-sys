<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';

try {
  $db = DB::getInstance();
  if (!$db) throw new Exception('Sin conexión');

  $id_inmueble   = (int)($_POST['id_inmueble'] ?? 0);
  $id_condominio = (int)($_POST['id_condominio'] ?? ($_SESSION['id_condominio'] ?? 0));
  $direction     = $_POST['direction'] ?? ''; // 'up' | 'down'

  if ($id_condominio <= 0) throw new Exception('Condominio inválido');
  if ($id_inmueble  <= 0) throw new Exception('Inmueble inválido');
  if ($direction !== 'up' && $direction !== 'down') throw new Exception('Dirección inválida');

  $db->beginTransaction();

  // 1) Normaliza correlativo a 1..N dentro del condominio (evita NULL/huecos)
  $sqlReseq = "
    WITH ord AS (
      SELECT i.id_inmueble,
             ROW_NUMBER() OVER (
               PARTITION BY i.id_condominio
               ORDER BY i.correlativo NULLS LAST, i.id_inmueble
             ) AS rn
      FROM public.inmueble i
      WHERE i.id_condominio = :cid
    )
    UPDATE public.inmueble i
       SET correlativo = o.rn
      FROM ord o
     WHERE i.id_inmueble = o.id_inmueble
  ";
  $st = $db->prepare($sqlReseq);
  $st->bindValue(':cid', $id_condominio, PDO::PARAM_INT);
  $st->execute();

  // 2) Posición actual
  $st = $db->prepare("
    SELECT correlativo AS rn
      FROM public.inmueble
     WHERE id_condominio = :cid AND id_inmueble = :id
     LIMIT 1
  ");
  $st->bindValue(':cid', $id_condominio, PDO::PARAM_INT);
  $st->bindValue(':id',  $id_inmueble,   PDO::PARAM_INT);
  $st->execute();
  $cur = $st->fetch(PDO::FETCH_ASSOC);
  if (!$cur) throw new Exception('Inmueble no encontrado en el condominio');

  $rn_cur = (int)$cur['rn'];
  $rn_adj = ($direction === 'up') ? $rn_cur - 1 : $rn_cur + 1;

  // 3) Vecino
  $st = $db->prepare("
    SELECT id_inmueble
      FROM public.inmueble
     WHERE id_condominio = :cid AND correlativo = :adj
     LIMIT 1
  ");
  $st->bindValue(':cid', $id_condominio, PDO::PARAM_INT);
  $st->bindValue(':adj', $rn_adj,        PDO::PARAM_INT);
  $st->execute();
  $adj = $st->fetch(PDO::FETCH_ASSOC);

  if (!$adj) {
    $db->rollBack();
    echo json_encode(['status'=>'ok','message'=>'Límite alcanzado; no hay registro adyacente.']);
    exit;
  }

  $id_adj = (int)$adj['id_inmueble'];

  // 4) SWAP en 3 pasos con staging negativo (dentro de SMALLINT)
  // Paso A: mover el actual a valor negativo exclusivo
  $stA = $db->prepare("
    UPDATE public.inmueble
       SET correlativo = :tmp
     WHERE id_condominio = :cid AND id_inmueble = :id_cur
  ");
  $stA->bindValue(':tmp', -$rn_cur,        PDO::PARAM_INT);
  $stA->bindValue(':cid', $id_condominio,  PDO::PARAM_INT);
  $stA->bindValue(':id_cur', $id_inmueble, PDO::PARAM_INT);
  $stA->execute();

  // Paso B: poner el vecino en la posición del actual
  $stB = $db->prepare("
    UPDATE public.inmueble
       SET correlativo = :rn_cur
     WHERE id_condominio = :cid AND id_inmueble = :id_adj
  ");
  $stB->bindValue(':rn_cur', $rn_cur,        PDO::PARAM_INT);
  $stB->bindValue(':cid',    $id_condominio, PDO::PARAM_INT);
  $stB->bindValue(':id_adj', $id_adj,        PDO::PARAM_INT);
  $stB->execute();

  // Paso C: traer el actual (negativo) a la posición del vecino
  $stC = $db->prepare("
    UPDATE public.inmueble
       SET correlativo = :rn_adj
     WHERE id_condominio = :cid AND id_inmueble = :id_cur
  ");
  $stC->bindValue(':rn_adj', $rn_adj,        PDO::PARAM_INT);
  $stC->bindValue(':cid',    $id_condominio, PDO::PARAM_INT);
  $stC->bindValue(':id_cur', $id_inmueble,   PDO::PARAM_INT);
  $stC->execute();

  $db->commit();
  echo json_encode(['status'=>'ok']);

} catch (Throwable $e) {
  if (isset($db) && method_exists($db,'inTransaction') && $db->inTransaction()) $db->rollBack();
  echo json_encode(['status'=>'error','message'=>'Error al reordenar: '.$e->getMessage()]);
}
