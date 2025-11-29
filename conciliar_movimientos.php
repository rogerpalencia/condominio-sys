<?php
require_once 'core/PDO.class.php';

try {
    $conn = DB::getInstance();
    if (!$conn) throw new Exception('No se pudo conectar a la base de datos');

    $id_movimiento = (int)($_POST['id_movimiento'] ?? 0);
    $id_condominio = (int)($_POST['id_condominio'] ?? 0);
    $mes_contable = $_POST['mes_contable'] ?? '';
    $anio_contable = $_POST['anio_contable'] ?? '';

    if ($id_movimiento <= 0 || $id_condominio <= 0 || !$mes_contable || !$anio_contable) {
        throw new Exception('Datos incompletos para conciliar el movimiento.');
    }

    $sql = "UPDATE movimiento_general 
            SET estado = 'conciliado', mes_contable = :mes, anio_contable = :anio 
            WHERE id_movimiento = :id AND id_condominio = :id_condominio";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id' => $id_movimiento,
        ':id_condominio' => $id_condominio,
        ':mes' => $mes_contable,
        ':anio' => $anio_contable
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No se pudo conciliar el movimiento o ya está conciliado.');
    }

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'message' => 'Movimiento conciliado']);
} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}