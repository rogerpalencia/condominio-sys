<?php
require_once("core/PDO.class.php");
$conn = DB::getInstance();
header('Content-Type: application/json');

try {
    if (
        empty($_POST['id_condominio']) || empty($_POST['mes']) || empty($_POST['anio']) ||
        empty($_POST['tipo_cuota']) || empty($_POST['cuenta']) || empty($_POST['descripcion']) || empty($_POST['monto'])
    ) {
        throw new Exception("Faltan campos obligatorios.");
    }

    $id_condominio = (int)$_POST['id_condominio'];
    $mes = (int)$_POST['mes'];
    $anio = (int)$_POST['anio'];
    $tipo = $_POST['tipo_cuota'];
    $cuentas = $_POST['id_cuenta'];
    $descripciones = $_POST['descripcion'];
    $montos = $_POST['monto'];
    $total = floatval(str_replace(',', '.', $_POST['total_presupuesto'] ?? 0));

    if ($total <= 0 || count($cuentas) === 0) {
        throw new Exception("Presupuesto inválido.");
    }

    // Iniciar transacción
    $conn->beginTransaction();

    // Eliminar presupuesto anterior (si existe)
    $stmt = $conn->prepare("SELECT id_notificacion FROM notificacion_cobro 
                            WHERE id_condominio = :condominio AND mes = :mes AND anio = :anio 
                              AND id_tipo = 1 AND id_inmueble IS NULL");
    $stmt->execute([':condominio' => $id_condominio, ':mes' => $mes, ':anio' => $anio]);
    $id_antiguo = $stmt->fetchColumn();

    if ($id_antiguo) {
        $conn->prepare("DELETE FROM notificacion_cobro_detalle WHERE id_notificacion = :id")
             ->execute([':id' => $id_antiguo]);

        $conn->prepare("DELETE FROM notificacion_cobro WHERE id_notificacion = :id")
             ->execute([':id' => $id_antiguo]);
    }

    // Insertar cabecera
    $sqlCab = "INSERT INTO notificacion_cobro (
                    id_condominio, id_tipo, mes, anio, estado, 
                    fecha_emision, fecha_vencimiento, id_moneda, 
                    monto_total, pronto_pago, fecha_creacion, fecha_actualizacion, id_inmueble
               ) VALUES (
                    :condominio, 1, :mes, :anio, 'pendiente',
                    CURRENT_DATE, CURRENT_DATE, 1,
                    :monto_total, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, NULL
               ) RETURNING id_notificacion";

    $stmtCab = $conn->prepare($sqlCab);
    $stmtCab->execute([
        ':condominio' => $id_condominio,
        ':mes' => $mes,
        ':anio' => $anio,
        ':monto_total' => $total
    ]);
    $id_presupuesto = $stmtCab->fetchColumn();

    // Insertar detalles
    $sqlDet = "INSERT INTO notificacion_cobro_detalle (id_notificacion, id_plan_cuenta, descripcion, monto)
               VALUES (:id_notificacion, :id_plan_cuenta, :descripcion, :monto)";
    $stmtDet = $conn->prepare($sqlDet);

    for ($i = 0; $i < count($cuentas); $i++) {
        $cuenta = $cuentas[$i];
        $descripcion = $descripciones[$i];
        $monto = floatval(str_replace(',', '.', $montos[$i]));

        if ($cuenta && $monto > 0) {
            $stmtDet->execute([
                ':id_notificacion' => $id_presupuesto,
                ':id_plan_cuenta' => $cuenta,
                ':descripcion' => $descripcion,
                ':monto' => $monto
            ]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'ok', 'message' => 'Presupuesto guardado correctamente.']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'error inser_presupuesto'. $e->getMessage()]);
}
