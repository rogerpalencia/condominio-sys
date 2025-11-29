<?php
/*******************************
 *  Archivo: procesar_conciliacion.php
 *  Descripción: Procesa la conciliación de pagos por cuenta, aprobando o rechazando
 *               pagos individuales, actualizando recibo_cabecera, recibo_origen_fondos,
 *               recibo_destino_fondos, notificacion_cobro, y eliminando créditos a favor.
 *  Entrada: id_recibo, id_condominio, id_usuario, accion[], motivo_rechazo[] (POST)
 *  Respuesta: JSON con estado y mensaje para SweetAlert.
 *******************************/

session_start();
require_once 'core/PDO.class.php';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/procesar_conciliacion.log');

error_log('Iniciando procesar_conciliacion.php');

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Error: Método no permitido. Se esperaba POST.');
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

// Validar parámetros
$required_params = ['id_recibo', 'id_condominio', 'id_usuario'];
foreach ($required_params as $param) {
    if (!isset($_POST[$param]) || empty($_POST[$param])) {
        error_log("Error: $param no proporcionado.");
        echo json_encode(['status' => 'error', 'message' => "Parámetro $param no proporcionado."]);
        exit;
    }
}

$id_recibo = (int)$_POST['id_recibo'];
$id_condominio = (int)$_POST['id_condominio'];
$id_usuario = (int)$_POST['id_usuario'];
$acciones = $_POST['accion'] ?? [];
$motivos_rechazo = $_POST['motivo_rechazo'] ?? [];

error_log("Parámetros recibidos - ID Recibo: $id_recibo, ID Condominio: $id_condominio, ID Usuario: $id_usuario");

// Validar sesión
if (!isset($_SESSION['userid']) || (int)$_SESSION['userid'] !== $id_usuario) {
    error_log('Error: Sesión no válida o usuario no autenticado.');
    echo json_encode(['status' => 'error', 'message' => 'Sesión no válida o usuario no autenticado.']);
    exit;
}

$db = DB::getInstance();
if ($db === null) {
    error_log('Error: No se pudo conectar a la base de datos.');
    echo json_encode(['status' => 'error', 'message' => 'No se pudo conectar a la base de datos.']);
    exit;
}

try {
    $db->beginTransaction();

    // Verificar recibo
    $sql = "SELECT estado, id_propietario, id_condominio, monto_total, total_pagado
            FROM recibo_cabecera 
            WHERE id_recibo = :id_recibo FOR UPDATE";
    $recibo = $db->row($sql, ['id_recibo' => $id_recibo]);
    if (!$recibo) {
        throw new Exception('Recibo no encontrado: ID ' . $id_recibo);
    }
    if ($recibo['estado'] !== 'en_revision') {
        throw new Exception('El recibo no está en estado "en_revision". Estado actual: ' . $recibo['estado']);
    }
    if ((int)$recibo['id_condominio'] !== $id_condominio) {
        throw new Exception('El recibo no pertenece al condominio especificado.');
    }

    $id_propietario = (int)$recibo['id_propietario'];
    $nuevo_monto_total = 0;
    $nuevo_total_pagado = 0;
    $pagos_aprobados = 0;
    $pagos_rechazados = 0;

    // Obtener pagos actuales
    $sql = "SELECT id_origen, monto, monto_base, id_moneda
            FROM recibo_origen_fondos 
            WHERE id_recibo = :id_recibo";
    $pagos = $db->query($sql, ['id_recibo' => $id_recibo]);
    if (!$pagos) {
        throw new Exception('No se encontraron pagos para el recibo.');
    }

    // Procesar cada pago
    foreach ($pagos as $pago) {
        $id_origen = (int)$pago['id_origen'];
        $monto = (float)$pago['monto'];
        $monto_base = (float)$pago['monto_base'];
        $id_moneda = (int)$pago['id_moneda'];

        if (!isset($acciones[$id_origen])) {
            throw new Exception('Acción no especificada para el pago ID ' . $id_origen);
        }

        $accion = $acciones[$id_origen];
        if ($accion === 'aprobar') {
            $nuevo_monto_total += $monto_base;
            $nuevo_total_pagado += $monto;
            $pagos_aprobados++;
            error_log("Pago ID $id_origen aprobado: monto=$monto, monto_base=$monto_base");
        } elseif ($accion === 'rechazar') {
            if (!isset($motivos_rechazo[$id_origen]) || empty(trim($motivos_rechazo[$id_origen]))) {
                throw new Exception('Motivo de rechazo no proporcionado para el pago ID ' . $id_origen);
            }
            $motivo = trim($motivos_rechazo[$id_origen]);

            // Eliminar el pago de recibo_origen_fondos
            $sql = "DELETE FROM recibo_origen_fondos WHERE id_origen = :id_origen";
            $db->query($sql, ['id_origen' => $id_origen]);
            error_log("Pago ID $id_origen rechazado: motivo=$motivo");

            // Revertir montos aplicados en recibo_destino_fondos
            $sql = "SELECT id_notificacion, monto_aplicado 
                    FROM recibo_destino_fondos 
                    WHERE id_recibo = :id_recibo AND id_cuenta IN (
                        SELECT id_cuenta FROM recibo_origen_fondos WHERE id_origen = :id_origen
                    )";
            $destinos = $db->query($sql, ['id_recibo' => $id_recibo, 'id_origen' => $id_origen]);
            foreach ($destinos as $destino) {
                $id_notificacion = (int)$destino['id_notificacion'];
                $monto_aplicado = (float)$destino['monto_aplicado'];

                // Actualizar notificacion_cobro
                $sql = "SELECT monto_pagado, monto_x_pagar 
                        FROM notificacion_cobro 
                        WHERE id_notificacion = :id_notificacion FOR UPDATE";
                $notif = $db->row($sql, ['id_notificacion' => $id_notificacion]);
                if (!$notif) {
                    throw new Exception('Notificación no encontrada: ID ' . $id_notificacion);
                }

                $monto_pagado = (float)$notif['monto_pagado'] - $monto_aplicado;
                $monto_x_pagar = (float)$notif['monto_x_pagar'];
                $nuevo_estado = 'pendiente';
                if ($monto_pagado >= $monto_x_pagar) {
                    $nuevo_estado = 'pagada';
                } elseif ($monto_pagado > 0) {
                    $nuevo_estado = 'parcialmente_pagada';
                }

                $sql = "UPDATE notificacion_cobro 
                        SET monto_pagado = :monto_pagado, 
                            estado = :estado, 
                            fecha_actualizacion = NOW()
                        WHERE id_notificacion = :id_notificacion";
                $db->query($sql, [
                    'monto_pagado' => $monto_pagado,
                    'estado' => $nuevo_estado,
                    'id_notificacion' => $id_notificacion
                ]);
                error_log("Notificación ID $id_notificacion actualizada: monto_pagado=$monto_pagado, estado=$nuevo_estado");
            }

            // Eliminar registros de recibo_destino_fondos asociados
            $sql = "DELETE FROM recibo_destino_fondos 
                    WHERE id_recibo = :id_recibo AND id_cuenta IN (
                        SELECT id_cuenta FROM recibo_origen_fondos WHERE id_origen = :id_origen
                    )";
            $db->query($sql, ['id_recibo' => $id_recibo, 'id_origen' => $id_origen]);

            // Eliminar créditos a favor asociados
            $sql = "DELETE FROM credito_a_favor 
                    WHERE id_origen = :id_recibo AND id_propietario = :id_propietario";
            $db->query($sql, ['id_recibo' => $id_recibo, 'id_propietario' => $id_propietario]);
            error_log("Créditos a favor eliminados para recibo ID $id_recibo");

            $pagos_rechazados++;
        }
    }

    // Actualizar recibo_cabecera
    $nuevo_estado = 'en_revision';
    if ($pagos_aprobados > 0 && $pagos_rechazados == 0) {
        $nuevo_estado = 'aprobado';
    } elseif ($pagos_aprobados == 0 && $pagos_rechazados > 0) {
        $nuevo_estado = 'anulado';
    }

    $sql = "UPDATE recibo_cabecera 
            SET monto_total = :monto_total, 
                total_pagado = :total_pagado, 
                estado = :estado, 
                fecha_actualizacion = NOW(), 
                id_usuario = :id_usuario
            WHERE id_recibo = :id_recibo";
    $params = [
        'monto_total' => $nuevo_monto_total,
        'total_pagado' => $nuevo_total_pagado,
        'estado' => $nuevo_estado,
        'id_usuario' => $id_usuario,
        'id_recibo' => $id_recibo
    ];
    $db->query($sql, $params);
    error_log("Recibo ID $id_recibo actualizado: monto_total=$nuevo_monto_total, total_pagado=$nuevo_total_pagado, estado=$nuevo_estado");

    // Si el recibo pasa a aprobado, actualizar notificaciones
    if ($nuevo_estado === 'aprobado') {
        $sql = "SELECT id_notificacion, monto_aplicado 
                FROM recibo_destino_fondos 
                WHERE id_recibo = :id_recibo";
        $notificaciones = $db->query($sql, ['id_recibo' => $id_recibo]);
        foreach ($notificaciones as $notif) {
            $id_notificacion = (int)$notif['id_notificacion'];
            $monto_aplicado = (float)$notif['monto_aplicado'];

            $sql = "SELECT monto_x_pagar, monto_pagado 
                    FROM notificacion_cobro 
                    WHERE id_notificacion = :id_notificacion FOR UPDATE";
            $notif_data = $db->row($sql, ['id_notificacion' => $id_notificacion]);
            if (!$notif_data) {
                throw new Exception('Notificación no encontrada: ID ' . $id_notificacion);
            }

            $monto_x_pagar = (float)$notif_data['monto_x_pagar'];
            $monto_pagado = (float)$notif_data['monto_pagado'];
            $nuevo_monto_pagado = $monto_pagado + $monto_aplicado;

            $nuevo_estado_notif = 'pendiente';
            if ($nuevo_monto_pagado >= $monto_x_pagar) {
                $nuevo_estado_notif = 'pagada';
            } elseif ($nuevo_monto_pagado > 0) {
                $nuevo_estado_notif = 'parcialmente_pagada';
            }

            $sql = "UPDATE notificacion_cobro 
                    SET monto_pagado = :monto_pagado, 
                        estado = :estado, 
                        fecha_actualizacion = NOW()
                    WHERE id_notificacion = :id_notificacion";
            $db->query($sql, [
                'monto_pagado' => $nuevo_monto_pagado,
                'estado' => $nuevo_estado_notif,
                'id_notificacion' => $id_notificacion
            ]);
            error_log("Notificación ID $id_notificacion actualizada: monto_pagado=$nuevo_monto_pagado, estado=$nuevo_estado_notif");
        }
    }

    $db->commit();
    error_log('Transacción completada exitosamente');

    echo json_encode([
        'status' => 'success',
        'message' => 'Conciliación procesada correctamente.'
    ]);
} catch (Exception $e) {
    $db->rollBack();
    error_log('Error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al procesar la conciliación: ' . $e->getMessage()
    ]);
}
?>