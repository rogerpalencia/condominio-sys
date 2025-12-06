<?php
// email/lib_email.php — Librería de email (COMPOSICIÓN + ENVÍO). Sin endpoints ni ecos.

// Dependencias centrales (rutas absolutas y seguras)
require_once __DIR__ . "/../core/config.php";
require_once __DIR__ . "/../core/PDO.class.php";

// PHPMailer (rutas absolutas)
require_once __DIR__ . "/../assets/libs/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../assets/libs/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/../assets/libs/PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/* ============================================================
   Utilidades de entorno/URL
   ============================================================ */

/** Obtiene la BASE_URL del sistema de forma robusta, incluso en cron. */
if (!function_exists('rhodium_base_url')) {
    function rhodium_base_url(): string {
        if (defined('BASE_URL') && BASE_URL) {
            return rtrim(BASE_URL, "/") . "/";
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'rhodiumdev.com';
        // Ajusta si tu instalación no vive en /condominio/sys/
        return "https://{$host}/condominio/sys/";
    }
}

/**
 * Convierte una ruta (relativa o absoluta) a URL absoluta servible por email.
 * - Acepta http/https/cid tal cual.
 * - Si empieza por '/', se usa el ORIGEN (scheme+host) de rhodium_base_url().
 * - Si es relativa, se resuelve contra rhodium_base_url().
 */
if (!function_exists('absolutizar_logo')) {
    function absolutizar_logo(?string $ruta, ?string $base = null): ?string {
        if (!$ruta) return null;
        $ruta = trim($ruta);

        // Ya absoluta o CID (embebido)
        if (stripos($ruta, 'http://') === 0 || stripos($ruta, 'https://') === 0 || stripos($ruta, 'cid:') === 0) {
            return $ruta;
        }

        // Base por defecto: la del sistema
        $base = $base ?: rhodium_base_url(); // p.ej. https://host/condominio/sys/
        $base = rtrim($base, '/') . '/';

        // Origen (scheme + host) derivado de la base
        $p = parse_url($base);
        $origin = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? ($_SERVER['HTTP_HOST'] ?? 'rhodiumdev.com'));

        // Ruta absoluta desde raíz del host
        if (isset($ruta[0]) && $ruta[0] === '/') {
            return rtrim($origin, '/') . $ruta; // respeta /condominio/... si ya viene así
        }

        // Ruta relativa -> resuelve contra base del sistema
        return $base . ltrim($ruta, '/');
    }
}

/* ============================================================
   Normalización de tipos
   ============================================================ */
/** Devuelve uno de: notificacion | recibo | master_relacion */
if (!function_exists('normalizar_tipo')) {
    function normalizar_tipo(string $tipo): string {
        $t = strtolower(trim($tipo));
        if ($t === 'master' || $t === 'notificacion_cobro_master' || $t === 'relacion' || $t === 'relación') {
            return 'master_relacion';
        }
        if ($t === 'notificacion' || $t === 'notificación') return 'notificacion';
        if ($t === 'recibo') return 'recibo';
        return $t;
    }
}

/* ============================================================
   Helpers de datos
   ============================================================ */
/** Arma nombre formal: NOMBRE1 I. APELLIDO1 I. */
if (!function_exists('nombre_formal')) {
    function nombre_formal(array $p): string {
        $n1 = trim($p['nombre1'] ?? '');
        $n2 = trim($p['nombre2'] ?? '');
        $a1 = trim($p['apellido1'] ?? '');
        $a2 = trim($p['apellido2'] ?? '');
        $partes = [];
        if ($n1 !== '') $partes[] = mb_strtoupper($n1);
        if ($n2 !== '') $partes[] = mb_strtoupper(mb_substr($n2, 0, 1)).'.';
        if ($a1 !== '') $partes[] = mb_strtoupper($a1);
        if ($a2 !== '') $partes[] = mb_strtoupper(mb_substr($a2, 0, 1)).'.';
        return trim(implode(' ', $partes)) ?: 'PROPIETARIO/A';
    }
}

/** Lee nombre y logos del condominio. */
if (!function_exists('datos_condominio')) {
    function datos_condominio($conn, int $id_condominio): array {
        $st = $conn->prepare("
            SELECT 
                c.nombre,
                c.url_logo_izquierda,
                c.url_logo_derecha,
                COALESCE(NULLIF(c.linea_1,''),'') AS linea_1,
                COALESCE(NULLIF(c.linea_2,''),'') AS linea_2,
                COALESCE(NULLIF(c.linea_3,''),'') AS linea_3
            FROM public.condominio c
            WHERE c.id_condominio = :c
            LIMIT 1
        ");
        $st->execute([':c' => $id_condominio]);
        $row = $st->fetch(\PDO::FETCH_ASSOC) ?: [];
        $row['nombre'] = trim($row['nombre'] ?? 'CONDOMINIO');

        // Absolutizar logos (garantiza URLs completas)
        $row['url_logo_izquierda'] = absolutizar_logo($row['url_logo_izquierda'] ?? null);
        $row['url_logo_derecha']   = absolutizar_logo($row['url_logo_derecha']   ?? null);
        return $row;
    }
}

/* ============================================================
   COMPOSICIÓN (no envía)
   ============================================================ */
/**
 * compose_email
 * - $tipo: notificacion | recibo | master_relacion
 * - $id  : id_notificacion (para 'notificacion' y 'recibo'), id_notificacion_master (para 'master_relacion')
 * - $token: obligatorio solo para 'notificacion'. Para 'recibo' se resuelve por SQL. Para 'master_relacion' no aplica.
 * - $destino: 'default' => busca correo del propietario (solo notificacion/recibo). Si es email, se usa tal cual.
 * - $to_name_override: si se pasa, personaliza el saludo usando ese nombre (útil en master_relacion).
 *
 * Retorna: [to_email, to_name, subject, html, text, link_target]
 */
if (!function_exists('compose_email')) {
    function compose_email($conn, int $id_condominio, string $tipo, int $id, string $token = '', string $destino = 'default', ?string $to_name_override = null): array {
        $tipo = normalizar_tipo($tipo);
        $baseUrl = rhodium_base_url();

        // Datos del condominio
        $condo = datos_condominio($conn, $id_condominio);
        $nombreCondo = $condo['nombre'];
        $logoLeftAbs  = $condo['url_logo_izquierda'];
        $logoRightAbs = $condo['url_logo_derecha'];

        // Destino
        $to_email = '';
        $to_name  = '';
        $inmueble_ident = '';

        if ($destino === 'default') {
            if ($tipo === 'notificacion' || $tipo === 'recibo') {
                $stD = $conn->prepare("
                    SELECT 
                        u.correo,
                        p.nombre1, p.nombre2, p.apellido1, p.apellido2,
                        i.identificacion
                    FROM public.notificacion_cobro nc
                    JOIN public.inmueble i              ON nc.id_inmueble = i.id_inmueble
                    JOIN public.propietario_inmueble pi ON pi.id_inmueble = i.id_inmueble
                    JOIN public.propietario p           ON p.id_propietario = pi.id_propietario
                    JOIN menu_login.usuario u           ON u.id_usuario = pi.id_usuario
                    WHERE nc.id_notificacion = :id
                    LIMIT 1
                ");
                $stD->execute([':id' => $id]);
                if ($r = $stD->fetch(\PDO::FETCH_ASSOC)) {
                    $to_email = trim($r['correo'] ?? '');
                    $to_name  = nombre_formal($r);
                    $inmueble_ident = trim($r['identificacion'] ?? '');
                }
            }
        } else {
            $to_email = trim($destino);
        }

        // Override explícito del nombre (para personalizar saludo en master_relacion)
        if ($to_name_override && trim($to_name_override) !== '') {
            $to_name = trim($to_name_override);
        }

        // Construcción de URL/Subject/Body
        $titulo     = '';
        $linkDoc    = '';
        $linkTarget = '';
        $desc_interior = '';

        if ($tipo === 'notificacion') {
            $titulo     = 'Notificación de Cobro';
            $linkDoc    = $baseUrl . "generar_notificacion.php?token=" . urlencode($token);
            $linkTarget = "generar_notificacion.php?token=" . $token;
        } elseif ($tipo === 'recibo') {
            // Resuelve el token del recibo asociado a la notificación
            $stR = $conn->prepare("
                SELECT rc.token
                FROM public.recibo_cabecera rc
                JOIN public.recibo_destino_fondos rdf ON rdf.id_recibo = rc.id_recibo
                WHERE rdf.id_notificacion = :id
                ORDER BY rc.fecha_emision DESC, rc.id_recibo DESC
                LIMIT 1
            ");
            $stR->execute([':id' => $id]);
            $tokRec = $stR->fetchColumn();
            if (!$tokRec) {
                throw new \Exception("No se consiguió el token del recibo asociado a la notificación #{$id}.");
            }
            $titulo     = 'Recibo de Pago';
            $linkDoc    = $baseUrl . "generar_recibo.php?token=" . urlencode($tokRec);
            $linkTarget = "generar_recibo.php?token=" . $tokRec;
        } elseif ($tipo === 'master_relacion') {
            $titulo     = 'Relación de Ingresos/Egresos';
            $linkDoc    = $baseUrl . "generar_notificacion_master.php?id_notificacion_master=" . intval($id);
            $linkTarget = "generar_notificacion_master.php?id_notificacion_master=" . intval($id);
            $stM = $conn->prepare("SELECT descripcion FROM public.notificacion_cobro_master WHERE id_notificacion_master = :idm LIMIT 1");
            $stM->execute([':idm' => $id]);
            $desc_interior = trim((string)$stM->fetchColumn());
        } else {
            throw new \Exception("Tipo de envío no soportado: {$tipo}");
        }

        $subject = "{$titulo} - {$nombreCondo}";

        // Header con logos (URLs absolutas garantizadas)
        $logoHtmlLeft  = $logoLeftAbs  ? "<img src=\"{$logoLeftAbs}\" alt=\"Logo\" style=\"display:block; max-height:56px; width:auto; height:auto;\">" : "";
        $logoHtmlRight = $logoRightAbs ? "<img src=\"{$logoRightAbs}\" alt=\"Logo\" style=\"display:block; max-height:56px; width:auto; height:auto;\">" : "";

        $saludo = ($to_name !== '') ? "Estimado(a) {$to_name}," : "Estimado(a) propietario(a),";

        // PIE institucional
        $pie_institucional = "
          <p style='font-size:12px; color:#7a8495; margin:0;'>
            Este correo fue enviado automáticamente por el sistema de condominios <strong>Rhodium</strong>.<br>
            Desarrollado por <strong>rhodiumdev / Soluciones Smartsys, C.A.</strong>
          </p>";

        // Línea de depuración (para borrar luego)
        $debug_line = "<div style=\"font-size:11px; color:#9aa5b1; margin-top:4px;\">DEBUG logos: L="
                    . htmlspecialchars((string)$logoLeftAbs, ENT_QUOTES, 'UTF-8')
                    . " &nbsp;|&nbsp; R="
                    . htmlspecialchars((string)$logoRightAbs, ENT_QUOTES, 'UTF-8')
                    . "</div>";

        $hostForPortal = rtrim(rhodium_base_url(), '/');

        // ========= HTML (estilo corporativo moderno) =========
        $html  = "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'>";
        $html .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        $html .= "<title>" . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "</title>";
        $html .= "</head>";
        $html .= "<body style='margin:0; padding:0; background:#eef2f7; font-family: Arial, Helvetica, sans-serif; color:#203047;'>";

        // Wrapper
        $html .= "<table role='presentation' width='100%' cellpadding='0' cellspacing='0' border='0' style='background:#eef2f7; padding:24px 12px;'><tr><td align='center'>";

        // Tarjeta principal
        $html .= "<table role='presentation' width='640' cellpadding='0' cellspacing='0' border='0' style='max-width:640px; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 8px 28px rgba(16, 29, 48, 0.15);'>";
        // Header con degradado
        $html .= "<tr><td style='background:linear-gradient(135deg,#0e1f44 0%, #6885b4ff  100% , #c0d8ebff 55%); padding:18px 22px;'>";
        $html .= "  <table role='presentation' width='100%' cellpadding='0' cellspacing='0' border='0'><tr>";
        $html .= "    <td align='left' style='width:25%; vertical-align:middle;'>{$logoHtmlLeft}</td>";
        $html .= "    <td align='center' style='width:50%; vertical-align:middle;'>";
        $html .= "      <div style='font-size:18px; line-height:1.2; font-weight:800; color:#ffffff; letter-spacing:.3px;'>{$titulo}</div>";
        $html .= "      <div style='font-size:13px; color:#d9e6fb; margin-top:2px;'>" . htmlspecialchars($nombreCondo, ENT_QUOTES, 'UTF-8') . "</div>";
      // <--  $html .=          $debug_line;  Línea de test con URLs absolutas de los logos
        $html .= "    </td>";
      // <--   $html .= "    <td align='right' style='width:25%; vertical-align:middle;'>{$logoHtmlRight}</td>";
        $html .= "  </tr></table>";
        $html .= "</td></tr>";

        // Contenido
        $html .= "<tr><td style='padding:28px 26px 8px 26px;'>";
        $html .= "  <p style='margin:0 0 12px 0; font-size:15px;'>{$saludo}</p>";

        if ($tipo === 'notificacion' || $tipo === 'recibo') {
            $html .= "  <p style='margin:0 0 10px 0; font-size:15px;'>En el condominio <strong>"
                   . htmlspecialchars($nombreCondo, ENT_QUOTES, 'UTF-8') . "</strong>"
                   . ($inmueble_ident ? " (inmueble <strong>" . htmlspecialchars($inmueble_ident, ENT_QUOTES, 'UTF-8') . "</strong>)" : "")
                   . " se ha generado un nuevo documento: <strong>{$titulo}</strong>.</p>";
        } else {
            $html .= "  <p style='margin:0 0 10px 0; font-size:15px;'>Se encuentra disponible la <strong>{$titulo}</strong> del condominio <strong>"
                   . htmlspecialchars($nombreCondo, ENT_QUOTES, 'UTF-8') . "</strong>.</p>";
            if ($desc_interior !== '') {
                $html .= "<p style='margin:6px 0 2px 0; font-size:14px; color:#4a5a75;'><em>"
                       . htmlspecialchars($desc_interior, ENT_QUOTES, 'UTF-8') . "</em></p>";
            }
        }

        // CTA
        $html .= "  <div style='margin:20px 0 14px 0;' align='center'>";
        $html .= "    <a href='{$linkDoc}' style='display:inline-block; padding:12px 22px; background:#1b5fcc; color:#ffffff; text-decoration:none; font-weight:700; border-radius:8px; box-shadow:0 6px 14px rgba(27,95,204,.25);'>Acceder al documento</a>";
        $html .= "  </div>";

        // Nota portal
        $html .= "  <div style='margin:10px 0 0 0; font-size:12px; color:#51627a; background:#f4f7fb; border:1px solid #e2eaf5; padding:10px 12px; border-radius:8px;'>";
        $html .= "    Este enlace le permite ver únicamente este documento. Para consultar más detalles e historial completo, ingrese al portal:&nbsp; ";
        $html .= "    <strong>" . htmlspecialchars($hostForPortal, ENT_QUOTES, 'UTF-8') . "</strong> con sus credenciales.";
        $html .= "  </div>";

        // Divider
        $html .= "  <div style='height:1px; background:#e8eef7; margin:24px 0 12px 0;'></div>";

        // Nota antispam + pie
        $html .= "  <p style='margin:0 0 10px 0; font-size:12px; color:#6a7a93;'>Nota antispam: este mensaje no es publicidad. Si considera que lo recibió por error, por favor comuníquese con la administración de su condominio.</p>";
        $html .=      $pie_institucional;

        $html .= "</td></tr>";
        $html .= "</table>"; // fin tarjeta

        $html .= "</td></tr></table>"; // fin wrapper
        $html .= "</body></html>";
        $preheader = "Aviso automático del sistema de condominios Rhodium.";
        
        $html .= "<div style='display:none;visibility:hidden;opacity:0;color:transparent;height:0;width:0;overflow:hidden;'>"
       . htmlspecialchars($preheader, ENT_QUOTES, 'UTF-8')
       . "</div>";

        // ========= TEXTO PLANO =========
        $text  = "{$titulo} - {$nombreCondo}\n\n";
        $text .= ($to_name ? "{$to_name},\n\n" : "Estimado(a) propietario(a),\n\n");
        $text .= "Se ha generado un documento {$titulo}.\n";
        if ($inmueble_ident) $text .= "Inmueble: {$inmueble_ident}\n";
        $text .= "Enlace seguro: {$linkDoc}\n\n";
        $text .= "Portal completo: {$hostForPortal}\n";
        if ($logoLeftAbs || $logoRightAbs) {
            $text .= "\n[DEBUG logos] L={$logoLeftAbs} | R={$logoRightAbs}\n";
        }

        return [
            'to_email'    => $to_email, // OJO: puede ir vacío en master_relacion si no se pasó destino email
            'to_name'     => $to_name,
            'subject'     => $subject,
            'html'        => $html,
            'text'        => $text,
            'link_target' => $linkTarget,
        ];
    }
}

/* ============================================================
   ENVÍO SMTP
   ============================================================ */
if (!function_exists('send_via_smtp')) {
    function send_via_smtp(array $config, array $email, bool $con_debug = false): array {
        $mail = new PHPMailer(true);
        $debugLog = '';
        $probeInfo = null;

        try {
            $claveSMTP = !empty($config['contrasena_enc']) ? base64_decode($config['contrasena_enc']) : '';

            // Validación temprana para evitar intentos de conexión inválidos (causan timeouts 504)
            $host = trim((string)($config['host'] ?? ''));
            $puerto = (int)($config['puerto'] ?? 0);
            if ($host === '' || $puerto <= 0) {
                throw new \Exception('Configuración SMTP incompleta (host/puerto)');
            }

            // Sonda TCP previa: falla rápido si el host/puerto no responden
            $errno = 0;
            $errstr = '';
            $socket = @stream_socket_client(
                "tcp://{$host}:{$puerto}",
                $errno,
                $errstr,
                5, // segundos de timeout para la sonda
                STREAM_CLIENT_CONNECT
            );
            if ($socket === false) {
                $probeInfo = "Sonda TCP falló: tcp://{$host}:{$puerto} ({$errno}) {$errstr}";
                throw new \Exception('Conexión inicial SMTP falló (sonda TCP): ' . $probeInfo);
            }
            $probeInfo = "Sonda TCP ok: tcp://{$host}:{$puerto}";
            fclose($socket);

            // Tiempo máximo de conexión y envío para evitar timeouts a nivel de proxy (504)
            $mail->Timeout        = 20;  // segundos para operaciones SMTP
            $mail->SMTPKeepAlive  = false; // no reutilizar conexiones

            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->Port       = $puerto;
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['usuario'];
            $mail->Password   = $claveSMTP;
            $mail->SMTPSecure = (strtolower((string)($config['seguridad'] ?? 'tls')) === 'ssl')
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;

            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';

            // Evita fallos por certificados autofirmados típicos de hosting compartido
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            if ($con_debug) {
                $mail->SMTPDebug   = 2;
                $mail->Debugoutput = function ($str, $level) use (&$debugLog) {
                    $debugLog .= "[$level] $str\n";
                };
            }

            $mail->setFrom($config['from_email'], $config['from_name'] ?? $config['from_email']);

            // Reply-To opcional/seguro
            $replyTo = trim((string)($config['reply_to_email'] ?? ''));
            if ($replyTo !== '') {
                if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                    $mail->addReplyTo($replyTo, $config['reply_to_name'] ?? $replyTo);
                }
            }

            // Dirección destino obligatoria
            $to = trim((string)($email['to_email'] ?? ''));
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Invalid address: (to): {$to}");
            }

            $mail->addAddress($to, $email['to_name'] ?? '');
            $mail->Subject = $email['subject'] ?? '';
            $mail->isHTML(true);
            $mail->Body    = $email['html'] ?? '';
            $mail->AltBody = $email['text'] ?? '';

            // Buenas prácticas de deliverability (opcionales)
            if (!empty($config['list_unsubscribe'])) {
                $mail->addCustomHeader('List-Unsubscribe', '<' . $config['list_unsubscribe'] . '>');
            }

            $mail->send();
            return [
                'ok'=>true,
                'message_id'=>$mail->getLastMessageID(),
                'debug'=>$debugLog,
                'probe'=>$probeInfo,
            ];
        } catch (PHPMailerException $e) {
            error_log('[lib_email][send_via_smtp] PHPMailerException: ' . $e->getMessage());
            return [
                'ok'=>false,
                'error'=>"Mailer Error: " . ($mail->ErrorInfo ?? 'desconocido'),
                'exception'=>$e->getMessage(),
                'debug'=>$debugLog,
                'probe'=>$probeInfo,
            ];
        } catch (\Throwable $e) {
            error_log('[lib_email][send_via_smtp] Throwable: ' . $e->getMessage());
            return [
                'ok'=>false,
                'error'=>"Mailer Error: " . ($mail->ErrorInfo ?? 'desconocido'),
                'exception'=>$e->getMessage(),
                'debug'=>$debugLog,
                'probe'=>$probeInfo,
            ];
        }
    }
}


// Sustituye tu get_smtp_config existente por esta versión “tolerante”
// === Helper centralizado para obtener la configuración SMTP activa ===
// Acepta PDO, tu wrapper DB con ->prepare() o __call(), o NULL. Si no sirve, obtiene DB::getInstance() adentro.
if (!function_exists('get_smtp_config')) {
  function get_smtp_config($conn_like, int $id_condominio): array {
    // Normalizar conexión: si no es usable, intentar DB::getInstance()
    $conn_like = normalizar_conexion($conn_like);

    if (!$conn_like) {
      // No hay forma segura de preparar consultas; devolver vacío en vez de fatal error
      error_log('[lib_email][get_smtp_config] No se pudo obtener una conexión con prepare().');
      return [];
    }

    // 1) "email"."config" (tu esquema principal)
    try {
      $st = $conn_like->prepare('
        SELECT host, puerto, usuario, contrasena_enc, seguridad,
               from_email, from_name, reply_to_email, reply_to_name, list_unsubscribe
          FROM "email"."config"
         WHERE id_condominio = :c AND activo = TRUE
         ORDER BY updated_at DESC NULLS LAST, id_email_config DESC
         LIMIT 1
      ');
      $st->execute([':c'=>$id_condominio]);
      $row = $st->fetch(PDO::FETCH_ASSOC);
      if ($row && !empty($row['host'])) return $row;
    } catch (Throwable $e) {
      error_log('[lib_email][get_smtp_config] email.config: '.$e->getMessage());
      // Intento de compatibilidad si falta la columna list_unsubscribe
      if (stripos($e->getMessage(), 'list_unsubscribe') !== false) {
        try {
          $st = $conn_like->prepare('
            SELECT host, puerto, usuario, contrasena_enc, seguridad,
                   from_email, from_name, reply_to_email, reply_to_name
              FROM "email"."config"
             WHERE id_condominio = :c AND activo = TRUE
             ORDER BY updated_at DESC NULLS LAST, id_email_config DESC
             LIMIT 1
          ');
          $st->execute([':c'=>$id_condominio]);
          $row = $st->fetch(PDO::FETCH_ASSOC);
          if ($row && !empty($row['host'])) return $row;
        } catch (Throwable $e2) {
          error_log('[lib_email][get_smtp_config] email.config sin list_unsubscribe: '.$e2->getMessage());
        }
      }
    }

    // 2) public.email_config (fallback, por si tu instalación lo usa)
    try {
      $st = $conn_like->prepare('
        SELECT host, puerto, usuario, contrasena_enc, seguridad,
               from_email, from_name, reply_to_email, reply_to_name, list_unsubscribe
          FROM public.email_config
         WHERE id_condominio = :c AND activo = TRUE
         LIMIT 1
      ');
      $st->execute([':c'=>$id_condominio]);
      $row = $st->fetch(PDO::FETCH_ASSOC);
      if ($row && !empty($row['host'])) return $row;
    } catch (Throwable $e) {
      error_log('[lib_email][get_smtp_config] public.email_config: '.$e->getMessage());
      if (stripos($e->getMessage(), 'list_unsubscribe') !== false) {
        try {
          $st = $conn_like->prepare('
            SELECT host, puerto, usuario, contrasena_enc, seguridad,
                   from_email, from_name, reply_to_email, reply_to_name
              FROM public.email_config
             WHERE id_condominio = :c AND activo = TRUE
             LIMIT 1
          ');
          $st->execute([':c'=>$id_condominio]);
          $row = $st->fetch(PDO::FETCH_ASSOC);
          if ($row && !empty($row['host'])) return $row;
        } catch (Throwable $e2) {
          error_log('[lib_email][get_smtp_config] public.email_config sin list_unsubscribe: '.$e2->getMessage());
        }
      }
    }

    return [];
  }
}

// Normaliza la conexión recibida para garantizar que soporte ->prepare(), contemplando wrappers con __call
if (!function_exists('normalizar_conexion')) {
  function normalizar_conexion($conn_like) {
    $esUsable = function ($c) {
      return is_object($c) && (
        method_exists($c, 'prepare') ||
        is_callable([$c, 'prepare']) ||
        method_exists($c, '__call')
      );
    };

    if ($esUsable($conn_like)) {
      return $conn_like;
    }

    if (class_exists('DB') && method_exists('DB', 'getInstance')) {
      $conn = DB::getInstance();
      if ($esUsable($conn)) {
        return $conn;
      }
    }

    return null;
  }
}
