<?php
/**
 * email/enviar_email_master_broadcast.php
 * -----------------------------------------------------------
 * Envío masivo directo (sin cola) para:
 *  - master_relacion: un email por destinatario con link a la master
 *  - master_presupuesto: notificaciones hijas por destinatario (nc)
 *
 * Reutiliza librería probada: lib_email.php (compose_email + send_via_smtp)
 * Acepta emails por: array (emails[]), string (separadores), JSON body,
 * o emails_multi[] (FormData en bucle).
 * Siempre responde 200 + JSON para no romper el front.
 * -----------------------------------------------------------
 */

@session_start();
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', '0');
@error_reporting(E_ALL);

function respond_json(array $arr) {
  http_response_code(200);
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $root = dirname(__DIR__); // /sys/email -> /sys
  require_once $root . '/core/config.php';
  require_once $root . '/core/PDO.class.php';
  require_once __DIR__ . '/lib_email.php'; // compose_email + send_via_smtp

  /* ============================================================
     1) PARÁMETROS BÁSICOS (POST o JSON)
     ============================================================ */
  $tipo          = isset($_POST['tipo']) ? trim($_POST['tipo']) : '';
  $id_master     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $id_condominio = isset($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;

  if ((!$tipo || !$id_master || !$id_condominio)
      && !empty($_SERVER['CONTENT_TYPE'])
      && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $body = file_get_contents('php://input');
    if ($body) {
      $j = json_decode($body, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($j)) {
        if (!$tipo && !empty($j['tipo']))              $tipo = trim((string)$j['tipo']);
        if (!$id_master && !empty($j['id']))           $id_master = (int)$j['id'];
        if (!$id_condominio && !empty($j['id_condominio'])) $id_condominio = (int)$j['id_condominio'];
      }
    }
  }

  if (!in_array($tipo, ['master_relacion','master_presupuesto'], true) || $id_master<=0 || $id_condominio<=0) {
    respond_json(['status'=>'error','message'=>'Parámetros inválidos (tipo/id/id_condominio).']);
  }

  $conn = DB::getInstance();

  /* ============================================================
     2) SMTP CONFIG DESDE email.config (método probado)
     ============================================================ */
  $stS = $conn->prepare('
    SELECT host, puerto, usuario, contrasena_enc, seguridad,
           from_email, from_name, reply_to_email, reply_to_name
      FROM "email"."config"
     WHERE id_condominio = :c AND activo = TRUE
     ORDER BY updated_at DESC NULLS LAST, id_email_config DESC
     LIMIT 1
  ');
  $stS->execute([':c'=>$id_condominio]);
  $smtp = $stS->fetch(PDO::FETCH_ASSOC) ?: [];
  if (empty($smtp['host']) || empty($smtp['usuario'])) {
    respond_json(['status'=>'error','message'=>'No hay configuración SMTP activa en "email"."config".']);
  }

  /* ============================================================
     3) PARSING ROBUSTO DE emails (array, string, JSON, emails_multi[])
     ============================================================ */
  $emailsSeleccion = [];

  // JSON body (cuando no hay POST clásico)
  if (empty($_POST) && !empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $body = file_get_contents('php://input');
    if ($body !== false && $body !== '') {
      $json = json_decode($body, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        if (isset($json['emails']) && is_array($json['emails'])) {
          $emailsSeleccion = $json['emails'];
        } elseif (isset($json['emails']) && is_string($json['emails'])) {
          $emailsSeleccion = preg_split('/[,\s;]+/u', trim($json['emails']), -1, PREG_SPLIT_NO_EMPTY);
        }
      }
    }
  }

  // emails[] (array) o emails: [..]
  if (isset($_POST['emails']) && is_array($_POST['emails'])) {
    $emailsSeleccion = array_merge($emailsSeleccion, $_POST['emails']);
  }

  // emails como string (posible JSON embebido o separadores)
  if (isset($_POST['emails']) && is_string($_POST['emails'])) {
    $str = trim($_POST['emails']);
    if ($str !== '') {
      if ($str[0] === '[' || $str[0] === '{') {
        $decoded = json_decode($str, true);
        if (json_last_error() === JSON_ERROR_NONE) {
          if (is_array($decoded)) {
            if (isset($decoded['emails']) && is_array($decoded['emails'])) {
              $emailsSeleccion = array_merge($emailsSeleccion, $decoded['emails']);
            } else {
              $emailsSeleccion = array_merge($emailsSeleccion, $decoded);
            }
          }
        } else {
          $emailsSeleccion = array_merge($emailsSeleccion, preg_split('/[,\s;]+/u', $str, -1, PREG_SPLIT_NO_EMPTY));
        }
      } else {
        $emailsSeleccion = array_merge($emailsSeleccion, preg_split('/[,\s;]+/u', $str, -1, PREG_SPLIT_NO_EMPTY));
      }
    }
  }

  // FormData en bucle -> emails_multi[]
  if (isset($_POST['emails_multi']) && is_array($_POST['emails_multi'])) {
    $emailsSeleccion = array_merge($emailsSeleccion, $_POST['emails_multi']);
  }

  // Normalizar + validar + único
  $emailsSeleccion = array_values(array_unique(array_filter(
    array_map('trim', $emailsSeleccion),
    static function ($e) { return filter_var($e, FILTER_VALIDATE_EMAIL); }
  )));

  /* ============================================================
     4) DESTINATARIOS: seleccionados o TODOS del condominio
     ============================================================ */
  $destinatarios = [];
  if ($emailsSeleccion) {
    foreach ($emailsSeleccion as $e) {
      $destinatarios[] = ['email'=>$e, 'nombre1'=>'', 'apellido1'=>''];
    }
  } else {
    $qDest = "
      SELECT DISTINCT
        u.correo, p.nombre1, p.nombre2, p.apellido1, p.apellido2
      FROM public.inmueble i
      JOIN public.propietario_inmueble pi ON pi.id_inmueble = i.id_inmueble
      JOIN public.propietario p           ON p.id_propietario = pi.id_propietario
      JOIN menu_login.usuario u           ON u.id_usuario = pi.id_usuario
      WHERE i.id_condominio = :c
        AND u.correo IS NOT NULL
        AND TRIM(u.correo) <> ''
      ORDER BY u.correo
    ";
    $st = $conn->prepare($qDest);
    $st->execute([':c'=>$id_condominio]);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $e = trim($r['correo']);
      if (!filter_var($e, FILTER_VALIDATE_EMAIL)) continue;
      $destinatarios[] = [
        'email'     => $e,
        'nombre1'   => $r['nombre1'] ?? '',
        'apellido1' => $r['apellido1'] ?? '',
      ];
    }
  }

  if (!$destinatarios) {
    respond_json(['status'=>'ok','message'=>'No hay destinatarios con correo válido.','enviados'=>0,'fallidos'=>0]);
  }

  /* ============================================================
     5) ENVÍO: usa compose_email() + send_via_smtp() (probados)
     ============================================================ */
  $ok=0; $bad=0; $errores=[];

  if ($tipo === 'master_relacion') {
    // Un correo por destinatario con link a la master
    foreach ($destinatarios as $d) {
      $to_name_override = trim(($d['nombre1'] ?? '').' '.($d['apellido1'] ?? '')) ?: null;

      $emailPayload = compose_email(
        $conn,
        $id_condominio,
        'master_relacion',
        $id_master,          // id de la master
        '',                  // token no aplica
        $d['email'],         // destino explícito
        $to_name_override
      );

      $r = send_via_smtp($smtp, $emailPayload);
      if (!empty($r['ok'])) { $ok++; }
      else { $bad++; $errores[] = ['email'=>$d['email'], 'error'=>($r['error'] ?? $r['exception'] ?? 'fallo desconocido')]; }

      usleep(250000); // 0.25 s anti rate-limit
    }

  } else { // master_presupuesto
    // Enviar notificaciones hijas por destinatario
    $qH = "
      SELECT nc.id_notificacion, nc.token, u.correo
        FROM public.notificacion_cobro nc
        JOIN public.inmueble i              ON nc.id_inmueble = i.id_inmueble
        JOIN public.propietario_inmueble pi ON pi.id_inmueble = i.id_inmueble
        JOIN menu_login.usuario u           ON u.id_usuario = pi.id_usuario
       WHERE nc.id_notificacion_master = :idm
         AND i.id_condominio = :c
         AND u.correo IS NOT NULL
         AND TRIM(u.correo) <> ''
       ORDER BY nc.id_notificacion
    ";
    $stH = $conn->prepare($qH);
    $stH->execute([':idm'=>$id_master, ':c'=>$id_condominio]);

    $hijasPorCorreo = [];
    while ($row = $stH->fetch(PDO::FETCH_ASSOC)) {
      $e = trim($row['correo']);
      if (!filter_var($e, FILTER_VALIDATE_EMAIL)) continue;
      $hijasPorCorreo[$e][] = ['id'=>(int)$row['id_notificacion'], 'token'=>(string)$row['token']];
    }

    if (!$hijasPorCorreo) {
      respond_json(['status'=>'error','message'=>'No existen notificaciones hijas para esta master (presupuesto).']);
    }

    foreach ($destinatarios as $d) {
      $email = $d['email'];
      if (empty($hijasPorCorreo[$email])) {
        $bad++; $errores[] = ['email'=>$email, 'error'=>'Propietario sin notificación hija asociada.'];
        continue;
      }

      foreach ($hijasPorCorreo[$email] as $h) {
        $emailPayload = compose_email(
          $conn,
          $id_condominio,
          'notificacion',     // hija individual
          $h['id'],
          $h['token'],
          $email,
          null
        );
        $r = send_via_smtp($smtp, $emailPayload);
        if (!empty($r['ok'])) { $ok++; }
        else { $bad++; $errores[] = ['email'=>$email, 'error'=>($r['error'] ?? $r['exception'] ?? 'fallo desconocido')]; }

        usleep(250000); // 0.25 s
      }
    }
  }

  /* ============================================================
     6) RESPUESTA
     ============================================================ */
  respond_json(['status'=>'ok','enviados'=>$ok,'fallidos'=>$bad,'errores'=>$errores]);

} catch (Throwable $e) {
  error_log('[broadcast][fatal] '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
  respond_json(['status'=>'error','message'=>'Error interno al enviar.']);
}
