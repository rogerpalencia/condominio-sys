<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';
try{
  $conn=DB::getInstance(); if(!$conn) throw new Exception('Sin conexión');
  $id_propietario=(int)($_POST['id_propietario']??0);
  $id_condominio=(int)($_POST['id_condominio']??($_SESSION['id_condominio']??0));
  if($id_propietario<=0||$id_condominio<=0) throw new Exception('Parámetros inválidos');

  $sql="SELECT p.id_propietario, p.nombre1, p.nombre2, p.apellido1, p.apellido2,
               p.t_cedula, p.cedula, p.t_rif, p.rif, p.celular, p.tratamiento,
               p.verificado, to_char(p.fecha_registro,'YYYY-MM-DD HH24:MI') AS fecha_registro,
               u.correo
        FROM public.propietario p
        LEFT JOIN menu_login.usuario u ON u.id_usuario=p.id_usuario
        WHERE p.id_propietario=:id AND p.id_condominio=:idc
        LIMIT 1";
  $st=$conn->prepare($sql); $st->execute([':id'=>$id_propietario, ':idc'=>$id_condominio]);
  $r=$st->fetch(PDO::FETCH_ASSOC); if(!$r) throw new Exception('No encontrado');
  echo json_encode(['status'=>'ok','data'=>$r]);
}catch(Throwable $e){ echo json_encode(['status'=>'error','message'=>$e->getMessage()]); }
