<?php
// notificacion_cobro_master_aprobar.php — Aprobar master y encolar emails masivos (presupuesto/relación)
header('Content-Type: application/json');

require_once __DIR__ . "/core/config.php";
require_once __DIR__ . "/core/PDO.class.php";
// *** Método aprobado: composición y envío viven en lib_email.php
require_once __DIR__ . "/email/lib_email.php";

/* ============================================================
   Helpers
   ============================================================ */

function marcar_master_estado($db, int $id_master, string $estado): bool {
    try {
        $sql = "
            UPDATE public.notificacion_cobro_master
               SET estado = :estado
             WHERE id_notificacion_master = :idm
        ";
        $st = $db->prepare($sql);
        $st->execute([':estado'=>$estado, ':idm'=>$id_master]);
        error_log("[aprobar] UPDATE master {$id_master} -> estado='{$estado}', rows=".$st->rowCount());
        return $st->rowCount() > 0;
    } catch (\Throwable $e) {
        error_log("[aprobar] marcar_master_estado error: ".$e->getMessage());
        return false;
    }
}

function generar_movimientos_contables(PDO $db, array $master, array $detalles, int $id_condominio): array {
    $fechaMaestra = $master['fecha_emision'] ?? date('Y-m-d');
    $mes   = (int)($master['mes'] ?? 0);
    $anio  = (int)($master['anio'] ?? 0);
    $id_moneda = (int)($master['id_moneda'] ?? 0);
    $descCab = strtoupper(trim($master['descripcion'] ?? 'NOTIFICACIÓN MASTER'));

    $sqlMG = $db->prepare("INSERT INTO movimiento_general
        (id_condominio, id_cuenta, descripcion, monto_total, id_moneda, tipo_movimiento,
         estado, fecha_movimiento, fecha_creacion, mes_contable, anio_contable)
        VALUES
        (:condo, :cuenta, :desc, :monto, :moneda, :tipo, 'aprobado', :fecha, CURRENT_TIMESTAMP, :mes, :anio)
        RETURNING id_movimiento");

    $sqlIng = $db->prepare("INSERT INTO movimiento_detalle_ingreso
        (id_movimiento_general, id_cuenta, id_plan_cuenta, monto, tasa, monto_base, referencia)
        VALUES (:id, :cuenta, :plan, :monto, 1, :monto, :ref)");

    $sqlEgr = $db->prepare("INSERT INTO movimiento_detalle_egreso
        (id_movimiento_general, id_cuenta, id_plan_cuenta, descripcion, monto_aplicado, tasa, monto_base, estado, fecha_aplicacion)
        VALUES (:id, :cuenta, :plan, :desc, :monto, 1, :monto, 'aprobado', :fecha)");

    $sqlSaldoMas  = $db->prepare("UPDATE cuenta SET saldo_actual = COALESCE(saldo_actual,0) + :monto WHERE id_cuenta = :cuenta");
    $sqlSaldoMenos= $db->prepare("UPDATE cuenta SET saldo_actual = COALESCE(saldo_actual,0) - :monto WHERE id_cuenta = :cuenta");
    $sqlCuentaVal = $db->prepare("SELECT 1 FROM cuenta WHERE id_cuenta = :cuenta AND id_condominio = :condo AND estatus = TRUE");

    $creados = 0;

    foreach ($detalles as $det) {
        $tipo = ($det['tipo_movimiento'] === 'egreso') ? 'egreso' : 'ingreso';
        $id_cuenta = (int)($det['id_cuenta'] ?? 0);
        if ($id_cuenta <= 0) {
            throw new Exception('Falta la cuenta financiera en un detalle de la notificación.');
        }

        $sqlCuentaVal->execute([':cuenta'=>$id_cuenta, ':condo'=>$id_condominio]);
        if (!$sqlCuentaVal->fetchColumn()) {
            throw new Exception("La cuenta financiera {$id_cuenta} no pertenece al condominio o está inactiva.");
        }

        $monto = (float)$det['monto'];
        $fechaDetalle = !empty($det['fecha_pago']) ? $det['fecha_pago'] : $fechaMaestra;
        $referencia   = !empty($det['referencia_pago']) ? strtoupper(trim($det['referencia_pago']))
                                                         : 'MASTER '.$master['id_notificacion_master'].' DET '.$det['id_detalle'];
        $descDet = strtoupper(trim($det['descripcion'] ?? $descCab));
        if ($monto <= 0) continue;

        $sqlMG->execute([
            ':condo' => $id_condominio,
            ':cuenta'=> $id_cuenta,
            ':desc'  => "MASTER #{$master['id_notificacion_master']} - {$descDet}",
            ':monto' => $monto,
            ':moneda'=> $id_moneda,
            ':tipo'  => $tipo,
            ':fecha' => $fechaDetalle,
            ':mes'   => $mes,
            ':anio'  => $anio,
        ]);
        $id_mov = (int)$sqlMG->fetchColumn();

        if ($tipo === 'ingreso') {
            $sqlIng->execute([
                ':id' => $id_mov,
                ':cuenta' => $id_cuenta,
                ':plan' => (int)$det['id_plan_cuenta'],
                ':monto' => $monto,
                ':ref' => $referencia
            ]);
            $sqlSaldoMas->execute([':monto'=>$monto, ':cuenta'=>$id_cuenta]);
        } else {
            $sqlEgr->execute([
                ':id' => $id_mov,
                ':cuenta' => $id_cuenta,
                ':plan' => (int)$det['id_plan_cuenta'],
                ':desc' => $descDet.' / '.$referencia,
                ':monto'=> $monto,
                ':fecha'=> $fechaDetalle,
            ]);
            $sqlSaldoMenos->execute([':monto'=>$monto, ':cuenta'=>$id_cuenta]);
        }

        $creados++;
    }

    return ['movimientos_creados' => $creados];
}

function limpiar_detalles_huerfanos(PDO $db, int $id_condominio): void {
    try {
        // El generador de notificaciones debería dejar consistentes cabeceras y detalles.
        // Si por alguna razón quedan detalles sin cabecera (p.ej. abortos previos),
        // se eliminan antes del commit para no romper la FK deferrable.
        $sql = "DELETE FROM notificacion_cobro_detalle d
                 WHERE d.id_condominio = :condo
                   AND NOT EXISTS (
                        SELECT 1 FROM notificacion_cobro c
                        WHERE c.id_notificacion = d.id_notificacion
                   )";
        $st = $db->prepare($sql);
        $st->execute([':condo'=>$id_condominio]);
    } catch (\Throwable $e) {
        // No interrumpe el flujo; el commit validará si aún queda alguna inconsistencia.
        error_log('[aprobar] limpiar_detalles_huerfanos: '.$e->getMessage());
    }
}

/* ============================================================
   Main
   ============================================================ */

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status'=>'error','message'=>'Método no permitido']);
        exit;
    }

    $id_master     = isset($_POST['id_notificacion_master']) ? (int)$_POST['id_notificacion_master'] : 0;
    $id_condominio = isset($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;
    if ($id_master <= 0 || $id_condominio <= 0) {
        echo json_encode(['status'=>'error','message'=>'Parámetros incompletos (id_master / id_condominio)']);
        exit;
    }

    $db = DB::getInstance();

    // Compatibilidad: garantizar columnas críticas en detalle
    $db->exec("ALTER TABLE IF EXISTS notificacion_cobro_detalle_master ADD COLUMN IF NOT EXISTS id_cuenta INT");
    $db->exec("ALTER TABLE IF EXISTS notificacion_cobro_detalle_master ADD COLUMN IF NOT EXISTS fecha_pago DATE");
    $db->exec("ALTER TABLE IF EXISTS notificacion_cobro_detalle_master ADD COLUMN IF NOT EXISTS referencia_pago VARCHAR(150)");

    // Master
    $stM = $db->prepare("SELECT id_notificacion_master, id_tipo, descripcion, anio, mes, fecha_emision, id_moneda
                           FROM public.notificacion_cobro_master
                          WHERE id_notificacion_master = :idm
                          LIMIT 1");
    $stM->execute([':idm'=>$id_master]);
    $master = $stM->fetch(\PDO::FETCH_ASSOC);
    if (!$master) {
        echo json_encode(['status'=>'error','message'=>'Master no encontrado.']);
        exit;
    }
    $id_tipo = (int)($master['id_tipo'] ?? 0);
    if ($id_tipo <= 0) {
        echo json_encode(['status'=>'error','message'=>'Master sin tipo válido.']);
        exit;
    }

    // Detalles con plan y cuenta financiera
    $stDet = $db->prepare("SELECT id_detalle, id_plan_cuenta, id_cuenta, descripcion, monto, tipo_movimiento,
                                   fecha_pago, referencia_pago
                            FROM public.notificacion_cobro_detalle_master
                           WHERE id_notificacion_master = :idm");
    $stDet->execute([':idm'=>$id_master]);
    $detalles = $stDet->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    if (!$detalles) {
        echo json_encode(['status'=>'error','message'=>'La master no tiene detalles para generar movimientos.']);
        exit;
    }

    // Config email activa (requerida para encolar)
    // Ajusta a tu esquema real si difiere
    $stCfg = $db->prepare('SELECT id_email_config FROM "email"."config"
                            WHERE id_condominio = :c AND activo = TRUE
                            ORDER BY updated_at DESC NULLS LAST, id_email_config DESC
                            LIMIT 1');
    $stCfg->execute([':c'=>$id_condominio]);
    $id_email_config = (int)$stCfg->fetchColumn();
    if ($id_email_config <= 0) {
        echo json_encode(['status'=>'error','message'=>'No hay configuración de email activa para este condominio.']);
        exit;
    }

    $db->beginTransaction();
    $encoladas = 0;
    $movimientos = ['movimientos_creados' => 0];

    if ($id_tipo === 1) {
        // =========================
        // TIPO 1 (Presupuesto): generar hijas y encolar una por hija
        // =========================

        // 1) Generar/actualizar hijas desde la maestra
        $stGen = $db->prepare('SELECT public.generar_notificaciones_desde_maestra(:idm) AS creadas');
        $stGen->execute([':idm' => $id_master]);
        $creadas = (int)$stGen->fetchColumn();
        error_log("[aprobar] Hijas generadas por la función: $creadas");

        // 2) Traer hijas (con token)
        $stH = $db->prepare("
            SELECT nc.id_notificacion, nc.token
              FROM public.notificacion_cobro nc
             WHERE nc.id_notificacion_master = :idm
             ORDER BY nc.id_notificacion
        ");
        $stH->execute([':idm'=>$id_master]);
        $hijas = $stH->fetchAll(\PDO::FETCH_ASSOC);

        if (!$hijas || count($hijas)===0) {
            $db->rollBack();
            echo json_encode(['status'=>'error','message'=>'No hay notificaciones hijas generadas para esta master.']);
            exit;
        }

        // 3) Preparar statement de encolado
        $ins = $db->prepare('INSERT INTO "email"."cola"
            (id_condominio, id_email_config, id_plantilla,
             para_email, para_nombre, id_propietario, id_usuario,
             asunto, cuerpo_html, cuerpo_texto, headers_json, adjuntos_json,
             link_target, link_payload, target_tipo, target_id)
          VALUES
            (:id_condominio, :id_email_config, :id_plantilla,
             :para_email, :para_nombre, :id_propietario, :id_usuario,
             :asunto, :cuerpo_html, :cuerpo_texto, NULL, NULL,
             :link_target, :link_payload, :target_tipo, :target_id)');

        // 4) Para cada hija, usar el MÉTODO APROBADO: compose_email()
        foreach ($hijas as $h) {
            $id_notif = (int)$h['id_notificacion'];
            $token    = (string)$h['token'];

            // compose_email resuelve 'default' -> correo del propietario
            $emailPayload = compose_email(
                $db,
                $id_condominio,
                'notificacion',
                $id_notif,
                $token,
                'default',     // destino por defecto (propietario)
                null
            );

            $to = trim((string)($emailPayload['to_email'] ?? ''));
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                // si no hay correo válido, no encolamos
                continue;
            }

            // Si quieres asociar id_propietario / id_usuario, resuélvelos aquí (opcional)
            $stDU = $db->prepare("
                SELECT p.id_propietario, u.id_usuario
                FROM public.notificacion_cobro nc
                JOIN public.inmueble i              ON nc.id_inmueble = i.id_inmueble
                JOIN public.propietario_inmueble pi ON pi.id_inmueble = i.id_inmueble
                JOIN public.propietario p           ON p.id_propietario = pi.id_propietario
                JOIN menu_login.usuario u           ON u.id_usuario = pi.id_usuario
                WHERE nc.id_notificacion = :id
                LIMIT 1
            ");
            $stDU->execute([':id'=>$id_notif]);
            $du = $stDU->fetch(\PDO::FETCH_ASSOC) ?: [];
            $id_propietario = isset($du['id_propietario']) ? (int)$du['id_propietario'] : null;
            $id_usuario     = isset($du['id_usuario'])     ? (int)$du['id_usuario']     : null;

            $payloadJson = json_encode([
                'tipo'            => 'notificacion',
                'id_notificacion' => $id_notif,
                'token'           => $token
            ], JSON_UNESCAPED_UNICODE);

            $ins->execute([
                ':id_condominio'   => $id_condominio,
                ':id_email_config' => $id_email_config,
                ':id_plantilla'    => null,
                ':para_email'      => $to,
                ':para_nombre'     => ($emailPayload['to_name'] ?? ''),
                ':id_propietario'  => $id_propietario,
                ':id_usuario'      => $id_usuario,
                ':asunto'          => ($emailPayload['subject'] ?? ''),
                ':cuerpo_html'     => ($emailPayload['html'] ?? ''),
                ':cuerpo_texto'    => ($emailPayload['text'] ?? ''),
                ':link_target'     => ($emailPayload['link_target'] ?? ''),
                ':link_payload'    => $payloadJson,
                ':target_tipo'     => 'notificacion',  // ENUM válido en email.cola
                ':target_id'       => $id_notif
            ]);
            $encoladas++;
        }

        // Movimientos contables a partir del presupuesto ejecutado
        $movimientos = generar_movimientos_contables($db, array_merge($master,['id_notificacion_master'=>$id_master]), $detalles, $id_condominio);

        // Limpia cualquier detalle huérfano que haya quedado de ejecuciones previas
        // antes de validar las FKs deferrables en el commit.
        limpiar_detalles_huerfanos($db, $id_condominio);

        // Marcar master emitida
        marcar_master_estado($db, $id_master, 'emitida');

    } else if ($id_tipo === 2) {
        // =========================
        // TIPO 2 (Relación): encolar a TODOS los propietarios con correo
        // =========================

        // 1) Destinatarios únicos
        $qDest = "
            SELECT DISTINCT
              u.correo,
              p.nombre1, p.nombre2, p.apellido1, p.apellido2,
              p.id_propietario, u.id_usuario
            FROM public.propietario_inmueble pi
            JOIN public.propietario p ON p.id_propietario = pi.id_propietario
            JOIN menu_login.usuario u ON u.id_usuario = pi.id_usuario
            JOIN public.inmueble i    ON i.id_inmueble = pi.id_inmueble
            WHERE i.id_condominio = :c
              AND COALESCE(u.correo,'') <> ''
            ORDER BY u.correo
        ";
        $stDest = $db->prepare($qDest);
        $stDest->execute([':c'=>$id_condominio]);
        $destinatarios = $stDest->fetchAll(\PDO::FETCH_ASSOC);

        if (!$destinatarios || count($destinatarios)===0) {
            $db->rollBack();
            echo json_encode(['status'=>'error','message'=>'No hay propietarios con correo para enviar la Relación.']);
            exit;
        }

        // 2) Statement de encolado
        $ins = $db->prepare('INSERT INTO "email"."cola"
            (id_condominio, id_email_config, id_plantilla,
             para_email, para_nombre, id_propietario, id_usuario,
             asunto, cuerpo_html, cuerpo_texto, headers_json, adjuntos_json,
             link_target, link_payload, target_tipo, target_id)
          VALUES
            (:id_condominio, :id_email_config, :id_plantilla,
             :para_email, :para_nombre, :id_propietario, :id_usuario,
             :asunto, :cuerpo_html, :cuerpo_texto, NULL, NULL,
             :link_target, :link_payload, :target_tipo, :target_id)');

        // 3) Para cada destinatario, componer con MÉTODO APROBADO
        foreach ($destinatarios as $d) {
            $to = trim((string)($d['correo'] ?? ''));
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) continue;

            // Personaliza saludo (opcional)
            $to_name_override = trim(($d['nombre1'] ?? '').' '.($d['apellido1'] ?? ''));

            $emailPayload = compose_email(
                $db,
                $id_condominio,
                'master_relacion',
                $id_master,
                '',                 // token no aplica
                $to,                // destino explícito
                $to_name_override
            );

            $payloadJson = json_encode([
                'tipo'                   => 'master_relacion',
                'id_notificacion_master' => $id_master
            ], JSON_UNESCAPED_UNICODE);

            $ins->execute([
                ':id_condominio'   => $id_condominio,
                ':id_email_config' => $id_email_config,
                ':id_plantilla'    => null,
                ':para_email'      => $to,
                ':para_nombre'     => ($emailPayload['to_name'] ?? $to_name_override),
                ':id_propietario'  => isset($d['id_propietario']) ? (int)$d['id_propietario'] : null,
                ':id_usuario'      => isset($d['id_usuario'])     ? (int)$d['id_usuario']     : null,
                ':asunto'          => ($emailPayload['subject'] ?? ''),
                ':cuerpo_html'     => ($emailPayload['html'] ?? ''),
                ':cuerpo_texto'    => ($emailPayload['text'] ?? ''),
                ':link_target'     => ($emailPayload['link_target'] ?? ''),
                ':link_payload'    => $payloadJson,
                ':target_tipo'     => 'master',   // ENUM válido ('master')
                ':target_id'       => $id_master
            ]);
            $encoladas++;
        }

        // Movimientos contables a partir de la relación ejecutada
        $movimientos = generar_movimientos_contables($db, array_merge($master,['id_notificacion_master'=>$id_master]), $detalles, $id_condominio);

        // Limpia cualquier detalle huérfano previo para no bloquear el commit por la FK.
        limpiar_detalles_huerfanos($db, $id_condominio);

        // Marcar master emitida
        marcar_master_estado($db, $id_master, 'emitida');

    } else {
        $db->rollBack();
        echo json_encode(['status'=>'error','message'=>'Tipo de master no soportado para aprobación.']);
        exit;
    }

    $db->commit();

    // Disparo best-effort del procesador de cola (opcional)
    $rutaProcesar = __DIR__ . '/email/procesar_cola.php';
    if (file_exists($rutaProcesar)) {
        try {
            require_once $rutaProcesar;
            if (function_exists('procesar_cola_lote')) {
                procesar_cola_lote(50);
            }
        } catch (\Throwable $e) {
            error_log("[aprobar] procesar_cola best-effort: " . $e->getMessage());
        }
    }

    $creados = isset($movimientos['movimientos_creados']) ? (int)$movimientos['movimientos_creados'] : 0;

    echo json_encode([
        'status'   => 'ok',
        'message'  => "Se encolaron {$encoladas} correos y se generaron {$creados} movimientos para la master #{$id_master}.",
        'enqueued' => $encoladas,
        'movimientos_creados' => $creados
    ]);

} catch (\Throwable $e) {
    try { if (isset($db) && $db) $db->rollBack(); } catch(\Throwable $e2){}
    error_log('[aprobar][error] ' . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
