<?php
// email/procesar_cola.php — Procesa la cola en lotes (para cron) SIN tocar endpoints.
require_once __DIR__ . "/../core/config.php";
require_once __DIR__ . "/../core/PDO.class.php";
require_once __DIR__ . "/lib_email.php"; // compose_email(), send_via_smtp(), normalizar_tipo()

/**
 * Procesa N correos en estado 'en_cola' → envía y actualiza estados.
 * Retorna un resumen.
 */
function procesar_cola_lote(int $limit = 50): array {
    $db = DB::getInstance();

    // Traer lote en FIFO
    $stSel = $db->prepare('
        SELECT *
        FROM "email"."cola"
        WHERE estado = \'en_cola\'
        ORDER BY id_email ASC
        LIMIT :lim
    ');
    $stSel->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stSel->execute();
    $rows = $stSel->fetchAll(PDO::FETCH_ASSOC);

    $procesados = 0; $enviados = 0; $fallidos = 0; $detalles = [];

    foreach ($rows as $row) {
        $procesados++;
        $id_email      = (int)$row['id_email'];
        $id_condominio = (int)$row['id_condominio'];
        $id_cfg        = (int)$row['id_email_config'];
        $target_tipo   = normalizar_tipo((string)($row['target_tipo'] ?? ''));
        $target_id     = (int)($row['target_id'] ?? 0);
        $para_email    = trim((string)($row['para_email'] ?? ''));
        $para_nombre   = trim((string)($row['para_nombre'] ?? ''));
        $link_token    = (string)($row['link_token'] ?? ''); // por si aplica
        $c_html        = (string)($row['cuerpo_html'] ?? '');
        $c_text        = (string)($row['cuerpo_texto'] ?? '');
        $asunto        = (string)($row['asunto'] ?? '');

        // Marcar "enviando"
        $db->prepare('UPDATE "email"."cola" SET estado = \'enviando\' WHERE id_email = :id')
           ->execute([':id'=>$id_email]);

        try {
            // Config SMTP
            $stCfg = $db->prepare('SELECT * FROM "email"."config" WHERE id_email_config = :id LIMIT 1');
            $stCfg->execute([':id'=>$id_cfg]);
            $config = $stCfg->fetch(PDO::FETCH_ASSOC);
            if (!$config) throw new Exception('Config SMTP no encontrada para la cola #' . $id_email);

            // Si ya hay cuerpo y asunto en la cola, los usamos (más eficiente)
            if ($asunto !== '' && $c_html !== '' && $c_text !== '') {
                $emailData = [
                    'to_email' => $para_email,
                    'to_name'  => $para_nombre,
                    'subject'  => $asunto,
                    'html'     => $c_html,
                    'text'     => $c_text,
                ];
            } else {
                // Componer con el motor único
                $emailData = compose_email($db, $id_condominio, $target_tipo, $target_id, $link_token, 
                    $target_tipo === 'master_relacion' ? $para_email : 'default',
                    $para_nombre ?: null
                );

                // En master_relacion, asegurar destinatario
                if ($target_tipo === 'master_relacion') {
                    $emailData['to_email'] = $para_email;
                    $emailData['to_name']  = $para_nombre ?: ($emailData['to_name'] ?? '');
                }
            }

            // Validación de destino
            if (empty($emailData['to_email'])) {
                throw new Exception('El destinatario está vacío.');
            }

            // Enviar
            $res = send_via_smtp($config, $emailData);
            if (!($res['ok'] ?? false)) {
                throw new Exception($res['error'] ?? 'No se pudo enviar');
            }

            // OK → actualizar enviado
            $db->prepare('UPDATE "email"."cola" 
                            SET estado = \'enviado\', enviado_en = now(), message_id = :mid, ultimo_error = NULL
                          WHERE id_email = :id')
               ->execute([':mid'=>$res['message_id'] ?? null, ':id'=>$id_email]);

            $enviados++;
            $detalles[] = "OK #{$id_email} → {$emailData['to_email']}";
        } catch (Throwable $e) {
            $fallidos++;
            $db->prepare('UPDATE "email"."cola" 
                            SET estado = \'fallido\', intentos = intentos + 1, ultimo_error = :err
                          WHERE id_email = :id')
               ->execute([':err'=>$e->getMessage(), ':id'=>$id_email]);
            $detalles[] = "FAIL #{$id_email}: " . $e->getMessage();
        }
    }

    return ['procesados'=>$procesados, 'enviados'=>$enviados, 'fallidos'=>$fallidos, 'detalles'=>$detalles];
}

// Si se invoca manualmente por navegador/CLI sin parámetros:
if (php_sapi_name() !== 'cli') {
    // Ejecutar un lote por defecto (opcional).
    $r = procesar_cola_lote(50);
    echo json_encode(['status'=>'ok'] + $r);
}
