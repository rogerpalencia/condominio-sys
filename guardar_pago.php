<?php
@session_start();
require_once 'core/PDO.class.php';

/**
 * guardar_pago.php
 * Registra un nuevo recibo de pago, incluyendo orígenes de fondos (banco/crédito) y destinos (notificaciones).
 * - Valida que el monto aplicado no exceda el saldo pendiente de la notificación.
 * - Usa id_moneda de notificacion_cobro para evitar errores de moneda.
 * - NO actualiza notificacion_cobro.monto_pagado ni estado (se hace en aprobar_fondos.php).
 * - Maneja transacciones para garantizar consistencia.
 */

try {
    $conn = DB::getInstance();
    $conn->beginTransaction();

    // Validar parámetros de entrada
    $id_usuario = (int)$_SESSION['userid'];
    $id_inmueble = (int)$_POST['id_inmueble'];
    $fecha_pago = $_POST['fecha_pago'];
    $observacion = trim($_POST['observacion_pago']);
    $notificaciones = json_decode($_POST['notificaciones'], true);

    if (!$id_inmueble || !$fecha_pago || empty($notificaciones)) {
        throw new Exception("Parámetros inválidos.");
    }

    // Buscar datos del inmueble
    $stmt = $conn->prepare("SELECT c.id_condominio, c.id_moneda AS id_moneda_base, pi.id_propietario
                            FROM inmueble i
                            JOIN condominio c ON i.id_condominio = c.id_condominio
                            JOIN propietario_inmueble pi ON pi.id_inmueble = i.id_inmueble
                            WHERE i.id_inmueble = :id_inmueble
                            LIMIT 1");
    $stmt->execute([':id_inmueble' => $id_inmueble]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception("No se encontró información del inmueble.");
    }

    $id_condominio   = (int)$row['id_condominio'];
    $id_moneda_base  = (int)$row['id_moneda_base'];
    $id_propietario  = (int)$row['id_propietario'];

    // Determinar correlativo
    $stmt = $conn->prepare("SELECT COALESCE(MAX(correlativo_condominio), 0) + 1
                            FROM recibo_cabecera WHERE id_condominio = :id");
    $stmt->execute([':id' => $id_condominio]);
    $correlativo = (int)$stmt->fetchColumn();

    // Insertar en recibo_cabecera
    $stmt = $conn->prepare("INSERT INTO recibo_cabecera (
        id_inmueble, id_condominio, id_propietario, id_usuario,
        fecha_emision, numero_recibo, monto_total, monto_descuento_pronto_pago,
        total_pagado, observaciones, estado, fecha_creacion, fecha_actualizacion,
        correlativo_condominio
    ) VALUES (
        :id_inmueble, :id_condominio, :id_propietario, :id_usuario,
        :fecha_pago, :numero_recibo, 0, 0,
        0, :observaciones, 'en_revision', now(), now(),
        :correlativo
    ) RETURNING id_recibo");

    $stmt->execute([
        ':id_inmueble' => $id_inmueble,
        ':id_condominio' => $id_condominio,
        ':id_propietario' => $id_propietario,
        ':id_usuario' => $id_usuario,
        ':fecha_pago' => $fecha_pago,
        ':numero_recibo' => 'TEMP-' . time(),
        ':observaciones' => $observacion,
        ':correlativo' => $correlativo
    ]);

    $id_recibo = (int)$stmt->fetchColumn();
    $total_base = 0;

    // Guardar pagos desde cuentas bancarias / efectivos / zelle …
    if (!empty($_POST['monto_cuenta'])) {
        foreach ($_POST['monto_cuenta'] as $id_cuenta => $monto) {
            $monto = (float)str_replace(',', '.', $monto);
            if ($monto <= 0) continue;

            $tasa = (float)$_POST['tasa_cambio'][$id_cuenta];
            if ($tasa <= 0) {
                $tasa = 1.0; // Forzar tasa mínima de 1 si es inválida
                error_log("Tasa inválida ($tasa) para cuenta $id_cuenta, usando 1.0 como valor predeterminado.");
            }
            $base = $monto * $tasa;
            $tipo_origen = $_POST['tipo_origen'][$id_cuenta] ?? 'banco'; // ← ahora dinámico

            $conn->prepare("INSERT INTO recibo_origen_fondos (
                id_recibo, id_cuenta, tipo_origen, monto, referencia,
                tasa, monto_base, id_moneda, id_moneda_base, estado, fecha_actualizacion
            ) VALUES (
                :id_recibo, :id_cuenta, :tipo_origen, :monto, :referencia,
                :tasa, :monto_base, :id_moneda, :id_moneda_base, 'en_revision', now()
            )")->execute([
                ':id_recibo'      => $id_recibo,
                ':id_cuenta'      => $id_cuenta,
                ':tipo_origen'    => $tipo_origen,
                ':monto'          => $monto,
                ':referencia'     => $_POST['referencia'][$id_cuenta] ?? '',
                ':tasa'           => $tasa,
                ':monto_base'     => $base,
                ':id_moneda'      => obtenerMonedaCuenta($id_cuenta, $conn),
                ':id_moneda_base' => $id_moneda_base
            ]);

            $total_base += $base;
        }
    }

    // Guardar créditos usados
    if (!empty($_POST['monto_credito'])) {
        foreach ($_POST['monto_credito'] as $id_moneda => $monto) {
            $monto = (float)str_replace(',', '.', $monto);
            if ($monto <= 0) continue;

            $tasa = (float)$_POST['tasa_cambio_credito'][$id_moneda]; // Corregido de 'tasa_credito' a 'tasa_cambio_credito'
            if ($tasa <= 0) {
                $tasa = 1.0; // Forzar tasa mínima de 1 si es inválida
                error_log("Tasa inválida ($tasa) para crédito $id_moneda, usando 1.0 como valor predeterminado.");
            }
            $base = $monto * $tasa;

            $conn->prepare("INSERT INTO recibo_origen_fondos (
                id_recibo, tipo_origen, monto, tasa, monto_base,
                id_moneda, id_moneda_base, estado, fecha_actualizacion
            ) VALUES (
                :id_recibo, 'credito', :monto, :tasa, :monto_base,
                :id_moneda, :id_moneda_base, 'en_revision', now()
            )")->execute([
                ':id_recibo'      => $id_recibo,
                ':monto'          => $monto,
                ':tasa'           => $tasa,
                ':monto_base'     => $base,
                ':id_moneda'      => $id_moneda,
                ':id_moneda_base' => $id_moneda_base
            ]);

            $total_base += $base;
        }
    }

    // Guardar destino (aplicaciones a notificaciones)
    if (!empty($notificaciones)) {
        foreach ($notificaciones as $n) {
            $id_notificacion = (int)$n['id_notificacion'];
            $monto_aplicado  = (float)str_replace(',', '.', $n['abono']);
            $tasa            = (float)$n['tasa'];
            if ($monto_aplicado <= 0) continue;

            // Obtener id_moneda, monto_x_pagar y monto_pagado de la notificación
            $stmt = $conn->prepare("SELECT id_moneda, monto_x_pagar, COALESCE(monto_pagado, 0) AS monto_pagado
                                    FROM notificacion_cobro 
                                    WHERE id_notificacion = :id");
            $stmt->execute([':id' => $id_notificacion]);
            $notificacion = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$notificacion) {
                throw new Exception("No se encontró la notificación {$id_notificacion}.");
            }

            $id_moneda = (int)$notificacion['id_moneda'];
            $monto_x_pagar = (float)$notificacion['monto_x_pagar'];
            $monto_pagado = (float)$notificacion['monto_pagado'];

            // Validar que el monto aplicado no exceda el saldo pendiente
            $saldo_pendiente = $monto_x_pagar ;
            if ($monto_aplicado > $saldo_pendiente) {
                throw new Exception("El monto aplicado ({$monto_aplicado}) excede el saldo pendiente ({$saldo_pendiente}) de la notificación {$id_notificacion}.");
            }

            $monto_base = $monto_aplicado * $tasa;

            // Insertar en recibo_destino_fondos
            $conn->prepare("INSERT INTO recibo_destino_fondos (
                id_recibo, id_notificacion, monto_aplicado,
                id_moneda, id_moneda_base, tasa, monto_base
            ) VALUES (
                :id_recibo, :id_notificacion, :monto_aplicado,
                :id_moneda, :id_moneda_base, :tasa, :monto_base
            )")->execute([
                ':id_recibo'        => $id_recibo,
                ':id_notificacion'  => $id_notificacion,
                ':monto_aplicado'   => $monto_aplicado,
                ':id_moneda'        => $id_moneda,
                ':id_moneda_base'   => $id_moneda_base,
                ':tasa'             => $tasa,
                ':monto_base'       => $monto_base
            ]);
        }
    }

    // Actualizar total pagado en recibo_cabecera
    $conn->prepare("UPDATE recibo_cabecera SET total_pagado = :total WHERE id_recibo = :id")
        ->execute([
            ':total' => round($total_base, 2),
            ':id' => $id_recibo
        ]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Pago registrado correctamente.']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}

/**
 * Obtiene el id_moneda de una cuenta bancaria.
 */
function obtenerMonedaCuenta($id_cuenta, $conn) {
    $stmt = $conn->prepare("SELECT id_moneda FROM cuenta WHERE id_cuenta = :id");
    $stmt->execute([':id' => $id_cuenta]);
    return (int)$stmt->fetchColumn();
}
?>