<?php
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn || !$conn->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
        throw new Exception('No se pudo establecer la conexión a la base de datos');
    }

    $conn->beginTransaction();

    $id_notificacion = (int)$_POST['id_notificacion_master'] ?? 0;
    if ($id_notificacion <= 0) {
        throw new Exception('ID de notificación inválido');
    }

    // Check if notification exists and is not approved
    $sql = "SELECT estado FROM notificacion_cobro_master WHERE id_notificacion_master = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_notificacion]);
    $estado = $stmt->fetchColumn();
    if (!$estado) {
        throw new Exception('Notificación no encontrada');
    }
    if ($estado === 'emitida') {
        throw new Exception('No se puede eliminar una notificación emitida');
    }

    // Delete details
    $sql = "DELETE FROM notificacion_cobro_detalle_master WHERE id_notificacion_master = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_notificacion]);

    // Delete master
    $sql = "DELETE FROM notificacion_cobro_master WHERE id_notificacion_master = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_notificacion]);

    $conn->commit();
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}