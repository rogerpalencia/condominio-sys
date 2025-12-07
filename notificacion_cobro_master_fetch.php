<?php
//notificacion_cobro_master_fetch
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn || !$conn->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
        throw new Exception('No se pudo establecer la conexión a la base de datos');
    }

    $id_notificacion = (int)($_POST['id_notificacion_master'] ?? 0);
    if ($id_notificacion <= 0) {
        throw new Exception('ID de notificación inválido');
    }

    // Obtener datos de la cabecera
    $sql = "SELECT id_condominio, anio, mes, fecha_emision, fecha_vencimiento,
                   monto_total, estado, id_moneda, descripcion, activa, id_tipo
            FROM notificacion_cobro_master
            WHERE id_notificacion_master = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_notificacion]);
    $master = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$master) {
        throw new Exception('Notificación no encontrada');
    }

    // Obtener detalles con el tipo de movimiento
    $sql = "SELECT d.id_plan_cuenta, d.id_cuenta, d.descripcion, d.monto, d.tipo_movimiento,
                   d.fecha_pago, d.referencia_pago
            FROM notificacion_cobro_detalle_master d
            WHERE d.id_notificacion_master = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_notificacion]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'data' => array_merge($master, ['detalles' => $detalles])
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
