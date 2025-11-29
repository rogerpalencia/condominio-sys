<?php
header('Content-Type: application/json');
require_once 'core/PDO.class.php';
require_once 'core/funciones.php'; // Si es necesario para otras funciones

// Iniciar conexión
$conn = DB::getInstance();
if (!$conn) {
    echo json_encode(['error' => 'No se pudo conectar a la base de datos']);
    exit;
}
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Validar parámetro
$idInmueble = isset($_POST['id_inmueble']) ? (int)$_POST['id_inmueble'] : 0;
if ($idInmueble === 0) {
    echo '[]';
    exit;
}
error_log("id_inmueble recibido: $idInmueble");

// Obtener moneda base
$sqlBase = "SELECT c.id_moneda
            FROM condominio c
            JOIN inmueble i ON i.id_condominio = c.id_condominio
            WHERE i.id_inmueble = :id";
try {
    $stmtBase = $conn->prepare($sqlBase);
    $stmtBase->execute([':id' => $idInmueble]);
    $idMonBase = (int)$stmtBase->fetchColumn();
    if ($idMonBase === 0) {
        error_log("No se encontró moneda base para id_inmueble: $idInmueble");
        echo json_encode(['error' => 'No se encontró moneda base para el inmueble']);
        exit;
    }
} catch (PDOException $e) {
    error_log("Error en consulta de moneda base: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener moneda base: ' . $e->getMessage()]);
    exit;
}

// Obtener notificaciones pendientes
$sql = "
SELECT
    n.id_notificacion,
    n.descripcion,
    TO_CHAR(n.fecha_emision, 'YYYY-MM-DD') AS fecha_emision,
    n.id_moneda,
    m.codigo AS codigo_moneda,
    n.monto_x_pagar,
    CASE
      WHEN n.id_moneda = :base THEN 1::numeric
      ELSE COALESCE(
        (SELECT tc.tasa
           FROM tipo_cambio tc
          WHERE tc.id_moneda_origen = n.id_moneda
            AND tc.id_moneda_destino = :base
          ORDER BY tc.fecha_vigencia DESC
          LIMIT 1),
        (SELECT 1 / NULLIF(tc2.tasa, 0)
           FROM tipo_cambio tc2
          WHERE tc2.id_moneda_origen = :base
            AND tc2.id_moneda_destino = n.id_moneda
          ORDER BY tc2.fecha_vigencia DESC
          LIMIT 1),
        1::numeric)
    END AS tasa
FROM notificacion_cobro n
JOIN moneda m ON m.id_moneda = n.id_moneda
WHERE n.id_inmueble = :id
  AND n.monto_x_pagar > 0
ORDER BY n.fecha_emision asc";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id'   => $idInmueble,
        ':base' => $idMonBase
    ]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Notificaciones encontradas: " . count($result));
    echo json_encode($result);
} catch (PDOException $e) {
    error_log("Error en consulta de notificaciones: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener notificaciones: ' . $e->getMessage()]);
    exit;
}
?>