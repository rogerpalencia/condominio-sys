<?php
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    $id_cuenta = $_GET['id_cuenta'] ?? 0;
    $sql = "SELECT id_moneda FROM cuenta WHERE id_cuenta = :id_cuenta";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id_cuenta' => $id_cuenta]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row ?: ['id_moneda' => null]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}