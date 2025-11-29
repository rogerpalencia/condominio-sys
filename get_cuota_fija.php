<?php
require_once("core/PDO.class.php");
$conn = DB::getInstance();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION)) {
        session_start();
    }

    $id_usuario = $_SESSION['userid'] ?? null;

    if (!$id_usuario) {
        throw new Exception("Usuario no autenticado.");
    }

    // Obtener condominio del usuario
    $sql = "SELECT id_condominio FROM administradores WHERE id_usuario = :id_usuario AND estatus = true LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    $id_condominio = (int) $stmt->fetchColumn();

    if (!$id_condominio) {
        throw new Exception("No se pudo determinar el condominio.");
    }

    // Buscar la cuota vigente
    $sql = "SELECT monto, id_moneda
            FROM cuota_fija_mensual
            WHERE id_condominio = :id_condominio
              AND fecha_desde <= CURRENT_DATE
              AND (fecha_hasta IS NULL OR fecha_hasta > CURRENT_DATE)
            ORDER BY fecha_desde DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
    $stmt->execute();
    $cuota = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cuota) {
        throw new Exception("No hay cuota fija vigente configurada.");
    }

    echo json_encode([
        'status' => 'ok',
        'monto' => $cuota['monto'],
        'id_moneda' => $cuota['id_moneda']
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
