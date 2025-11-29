<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';

try {
  $conn = DB::getInstance(); if(!$conn) throw new Exception('Sin conexión');

  // id_condominio: POST > SESSION
  $id_condominio = (int)($_POST['id_condominio'] ?? ($_SESSION['id_condominio'] ?? 0));
  $id_inmueble   = (int)($_POST['id_inmueble'] ?? 0);
  $id_prop       = (int)($_POST['id_propietario'] ?? 0);

  if ($id_condominio<=0 || $id_inmueble<=0 || $id_prop<=0) {
    throw new Exception('Parámetros incompletos');
  }

  // Verifica inmueble pertenece al condominio
  $st = $conn->prepare("SELECT 1 FROM public.inmueble WHERE id_inmueble=:i AND id_condominio=:c");
  $st->execute([':i'=>$id_inmueble, ':c'=>$id_condominio]);
  if (!$st->fetch()) throw new Exception('El inmueble no pertenece al condominio');

  // Verifica propietario pertenece al condominio y obtiene id_usuario
  $st = $conn->prepare("SELECT id_usuario, nombre1,nombre2,apellido1,apellido2 FROM public.propietario WHERE id_propietario=:p AND id_condominio=:c");
  $st->execute([':p'=>$id_prop, ':c'=>$id_condominio]);
  $rowP = $st->fetch(PDO::FETCH_ASSOC);
  if (!$rowP) throw new Exception('El propietario no pertenece a este condominio');

  $id_usuario = (int)($rowP['id_usuario'] ?? 0);
  if ($id_usuario<=0) $id_usuario = $id_prop; // fallback histórico

  // ¿Ya tiene asociación?
  $st = $conn->prepare("SELECT 1 FROM public.propietario_inmueble WHERE id_inmueble=:i LIMIT 1");
  $st->execute([':i'=>$id_inmueble]);
  $exists = (bool)$st->fetch();

  if ($exists) {
    $sql = "UPDATE public.propietario_inmueble
            SET id_propietario = :p, id_usuario = :u
            WHERE id_inmueble = :i";
  } else {
    $sql = "INSERT INTO public.propietario_inmueble (id_inmueble, id_propietario, id_usuario)
            VALUES (:i, :p, :u)";
  }
  $st = $conn->prepare($sql);
  $st->execute([':i'=>$id_inmueble, ':p'=>$id_prop, ':u'=>$id_usuario]);

  // Nombre de retorno (por comodidad en el front)
  $nombre = trim(
    preg_replace('/\s+/', ' ',
      ($rowP['nombre1'] ?? '').' '.($rowP['nombre2'] ?? '').' '.($rowP['apellido1'] ?? '').' '.($rowP['apellido2'] ?? '')
    )
  );

  echo json_encode(['status'=>'ok', 'message'=>'Asociación registrada', 'id_inmueble'=>$id_inmueble, 'id_propietario'=>$id_prop, 'id_usuario'=>$id_usuario, 'propietario_nombre'=>mb_strtoupper($nombre,'UTF-8')]);

} catch (Throwable $e) {
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
