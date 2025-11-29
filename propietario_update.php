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

  // crear nuevo
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
  return (int)$st->fetchColumn();
}

try {
  $conn = DB::getInstance(); if(!$conn) throw new Exception('Sin conexión');

  $id_condominio  = (int)($_POST['id_condominio'] ?? ($_SESSION['id_condominio'] ?? 0));
  $id_propietario = (int)($_POST['id_propietario'] ?? 0);
  if ($id_condominio<=0 || $id_propietario<=0) throw new Exception('Parámetros inválidos');

  // Verifica pertenencia
  $st = $conn->prepare("SELECT 1 FROM public.propietario WHERE id_propietario=:id AND id_condominio=:c");
  $st->execute([':id'=>$id_propietario, ':c'=>$id_condominio]);
  if (!$st->fetch()) throw new Exception('Registro no pertenece a este condominio');

  // Datos propietario
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

  // verificado robusto -> 't'/'f'
  $verificado_db = (in_array(($_POST['verificado'] ?? '0'), ['1','t','true',1,true], true) ? 't' : 'f');

  // Fecha registro
  $fecha_registro = trim($_POST['fecha_registro'] ?? '');
  if ($fecha_registro !== '') $fecha_registro = str_replace('T',' ', $fecha_registro);

  // Unicidad cédula (otro propietario)
  $st = $conn->prepare("SELECT 1 FROM public.propietario
                        WHERE id_condominio=:c AND t_cedula=:tc AND cedula=:ce
                          AND id_propietario<>:id LIMIT 1");
  $st->execute([':c'=>$id_condominio, ':tc'=>$t_cedula, ':ce'=>$cedula, ':id'=>$id_propietario]);
  if ($st->fetch()) throw new Exception('Ya existe otro propietario con esa cédula en este condominio');

  // Unicidad RIF (si viene)
  if ($t_rif!=='' && $rif!=='') {
    $st = $conn->prepare("SELECT 1 FROM public.propietario
                          WHERE id_condominio=:c AND t_rif=:tr AND rif=:ri
                            AND id_propietario<>:id LIMIT 1");
    $st->execute([':c'=>$id_condominio, ':tr'=>$t_rif, ':ri'=>$rif, ':id'=>$id_propietario]);
    if ($st->fetch()) throw new Exception('Ya existe otro propietario con ese RIF en este condominio');
  }

  $conn->beginTransaction();

  // Usuario (crear o reutilizar por correo)
  $id_usuario = getOrCreateUsuarioId($conn, [
    'correo'   => $correo,
    'cedula'   => $cedula,
    'nombre'   => trim($nombre1.' '.$nombre2),
    'apellido' => trim($apellido1.' '.$apellido2),
    'telefono' => $celular
  ]);

  // Update propietario
  $sql = "UPDATE public.propietario SET
            id_usuario = :idu,
            nombre1 = :n1, nombre2 = :n2, apellido1 = :a1, apellido2 = :a2,
            t_cedula = :tc, cedula = :ce, t_rif = :tr, rif = :ri, celular = :cel,
            tratamiento = :trat, verificado = :ver::boolean,
            fecha_registro = COALESCE(NULLIF(:fr,'')::timestamp, fecha_registro)
          WHERE id_propietario = :id AND id_condominio = :c";
  $st = $conn->prepare($sql);
  $st->execute([
    ':idu'=>$id_usuario,
    ':n1'=>$nombre1, ':n2'=>$nombre2, ':a1'=>$apellido1, ':a2'=>$apellido2,
    ':tc'=>$t_cedula, ':ce'=>$cedula, ':tr'=>$t_rif, ':ri'=>$rif, ':cel'=>$celular,
    ':trat'=>$trat, ':ver'=>$verificado_db, ':fr'=>$fecha_registro,
    ':id'=>$id_propietario, ':c'=>$id_condominio
  ]);

  $conn->commit();
  echo json_encode(['status'=>'ok','message'=>'Propietario actualizado y usuario vinculado','id_usuario'=>$id_usuario]);

} catch (Throwable $e) {
  try { if ($conn && method_exists($conn,'inTransaction') && $conn->inTransaction()) $conn->rollBack(); } catch(Throwable $_) {}
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
