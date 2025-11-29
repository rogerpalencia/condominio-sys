<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';

try{
  $conn=DB::getInstance();
  if(!$conn || !$conn->getAttribute(PDO::ATTR_CONNECTION_STATUS)) throw new Exception('Sin conexión a BD');

  $id_condominio = 0;
  if(isset($_POST['id_condominio']) && $_POST['id_condominio']!=='') $id_condominio=(int)$_POST['id_condominio'];
  elseif(isset($_SESSION['id_condominio']))                          $id_condominio=(int)$_SESSION['id_condominio'];
  elseif(isset($_GET['id_condominio']))                              $id_condominio=(int)$_GET['id_condominio'];
  if($id_condominio<=0) throw new Exception('ID de condominio inválido');

  $draw=(int)($_POST['draw']??0); $start=(int)($_POST['start']??0); $length=(int)($_POST['length']??10);
  $search=trim($_POST['search']['value']??'');

  $sql_base = "SELECT p.id_propietario, p.nombre1, p.nombre2, p.apellido1, p.apellido2,
                      p.t_cedula, p.cedula, p.t_rif, p.rif, p.celular, p.tratamiento,
                      p.verificado, to_char(p.fecha_registro,'YYYY-MM-DD HH24:MI') AS fecha_registro,
                      u.correo
               FROM public.propietario p
               LEFT JOIN menu_login.usuario u ON u.id_usuario = p.id_usuario
               WHERE p.id_condominio = :idc";

  $params = [':idc'=>$id_condominio];

  $sql_list = $sql_base;
  if($search!==''){
    $sql_list .= " AND (
      p.nombre1 ILIKE :s OR p.nombre2 ILIKE :s OR p.apellido1 ILIKE :s OR p.apellido2 ILIKE :s OR
      u.correo ILIKE :s OR (p.t_cedula||'-'||p.cedula::text) ILIKE :s OR (p.t_rif||'-'||p.rif) ILIKE :s OR
      p.celular ILIKE :s
    )";
    $params[':s'] = "%{$search}%";
  }

  $orders=[]; $map=[
    0=>'p.id_propietario', 1=>'p.nombre1', 2=>'p.apellido1', 3=>'u.correo', 4=>'p.cedula',
    5=>'p.rif', 6=>'p.celular', 7=>'p.verificado', 8=>'p.fecha_registro'
  ];
  if(!empty($_POST['order'])){
    foreach($_POST['order'] as $ord){ $idx=(int)$ord['column']; $dir=(strtolower($ord['dir'])==='asc'?'ASC':'DESC'); if(isset($map[$idx])) $orders[]=$map[$idx].' '.$dir; }
  }
  $order_clause = $orders? implode(', ',$orders) : 'p.apellido1 ASC, p.nombre1 ASC';

  $stt=$conn->prepare("SELECT COUNT(*) FROM public.propietario p WHERE p.id_condominio=:idc"); $stt->execute([':idc'=>$id_condominio]);
  $recordsTotal=(int)$stt->fetchColumn();

  $stf=$conn->prepare("SELECT COUNT(*) FROM ($sql_list) x"); foreach($params as $k=>$v)$stf->bindValue($k,$v); $stf->execute();
  $recordsFiltered=(int)$stf->fetchColumn();

  $sql_final=$sql_list." ORDER BY $order_clause LIMIT :len OFFSET :off";
  $st=$conn->prepare($sql_final); foreach($params as $k=>$v)$st->bindValue($k,$v);
  $st->bindValue(':len',$length,PDO::PARAM_INT); $st->bindValue(':off',$start,PDO::PARAM_INT); $st->execute();
  $data=$st->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['draw'=>$draw,'recordsTotal'=>$recordsTotal,'recordsFiltered'=>$recordsFiltered,'data'=>$data]);

}catch(Throwable $e){
  echo json_encode(['draw'=>(int)($_POST['draw']??0),'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[],'error'=>$e->getMessage()]);
}
