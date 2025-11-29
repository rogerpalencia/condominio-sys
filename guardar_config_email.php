<?php
// guardar_config_email.php — Upsert en email.config (usa nombres reales de columnas)
@session_start();
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
  $id_condominio = (int)($_POST['id_condominio'] ?? 0);
  if ($id_condominio<=0) throw new Exception('ID de condominio inválido');

  // Campos obligatorios según esquema
  $host       = trim($_POST['host']       ?? '');
  $puerto     = (int)($_POST['puerto']    ?? 0);
  $seguridad  = strtolower(trim($_POST['seguridad'] ?? 'ninguna'));
  $usuario    = trim($_POST['usuario']    ?? '');
  $from_email = trim($_POST['from_email'] ?? '');
  $from_name  = trim($_POST['from_name']  ?? '');

  // Opcionales
  $reply_to_email = trim($_POST['reply_to_email'] ?? '');
  $reply_to_name  = trim($_POST['reply_to_name']  ?? '');
  $rate_limit     = (int)($_POST['rate_limit_por_min'] ?? 30);
  if ($rate_limit<=0 || $rate_limit>10000) $rate_limit=30;

  $activo   = !empty($_POST['activo']); // checkbox

  // Clave nueva (si viene)
  $clave_plana = $_POST['contrasena'] ?? '';
  $setNuevaClave = (trim($clave_plana) !== '');

  // Validaciones mínimas
  if ($host==='' || $puerto<=0 || $seguridad==='' || $usuario==='' || $from_email==='' || $from_name==='') {
    throw new Exception('Complete todos los campos obligatorios del email.');
  }
  // Normalizar seguridad a tres valores conocidos
  if (!in_array($seguridad, ['ninguna','ssl','tls'], true)) $seguridad='ninguna';

  $conn = DB::getInstance();
  $conn->beginTransaction();

  // Seleccionar la configuración MÁS RECIENTE de ese condominio
  $sqlSel = "SELECT id_email_config
             FROM email.config
             WHERE id_condominio=:c
             ORDER BY updated_at DESC, id_email_config DESC
             LIMIT 1";
  $st = $conn->prepare($sqlSel);
  $st->execute([':c'=>$id_condominio]);
  $id_email_config = $st->fetchColumn();

  if ($id_email_config) {
    // UPDATE (opcionalmente actualizamos contrasena_enc si usuario envió nueva)
    $sql = "UPDATE email.config
               SET host=:h, puerto=:p, seguridad=:s, usuario=:u,
                   from_email=:fe, from_name=:fn,
                   reply_to_email=:rte, reply_to_name=:rtn,
                   rate_limit_por_min=:rl, activo=:a,
                   updated_at=now()";
    if ($setNuevaClave) $sql .= ", contrasena_enc=:ce";
    $sql .= " WHERE id_email_config=:id";

    $st = $conn->prepare($sql);
    $st->bindValue(':h', $host);
    $st->bindValue(':p', $puerto, PDO::PARAM_INT);
    $st->bindValue(':s', $seguridad);
    $st->bindValue(':u', $usuario);
    $st->bindValue(':fe', $from_email);
    $st->bindValue(':fn', $from_name);
    $st->bindValue(':rte', $reply_to_email ?: null, $reply_to_email===''? PDO::PARAM_NULL : PDO::PARAM_STR);
    $st->bindValue(':rtn', $reply_to_name ?: null,  $reply_to_name===''?  PDO::PARAM_NULL : PDO::PARAM_STR);
    $st->bindValue(':rl', $rate_limit, PDO::PARAM_INT);
    $st->bindValue(':a',  $activo, PDO::PARAM_BOOL);
    if ($setNuevaClave) $st->bindValue(':ce', base64_encode($clave_plana)); // sin pgcrypto
    $st->bindValue(':id', $id_email_config, PDO::PARAM_INT);
    $st->execute();

  } else {
    // INSERT — contrasena_enc es NOT NULL => exigir clave
    if (!$setNuevaClave) throw new Exception('Debe indicar una clave SMTP (contrasena) para crear la configuración.');
    $sql = "INSERT INTO email.config
              (id_condominio, host, puerto, seguridad, usuario, contrasena_enc,
               from_email, from_name, reply_to_email, reply_to_name,
               rate_limit_por_min, activo, created_at, updated_at)
            VALUES
              (:c, :h, :p, :s, :u, :ce,
               :fe, :fn, :rte, :rtn,
               :rl, :a, now(), now())";
    $st = $conn->prepare($sql);
    $st->bindValue(':c',  $id_condominio, PDO::PARAM_INT);
    $st->bindValue(':h',  $host);
    $st->bindValue(':p',  $puerto, PDO::PARAM_INT);
    $st->bindValue(':s',  $seguridad);
    $st->bindValue(':u',  $usuario);
    $st->bindValue(':ce', base64_encode($clave_plana)); // sin pgcrypto
    $st->bindValue(':fe', $from_email);
    $st->bindValue(':fn', $from_name);
    $st->bindValue(':rte', $reply_to_email ?: null, $reply_to_email===''? PDO::PARAM_NULL : PDO::PARAM_STR);
    $st->bindValue(':rtn', $reply_to_name ?: null,  $reply_to_name===''?  PDO::PARAM_NULL : PDO::PARAM_STR);
    $st->bindValue(':rl', $rate_limit, PDO::PARAM_INT);
    $st->bindValue(':a',  $activo, PDO::PARAM_BOOL);
    $st->execute();
  }

  $conn->commit();
  echo json_encode(['status'=>'ok']);
} catch (Throwable $e) {
  if (!empty($conn) && $conn->inTransaction()) $conn->rollBack();
  echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
