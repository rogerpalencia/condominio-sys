<?php
//notificacion_cobro_insert.php
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    $conn->beginTransaction();

    // Validar datos base
    $id_condominio = (int) $_POST['id_condominio'];
    $id_inmueble = (int) $_POST['id_inmueble'];
    $id_moneda = (int) $_POST['id_moneda'];
    $fecha_emision = $_POST['fecha_emision'];
    $fecha_limite_pago = $_POST['fecha_limite_pago'];
    $descripcion_cab = strtoupper($_POST['descripcion_cab']);
    $cuentas = $_POST['id_plan_cuenta'] ?? [];
    $descripciones = $_POST['descripcion'] ?? [];
    $montos = $_POST['monto'] ?? [];

    if (empty($cuentas) || count($cuentas) !== count($descripciones) || count($cuentas) !== count($montos)) {
        throw new Exception("Los datos de presupuesto están incompletos.");
    }

    // Insertar cabecera
    $total_presupuesto = 0;
    foreach ($montos as $monto) {
        $monto_float = floatval(str_replace(',', '.', $monto));
        $total_presupuesto += $monto_float;
    }

    $sql_cab = "INSERT INTO notificacion_cobro (
        id_condominio, id_inmueble, id_moneda, fecha_emision, fecha_vencimiento, monto_total,monto_x_pagar, descripcion, estado, fecha_creacion, fecha_actualizacion
    ) VALUES (
        :id_condominio, :id_inmueble, :id_moneda, :fecha_emision, :fecha_limite_pago, :total,:total, :descripcion, 'pendiente', NOW(), NOW()
    ) RETURNING id_notificacion";

    $stmt_cab = $conn->prepare($sql_cab);
    $stmt_cab->execute([
        ':id_condominio' => $id_condominio,
        ':id_inmueble' => $id_inmueble,
        ':id_moneda' => $id_moneda,
        ':fecha_emision' => $fecha_emision,
        ':fecha_limite_pago' => $fecha_limite_pago,
        ':total' => $total_presupuesto,
        ':descripcion' => $descripcion_cab
    ]);

    $id_notificacion = $stmt_cab->fetchColumn();
    if (!$id_notificacion) {
        throw new Exception("No se pudo obtener el ID de la notificación generada.");
    }

    // Insertar detalles
    $sql_det = "INSERT INTO notificacion_cobro_detalle (
        id_notificacion, id_plan_cuenta, descripcion, monto, id_condominio, id_inmueble, estado
    ) VALUES (
        :id_notificacion, :id_plan_cuenta, :descripcion, :monto, :id_condominio, :id_inmueble, 'pendiente'
    )";

    $stmt_det = $conn->prepare($sql_det);

    for ($i = 0; $i < count($cuentas); $i++) {
        $stmt_det->execute([
            ':id_notificacion' => $id_notificacion,
            ':id_plan_cuenta' => (int) $cuentas[$i],
            ':descripcion' => strtoupper($descripciones[$i]),
            ':monto' => floatval(str_replace(',', '.', $montos[$i])),
            ':id_condominio' => $id_condominio,
            ':id_inmueble' => $id_inmueble
        ]);
    }

    $conn->commit();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>