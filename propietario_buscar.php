<?php
// propietario_buscar.php — Busca propietario por (id_condominio, t_cedula, cedula)
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';

try {
  $db = DB::getInstance();
  if (!$db) throw new Exception('Sin conexión');

  $id_condominio = (int)($_POST['id_condominio'] ?? ($_SESSION['id_condominio'] ?? 0));
  $t_cedula      = strtoupper(trim($_POST['t_cedula'] ?? ''));
  $cedula        = trim($_POST['cedula'] ?? '');

  if ($id_condominio <= 0) throw new Exception('Condominio inválido');
  if ($t_cedula === '' || $cedula === '') {
    echo json_encode(['status'=>'error','message'=>'Datos incompletos']); exit;
  }

  $sql = "SELECT p.id_propietario, p.nombre1, p.nombre2, p.apellido1, p.apellido2,
                 p.t_cedula, p.cedula, p.t_rif, p.rif, p.celular, p.tratamiento,
                 p.fecha_registro, p.verificado, p.id_condominio,
                 u.correo
          FROM public.propietario p
          LEFT JOIN menu_login.usuario u
                 ON u.id_usuario = p.id_usuario
          WHERE p.id_condominio = :c
            AND p.t_cedula = :t
            AND p.cedula::text = :ced";
  $st = $db->prepare($sql);
  $st->bindValue(':c', $id_condominio, PDO::PARAM_INT);
  $st->bindValue(':t', $t_cedula, PDO::PARAM_STR);
  $st->bindValue(':ced', (string)$cedula, PDO::PARAM_STR);
  $st->execute();
  $r = $st->fetch(PDO::FETCH_ASSOC);

  if (!$r) {
    echo json_encode(['status'=>'not_found']); exit;
  }

  echo json_encode(['status'=>'ok','data'=>$r]);
} catch (Throwable $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
