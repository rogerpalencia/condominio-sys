<?php
// guardar_config_condominio.php — Update datos generales de condominio
@session_start();
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
  $id = (int)($_POST['id_condominio'] ?? 0);
  if ($id<=0) throw new Exception('ID inválido');

  $nombre    = $_POST['nombre']    ?? '';
  $direccion = $_POST['direccion'] ?? '';
  $linea_1   = $_POST['linea_1']   ?? '';
  $linea_2   = $_POST['linea_2']   ?? '';
  $linea_3   = $_POST['linea_3']   ?? '';
  $id_moneda = (int)($_POST['id_moneda'] ?? 0);

  $conn = DB::getInstance();

  // Manejo de logos (opcional)
  $logo_izq = null; $logo_der = null;
  $uploadDir = 'assets/images/';
  if (!empty($_FILES['logo_izquierdo']['name'])) {
    $ext = pathinfo($_FILES['logo_izquierdo']['name'], PATHINFO_EXTENSION) ?: 'png';
    $fname = "logo_izq_{$id}_" . time() . "." . strtolower($ext);
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
    if (!move_uploaded_file($_FILES['logo_izquierdo']['tmp_name'], $uploadDir.$fname)) {
      throw new Exception('No se pudo guardar el logo izquierdo.');
    }
    $logo_izq = $uploadDir.$fname;
  }
  if (!empty($_FILES['logo_derecho']['name'])) {
    $ext = pathinfo($_FILES['logo_derecho']['name'], PATHINFO_EXTENSION) ?: 'png';
    $fname = "logo_der_{$id}_" . time() . "." . strtolower($ext);
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
    if (!move_uploaded_file($_FILES['logo_derecho']['tmp_name'], $uploadDir.$fname)) {
      throw new Exception('No se pudo guardar el logo derecho.');
    }
    $logo_der = $uploadDir.$fname;
  }

  $sql = "UPDATE public.condominio
             SET nombre=:n, direccion=:d,
                 linea_1=:l1, linea_2=:l2, linea_3=:l3,
                 id_moneda=:mon, fecha_actualizacion=now()";

  if ($logo_izq) $sql .= ", url_logo_izquierda=:li";
  if ($logo_der) $sql .= ", url_logo_derecha=:ld";
  $sql .= " WHERE id_condominio=:id";

  $st = $conn->prepare($sql);
  $st->bindValue(':n',$nombre);
  $st->bindValue(':d',$direccion);
  $st->bindValue(':l1',$linea_1);
  $st->bindValue(':l2',$linea_2);
  $st->bindValue(':l3',$linea_3);
  $st->bindValue(':mon',$id_moneda, PDO::PARAM_INT);
  if ($logo_izq) $st->bindValue(':li',$logo_izq);
  if ($logo_der) $st->bindValue(':ld',$logo_der);
  $st->bindValue(':id',$id, PDO::PARAM_INT);
  $st->execute();

  echo json_encode(['status'=>'ok']);
} catch (Throwable $e){
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
