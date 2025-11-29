<?php
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    $origen = $_GET['origen'] ?? null;
    $destino = $_GET['destino'] ?? null;

    if (!$origen || !$destino) {
        echo json_encode(['tasa' => 1.0]);
        exit;
    }

    $sql = "SELECT tasa FROM tipo_cambio 
            WHERE id_moneda_origen = :origen AND id_moneda_destino = :destino 
            AND fecha_vigencia <= CURRENT_TIMESTAMP 
            ORDER BY fecha_actualizacion DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':origen' => $origen, ':destino' => $destino]);
    $tasa = $stmt->fetchColumn();

    if ($tasa === false) {
        // Intentar con la inversa y calcular la inversa
        $sql_inversa = "SELECT tasa FROM tipo_cambio 
                        WHERE id_moneda_origen = :destino AND id_moneda_destino = :origen 
                        AND fecha_vigencia <= CURRENT_TIMESTAMP 
                        ORDER BY fecha_actualizacion DESC LIMIT 1";
        $stmt_inversa = $conn->prepare($sql_inversa);
        $stmt_inversa->execute([':destino' => $destino, ':origen' => $origen]);
        $tasa_inversa = $stmt_inversa->fetchColumn();
        $tasa = $tasa_inversa ? 1.0 / $tasa_inversa : 1.0;
    }

    echo json_encode(['tasa' => $tasa]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}