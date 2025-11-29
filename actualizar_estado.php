<?php
session_start();
require_once("core/PDO.class.php");

try {
    $conn = DB::getInstance();
    $conn->beginTransaction();

    $id_movimiento = $_POST['id_movimiento'] ?? null;
    $estado = $_POST['estado'] ?? null;

    if (!$id_movimiento || !$estado) {
        throw new Exception('ID de movimiento y estado son requeridos.');
    }

    // Verificar estado actual
    $stmt = $conn->prepare("SELECT estado FROM movimientos_caja_banco WHERE id_movimiento = :id");
    $stmt->execute([':id' => $id_movimiento]);
    $currentEstado = $stmt->fetchColumn();

    if ($currentEstado === 'cerrado') {
        throw new Exception('No se puede modificar un movimiento cerrado.');
    }

    if (($estado === 'aprobado' && $currentEstado !== 'pendiente') ||
        ($estado === 'cerrado' && $currentEstado !== 'aprobado') ||
        ($estado === 'anulado' && $currentEstado !== 'pendiente')) {
        throw new Exception('Transición de estado no permitida.');
    }

    // Actualizar estado
    $stmt = $conn->prepare("UPDATE movimientos_caja_banco SET estado = :estado, fecha_actualizacion = NOW() WHERE id_movimiento = :id");
    $stmt->execute([':estado' => $estado, ':id' => $id_movimiento]);

    $conn->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Estado actualizado correctamente.']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}