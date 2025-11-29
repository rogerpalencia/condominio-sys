<?php
@session_start();
require_once 'core/PDO.class.php';

/**
 * aprobar_fondos.php (versión robusta)
 * - No aborta toda la aprobación por una notificación sin saldo; la omite.
 * - Si el abono excede el saldo, lo ajusta al saldo (clamp).
 * - Concurrency-safe: usa FOR UPDATE por notificación.
 * - Estados: 'pagada' | 'parcialmente_pagada' | 'pendiente'.
 */

function jerr($msg, $code = 400) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

$id_recibo      = isset($_POST['id_recibo'])      ? (int)$_POST['id_recibo']      : 0;
$id_condominio  = isset($_POST['id_condominio'])  ? (int)$_POST['id_condominio']  : 0;
$id_usuario     = isset($_POST['id_usuario'])     ? (int)$_POST['id_usuario']     : 0;
$id_inmueble    = isset($_POST['id_inmueble'])    ? (int)$_POST['id_inmueble']    : 0;
$origenes       = json_decode($_POST['origenes']       ?? '[]', true);
$notificaciones = json_decode($_POST['notificaciones'] ?? '[]', true);
$moneda_credito = trim($_POST['moneda_credito']       ?? '');

if (!$id_recibo || !$id_condominio || !$id_usuario || !$id_inmueble || !is_array($origenes)) {
    jerr('Parámetros inválidos.');
}

$db = DB::getInstance();
if (!$db) jerr('No se pudo conectar a la base de datos.', 500);

$db->query('BEGIN');

/* ============================ base: propietario y moneda ============================ */
$id_propietario = $db->single(
    "SELECT id_propietario
       FROM propietario_inmueble
      WHERE id_inmueble = :inm
      LIMIT 1",
    ['inm' => $id_inmueble]
);
if (!$id_propietario) { $db->query('ROLLBACK'); jerr('Propietario no encontrado para el inmueble.'); }

$condoRow = $db->row(
    "SELECT c.id_moneda, m.codigo
       FROM condominio c
       JOIN moneda m ON m.id_moneda = c.id_moneda
      WHERE c.id_condominio = :cid",
    ['cid' => $id_condominio]
);
if (!$condoRow) { $db->query('ROLLBACK'); jerr('Condominio no encontrado.'); }
$moneda_base_id     = (int)$condoRow['id_moneda'];
$moneda_base_codigo = $condoRow['codigo'];

/* ============================ 1) ORÍGENES (aprobar/rechazar) ============================ */
$total_base_pagado = 0.0;
$mapaCreditoUsado  = []; // [id_moneda] => monto positivo (luego insertamos negativo en crédito)
if (!empty($origenes)) {
    foreach ($origenes as $o) {
        $id_origen = (int)($o['id_origen_fondos'] ?? 0);
        $estado    = in_array(($o['estado'] ?? ''), ['aprobado','rechazado'], true) ? $o['estado'] : 'rechazado';
        if ($id_origen <= 0) continue;

        $db->query(
            "UPDATE recibo_origen_fondos
                SET estado = :est, fecha_actualizacion = CURRENT_TIMESTAMP
              WHERE id_origen_fondos = :id AND id_recibo = :rec",
            ['est' => $estado, 'id' => $id_origen, 'rec' => $id_recibo]
        );

        if ($estado !== 'aprobado') continue;

        $row = $db->row(
            "SELECT monto_base, id_moneda, tipo_origen, COALESCE(id_cuenta,0) AS id_cuenta, monto, tasa
               FROM recibo_origen_fondos
              WHERE id_origen_fondos = :id",
            ['id' => $id_origen]
        );
        if (!$row) continue;

        $total_base_pagado += (float)$row['monto_base'];

        if ((int)$row['id_cuenta'] === 0 || $row['tipo_origen'] === 'credito') {
            $idMon = (int)$row['id_moneda'];
            $mapaCreditoUsado[$idMon] = ($mapaCreditoUsado[$idMon] ?? 0) + (float)$row['monto'];
        }
    }
}

/* ============================ 2) DESTINOS (recrear) ============================ */
$db->query("DELETE FROM recibo_destino_fondos WHERE id_recibo = :r", ['r' => $id_recibo]);

$notifData = [];             // para actualizar notificación al final
$total_base_aplicado = 0.0;

$omitidas  = [];             // notificaciones omitidas por saldo 0
$ajustadas = [];             // notificaciones cuyo abono se ajustó (clamp)

foreach ($notificaciones as $n) {
    $id_notif = (int)($n['id_notificacion'] ?? 0);
    $abono    = (float)str_replace(',', '.', ($n['abono'] ?? 0));
    if ($id_notif <= 0 || $abono <= 0) continue;

    // Bloqueo por fila para evitar condiciones de carrera
    $d = $db->row(
        "SELECT id_moneda, monto_x_pagar, COALESCE(monto_pagado,0) AS monto_pagado
           FROM notificacion_cobro
          WHERE id_notificacion = :n
          FOR UPDATE",
        ['n' => $id_notif]
    );
    if (!$d) { $db->query('ROLLBACK'); jerr("No se encontró la notificación {$id_notif}."); }

    $id_moneda_notif = (int)$d['id_moneda'];
    $monto_x_pagar   = (float)$d['monto_x_pagar'];
    $monto_pagado    = (float)$d['monto_pagado'];

    // Saldo pendiente real (redondeado a 2)
    $saldo_pendiente = round($monto_x_pagar - $monto_pagado, 2);

    // Si no hay saldo, omitir silenciosamente
    if ($saldo_pendiente <= 0) {
        $omitidas[] = $id_notif;
        continue;
    }

    // Clamp del abono al saldo pendiente
    $abono_original = $abono;
    if ($abono > $saldo_pendiente) {
        $abono = $saldo_pendiente;
        $ajustadas[] = ['id' => $id_notif, 'de' => $abono_original, 'a' => $abono];
    }

    // Tasa a moneda base
    $tasa = 1.0;
    if ($id_moneda_notif !== $moneda_base_id) {
        $tasa = (float)($db->single(
            "SELECT tasa
               FROM tipo_cambio
              WHERE id_moneda_origen = :o AND id_moneda_destino = :d
              ORDER BY fecha_vigencia DESC
              LIMIT 1",
            ['o' => $id_moneda_notif, 'd' => $moneda_base_id]
        ) ?: 1.0);
    }
    $monto_base = $abono * $tasa;

    // Insert destino
    $db->query(
        "INSERT INTO recibo_destino_fondos (
            id_recibo, id_notificacion, monto_aplicado,
            id_moneda, id_moneda_base, tasa, monto_base
        ) VALUES (
            :r, :n, :m, :im, :imb, :t, :mb
        )",
        [
            'r'   => $id_recibo,
            'n'   => $id_notif,
            'm'   => $abono,
            'im'  => $id_moneda_notif,
            'imb' => $moneda_base_id,
            't'   => $tasa,
            'mb'  => $monto_base
        ]
    );

    $total_base_aplicado += $monto_base;

    $notifData[$id_notif] = [
        'monto_aplicado' => $abono,
        'monto_x_pagar'  => $monto_x_pagar,
        'monto_pagado'   => $monto_pagado,
        'id_moneda'      => $id_moneda_notif
    ];
}

/* ============================ 3) ACTUALIZAR NOTIFICACIONES ============================ */
foreach ($notifData as $id_notif => $data) {
    $nuevo_pagado = round($data['monto_pagado'] + $data['monto_aplicado'], 2);
    $mxp          = round($data['monto_x_pagar'], 2);

    if ($nuevo_pagado <= 0) {
        $estado = 'pendiente';
    } elseif ($nuevo_pagado >= $mxp) {
        $estado = 'pagada';
    } else {
        $estado = 'parcialmente_pagada';
    }

    $db->query(
        "UPDATE notificacion_cobro
            SET monto_pagado = :mp,
                estado = :est,
                fecha_actualizacion = CURRENT_TIMESTAMP
          WHERE id_notificacion = :n",
        ['mp' => $nuevo_pagado, 'est' => $estado, 'n' => $id_notif]
    );
}

/* ============================ 4) CRÉDITOS (usados y excedentes) ============================ */
$credito_extra = max(0.0, round($total_base_pagado - $total_base_aplicado, 2)); // excedente en base

foreach ($mapaCreditoUsado as $id_moneda => $monto) {
    $monto = (float)$monto;
    if ($monto <= 0) continue;

    $db->query(
        "INSERT INTO credito_a_favor (
            id_propietario, id_moneda, monto, estado, origen, id_inmueble, fecha_creacion
        ) VALUES (
            :p, :m, :mon, 'activo', 'Pago con Crédito', :inm, CURRENT_TIMESTAMP
        )",
        ['p'=>$id_propietario,'m'=>(int)$id_moneda,'mon'=>-$monto,'inm'=>$id_inmueble]
    );
}

if ($credito_extra > 0 && $moneda_credito !== '') {
    $id_moneda_credito = (int)$db->single(
        "SELECT id_moneda FROM moneda WHERE codigo = :cod",
        ['cod' => $moneda_credito]
    );
    if ($id_moneda_credito <= 0) { $db->query('ROLLBACK'); jerr("Moneda de crédito inválida: {$moneda_credito}."); }

    $db->query(
        "INSERT INTO credito_a_favor (
            id_propietario, id_moneda, monto, estado, origen, id_inmueble, fecha_creacion
        ) VALUES (
            :p, :m, :mon, 'activo', 'Excedente de Pago', :inm, CURRENT_TIMESTAMP
        )",
        ['p'=>$id_propietario,'m'=>$id_moneda_credito,'mon'=>$credito_extra,'inm'=>$id_inmueble]
    );
}

/* ============================ 5) RECIBO (cabecera) ============================ */
$correlativo = (int)$db->single(
    "SELECT correlativo_condominio
       FROM recibo_cabecera
      WHERE id_recibo = :r",
    ['r' => $id_recibo]
);
$numero_recibo = sprintf("REC-%04d-%04d", $id_condominio, $correlativo);

$db->query(
    "UPDATE recibo_cabecera
        SET total_pagado = :tp,
            numero_recibo = :nr,
            estado = 'aprobado',
            fecha_actualizacion = CURRENT_TIMESTAMP
      WHERE id_recibo = :r",
    ['tp'=>$total_base_pagado,'nr'=>$numero_recibo,'r'=>$id_recibo]
);

/* ============================ 6) sanity check ============================ */
if (round($total_base_aplicado, 2) > round($total_base_pagado, 2)) {
    $db->query('ROLLBACK');
    jerr("El total aplicado ({$total_base_aplicado}) excede el total pagado ({$total_base_pagado}).");
}

$db->query('COMMIT');

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status'     => 'success',
    'message'    => 'Conciliación aprobada correctamente.',
    'omitidas'   => $omitidas,   // ids de notificaciones con saldo 0
    'ajustadas'  => $ajustadas   // [{id,de,a}] abonos ajustados al saldo
], JSON_UNESCAPED_UNICODE);
exit;
