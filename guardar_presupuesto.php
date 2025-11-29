<?php
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    $conn->beginTransaction();

    $id_condominio = (int) $_POST['id_condominio'];
    $mes           = (int) $_POST['mes'];
    $anio          = (int) $_POST['anio'];
    $tipo          = $_POST['tipo_cuota'];
    $id_moneda = (int) $_POST['id_moneda'];

    $cuentas       = $_POST['id_plan_cuenta'] ?? $_POST['cuenta']; // puede llegar como 'cuenta' o 'id_plan_cuenta'
    $descripciones = $_POST['descripcion'];
    $montos        = $_POST['monto'];

    // Validar campos básicos
    if (!$id_condominio || !$mes || !$anio || empty($cuentas)) {
        throw new Exception("Datos incompletos para guardar el presupuesto.");
    }

    // Eliminar anteriores
    $stmtDel = $conn->prepare("DELETE FROM notificacion_cobro WHERE id_condominio = :id AND anio = :anio AND mes = :mes AND id_tipo = 1 AND id_inmueble IS NULL");
    $stmtDel->execute([':id' => $id_condominio, ':anio' => $anio, ':mes' => $mes]);

    // Insertar cabecera de notificación de cobro (sin inmueble)
    $stmtCab = $conn->prepare("
INSERT INTO notificacion_cobro (
    id_condominio, anio, mes, estado, id_tipo, id_moneda, fecha_creacion,descripcion
) VALUES (
    :id_condominio, :anio, :mes, 'pendiente', 1, :id_moneda, CURRENT_TIMESTAMP,:descripcion
) RETURNING id_notificacion
    ");

    $stmtCab->execute([
        ':id_condominio' => $id_condominio,
        ':anio'  => $anio,
        ':mes'   => $mes,
        ':id_moneda'=> $id_moneda,
        ':descripcion' => 'Presupuesto de gastos'.' '.$mes.'/'.$anio
    ]);

    $id_notificacion = $stmtCab->fetchColumn();
    if (!$id_notificacion) {
        throw new Exception("No se pudo insertar la cabecera del presupuesto.");
    }

    // Insertar detalle
    $stmtDet = $conn->prepare("
        INSERT INTO notificacion_cobro_detalle (
            id_notificacion, id_plan_cuenta, descripcion, monto, id_condominio, anio, mes, estado, id_tipo
        ) VALUES (
            :id_notificacion, :id_plan_cuenta, :descripcion, :monto, :id_condominio, :anio, :mes, 'pendiente', 1
        )
    ");

    foreach ($cuentas as $i => $id_cuenta) {
        $id_cuenta = is_numeric($id_cuenta) ? (int) $id_cuenta : null;
        $desc      = strtoupper(trim($descripciones[$i]));
        $monto     = floatval(str_replace(',', '.', $montos[$i]));

        if ($id_cuenta && $monto > 0) {
            $stmtDet->execute([
                ':id_notificacion' => $id_notificacion,
                ':id_plan_cuenta'  => $id_cuenta,
                ':descripcion'     => $desc,
                ':monto'           => $monto,
                ':id_condominio'   => $id_condominio,
                ':anio'            => $anio,
                ':mes'             => $mes
            ]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
