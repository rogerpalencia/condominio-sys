<?php
require_once 'core/PDO.class.php';

try {
    $conn = DB::getInstance();
    if (!$conn) throw new Exception('No se pudo conectar a la base de datos');

    $id_movimiento = (int)($_POST['id_movimiento'] ?? 0);
    $id_condominio = (int)($_POST['id_condominio'] ?? 0);

    if ($id_movimiento <= 0 || $id_condominio <= 0) {
        throw new Exception('ID de movimiento o condominio no proporcionado.');
    }

    // Verificar que no esté conciliado
    $sql = "SELECT estado FROM movimiento_general WHERE id_movimiento = :id AND id_condominio = :id_condominio";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_movimiento, ':id_condominio' => $id_condominio]);
    $estado = $stmt->fetchColumn();

    if ($estado === 'conciliado') {
        throw new Exception('No se puede eliminar un movimiento conciliado.');
    }

    $sql = "DELETE FROM movimiento_general WHERE id_movimiento = :id AND id_condominio = :id_condominio";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_movimiento, ':id_condominio' => $id_condominio]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Movimiento no encontrado o ya eliminado.');
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'message' => 'Movimiento eliminado']);
} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}