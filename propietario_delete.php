<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';
try{
  $conn=DB::getInstance(); if(!$conn) throw new Exception('Sin conexión');
  $id_condominio=(int)($_POST['id_condominio']??($_SESSION['id_condominio']??0));
  $id_propietario=(int)($_POST['id_propietario']??0);
  if($id_condominio<=0 || $id_propietario<=0) throw new Exception('Parámetros inválidos');

  // Impedir borrar si tiene asociaciones
  $st=$conn->prepare("SELECT 1 FROM public.propietario_inmueble WHERE id_propietario=:id LIMIT 1");
  $st->execute([':id'=>$id_propietario]);
  if($st->fetch()) throw new Exception('No se puede eliminar: tiene inmuebles asociados');

  $st=$conn->prepare("DELETE FROM public.propietario WHERE id_propietario=:id AND id_condominio=:c");
  $st->execute([':id'=>$id_propietario, ':c'=>$id_condominio]);

  echo json_encode(['status'=>'ok','message'=>'Propietario eliminado']);
}catch(Throwable $e){
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
