<?php
@session_start();
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    $id_movimiento = (int)($_POST['id'] ?? 0);
    $mes_contable  = trim($_POST['mes_contable'] ?? '');
    $anio_contable = trim($_POST['anio_contable'] ?? '');
    $id_condominio = (int)($_SESSION['id_condominio'] ?? 0);

    if ($id_movimiento <= 0 || $id_condominio <= 0 || $mes_contable === '' || $anio_contable === '') {
        throw new Exception('Datos incompletos');
    }

    $sql = "UPDATE movimiento_general
            SET mes_contable = :mes, anio_contable = :anio
            WHERE id_movimiento = :id AND id_condominio = :condominio";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':mes' => $mes_contable,
        ':anio' => $anio_contable,
        ':id' => $id_movimiento,
        ':condominio' => $id_condominio
    ]);

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
