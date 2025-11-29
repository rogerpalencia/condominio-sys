<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';

function U($s){ return mb_strtoupper(trim((string)$s),'UTF-8'); }
function L($s){ return mb_strtolower(trim((string)$s),'UTF-8'); }

function getOrCreateUsuarioId($db, array $u): int {
  // ¿ya existe por correo?
  $st = $db->prepare("SELECT id_usuario FROM menu_login.usuario WHERE correo=:e LIMIT 1");
  $st->execute([':e'=>$u['correo']]);
  if ($id = $st->fetchColumn()) return (int)$id;

  $sql = "INSERT INTO menu_login.usuario
            (correo, contrasena, nombre, apellido, estado, telefono)
          VALUES
            (:correo, :contrasena, :nombre, :apellido, TRUE, NULLIF(:telefono,''))
          RETURNING id_usuario";
  $st = $db->prepare($sql);
  $st->execute([
    ':correo'     => $u['correo'],
    ':contrasena' => sha1((string)$u['cedula']),
    ':nombre'     => $u['nombre'],
    ':apellido'   => $u['apellido'],
    ':telefono'   => $u['telefono'] ?? ''
  ]);
  $idUsuario = $st->fetchColumn();
  if(!$idUsuario) throw new Exception('No se obtuvo id_usuario');
  return (int)$idUsuario;
}

try {
  $conn = DB::getInstance(); if (!$conn) throw new Exception('Sin conexión');

  $id_condominio = (int)($_POST['id_condominio'] ?? ($_SESSION['id_condominio'] ?? 0));
  if ($id_condominio <= 0) throw new Exception('Condominio inválido');

  $nombre1   = U($_POST['nombre1'] ?? ''); if ($nombre1==='') throw new Exception('Nombre1 requerido');
  $nombre2   = U($_POST['nombre2'] ?? '');
  $apellido1 = U($_POST['apellido1'] ?? ''); if ($apellido1==='') throw new Exception('Apellido1 requerido');
  $apellido2 = U($_POST['apellido2'] ?? '');
  $correo    = L($_POST['correo_usuario'] ?? ''); if ($correo==='') throw new Exception('Correo requerido');
  $t_cedula  = U($_POST['t_cedula'] ?? ''); if ($t_cedula==='') throw new Exception('T. cédula requerido');
  $cedula    = (int)($_POST['cedula'] ?? 0); if ($cedula<=0) throw new Exception('Cédula inválida');
  $t_rif     = U($_POST['t_rif'] ?? '');
  $rif       = U($_POST['rif'] ?? '');
  $celular   = trim($_POST['celular'] ?? '');
  $trat      = U($_POST['tratamiento'] ?? '');

  $verificado_db = (in_array(($_POST['verificado'] ?? '0'), ['1','t','true',1,true], true) ? 't' : 'f');

  $fecha_registro = trim($_POST['fecha_registro'] ?? '');
  if ($fecha_registro !== '') $fecha_registro = str_replace('T',' ', $fecha_registro);

  // ¿Se pidió asociar automáticamente al inmueble?
  $link_inmueble = (int)($_POST['link_inmueble'] ?? 0);

  // ¿Ya existe por cédula en este condominio?
  $sqlDup = "SELECT p.id_propietario
             FROM public.propietario p
             WHERE p.id_condominio=:c AND p.t_cedula=:tc AND p.cedula=:ce
             LIMIT 1";
  $st = $conn->prepare($sqlDup);
  $st->execute([':c'=>$id_condominio, ':tc'=>$t_cedula, ':ce'=>$cedula]);
  if ($dup = $st->fetch(PDO::FETCH_ASSOC)) {
    // Mantener la decisión del usuario: si existe, NO asociamos automáticamente.
    echo json_encode([
      'status'=>'exists',
      'message'=>'Ya existe un propietario con esa cédula en este condominio',
      'id_propietario'=>(int)$dup['id_propietario']
    ]);
    exit;
  }

  $conn->beginTransaction();

  // Crear / vincular usuario
  $id_usuario = getOrCreateUsuarioId($conn, [
    'correo'   => $correo,
    'cedula'   => $cedula,
    'nombre'   => trim($nombre1.' '.$nombre2),
    'apellido' => trim($apellido1.' '.$apellido2),
    'telefono' => $celular
  ]);

  // Insert propietario
  $sql = "INSERT INTO public.propietario
            (id_condominio, id_usuario, nombre1, nombre2, apellido1, apellido2,
             t_cedula, cedula, t_rif, rif, celular, fecha_registro, tratamiento, verificado)
          VALUES
            (:idc, :idu, :n1, :n2, :a1, :a2, :tc, :ce, :tr, :ri, :cel,
             COALESCE(NULLIF(:fr,'')::timestamp, NOW()), :trat, :ver::boolean)
          RETURNING id_propietario";
  $st = $conn->prepare($sql);
  $st->execute([
    ':idc'=>$id_condominio, ':idu'=>$id_usuario,
    ':n1'=>$nombre1, ':n2'=>$nombre2, ':a1'=>$apellido1, ':a2'=>$apellido2,
    ':tc'=>$t_cedula, ':ce'=>$cedula, ':tr'=>$t_rif, ':ri'=>$rif, ':cel'=>$celular,
    ':fr'=>$fecha_registro, ':trat'=>$trat, ':ver'=>$verificado_db
  ]);
  $id_propietario = (int)$st->fetchColumn();

  $linked = false;
  // Auto-asociar si viene link_inmueble
  if ($link_inmueble > 0) {
    // Validar que el inmueble pertenezca al mismo condominio
    $st = $conn->prepare("SELECT 1 FROM public.inmueble WHERE id_inmueble=:i AND id_condominio=:c");
    $st->execute([':i'=>$link_inmueble, ':c'=>$id_condominio]);
    if (!$st->fetch()) throw new Exception('El inmueble indicado no pertenece al condominio');

    // Upsert propietario_inmueble
    $st = $conn->prepare("SELECT 1 FROM public.propietario_inmueble WHERE id_inmueble=:i LIMIT 1");
    $st->execute([':i'=>$link_inmueble]);
    $exists = (bool)$st->fetch();

    if ($exists) {
      $sql_up = "UPDATE public.propietario_inmueble
                 SET id_propietario=:p, id_usuario=:u
                 WHERE id_inmueble=:i";
    } else {
      $sql_up = "INSERT INTO public.propietario_inmueble (id_inmueble, id_propietario, id_usuario)
                 VALUES (:i, :p, :u)";
    }
    $st = $conn->prepare($sql_up);
    $st->execute([':i'=>$link_inmueble, ':p'=>$id_propietario, ':u'=>$id_usuario]);
    $linked = true;
  }

  $conn->commit();

  echo json_encode([
    'status'=>'ok',
    'message'=> $linked ? 'Propietario creado y asociado al inmueble' : 'Propietario creado y usuario vinculado',
    'id_propietario'=>$id_propietario,
    'id_usuario'=>$id_usuario,
    'linked'=>$linked,
    'id_inmueble'=>$linked ? $link_inmueble : null
  ]);

} catch (Throwable $e) {
  if ($conn && method_exists($conn,'inTransaction') && $conn->inTransaction()) $conn->rollBack();
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
