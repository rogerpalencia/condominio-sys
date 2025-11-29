<?php
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn || !$conn->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
        throw new Exception('No se pudo establecer la conexión a la base de datos');
    }

    $conn->beginTransaction();

    // Validar y sanitizar datos de entrada
    $required = ['id_condominio', 'id_moneda', 'anio', 'fecha_emision', 'fecha_vencimiento', 'descripcion_cab', 'id_tipo'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("El campo $field es obligatorio");
        }
    }

    $id_condo = (int)$_POST['id_condominio'];
    $id_mon = (int)$_POST['id_moneda'];
    $anio = (int)$_POST['anio'];
    $mes = (int)$_POST['mes'];
    
    $femi = date('Y-m-d', strtotime($_POST['fecha_emision']));
    $fven = date('Y-m-d', strtotime($_POST['fecha_vencimiento']));
    $nu = date('Y-m-d', strtotime($_POST['fecha_emision']));
    $desc = strtoupper(trim($_POST['descripcion_cab']));
    $id_tipo = (int)$_POST['id_tipo'];

    // Validaciones según restricciones
    if ($mes < 0 || $mes > 12) {
        throw new Exception('El mes debe estar entre 0 y 12');
    }
    if ($anio < 2000) {
        throw new Exception('El año debe ser mayor o igual a 2000');
    }
    if ($fven < $femi) {
        throw new Exception('La fecha de vencimiento debe ser mayor o igual a la fecha de emisión');
    }
    if (!in_array($id_tipo, [1, 2])) {
        throw new Exception('El tipo de notificación debe ser 1 o 2');
    }

    // Validar claves foráneas
    $sql_check = "SELECT 1 FROM condominio WHERE id_condominio = :c";
    $stmt = $conn->prepare($sql_check);
    $stmt->execute([':c' => $id_condo]);
    if (!$stmt->fetchColumn()) {
        throw new Exception('Condominio no encontrado');
    }

    $sql_check = "SELECT 1 FROM moneda WHERE id_moneda = :m";
    $stmt = $conn->prepare($sql_check);
    $stmt->execute([':m' => $id_mon]);
    if (!$stmt->fetchColumn()) {
        throw new Exception('Moneda no encontrada');
    }

    // Verificar restricción única para estado 'emitida' por tipo
    $sql_check = "SELECT 1 FROM notificacion_cobro_master 
                  WHERE id_condominio = :c AND anio = :a AND mes = :m AND id_tipo = :t AND estado = 'emitida'";
    $stmt = $conn->prepare($sql_check);
    $stmt->execute([':c' => $id_condo, ':a' => $anio, ':m' => $mes, ':t' => $id_tipo]);
    if ($stmt->fetchColumn()) {
        throw new Exception('Ya existe una notificación emitida para este condominio, año, mes y tipo');
    }

    // Procesar detalles
    $cuentas = $_POST['id_plan_cuenta'] ?? [];
    $descs = $_POST['descripcion'] ?? [];
    $montos = $_POST['monto'] ?? [];
    if (count($cuentas) !== count($descs) || count($cuentas) !== count($montos)) {
        throw new Exception('El número de cuentas, descripciones y montos no coincide');
    }

    $total = 0;
    foreach ($cuentas as $i => $id_plan) {
        $monto = floatval(str_replace(',', '.', $montos[$i]));
    //    if ($monto <= 0) {
    //        throw new Exception('Los montos deben ser mayores a 0');
    //    }
        // Obtener el tipo de cuenta
        $sql = "SELECT tipo FROM plan_cuenta WHERE id_plan = :id AND id_condominio = :c";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => (int)$id_plan, ':c' => $id_condo]);
        $tipo = $stmt->fetchColumn();
        if (!$tipo) {
            throw new Exception("Cuenta no encontrada para id_plan_cuenta: $id_plan");
        }
        $total += $monto;
    }

   // if ($id_tipo == 1 && $total <= 0) {
   //     throw new Exception('El monto total debe ser mayor a 0 para presupuestos');
  //  }

    // Insertar cabecera
    $sql = "INSERT INTO notificacion_cobro_master (
        id_condominio, anio, mes, fecha_emision, fecha_vencimiento,
        monto_total, estado, id_moneda, descripcion, id_tipo,
        fecha_creacion, fecha_actualizacion
    ) VALUES (
        :c, :a, :m, :fe, :fv, :tot, 'pendiente', :mon, :d, :tipo, NOW(), NOW()
    ) RETURNING id_notificacion_master";
    $stmt = $conn->prepare($sql);
    $params = [
        ':c' => $id_condo,
        ':a' => $anio,
        ':m' => $mes,
        ':fe' => $femi,
        ':fv' => $fven,
        ':tot' => $total,
        ':mon' => $id_mon,
        ':d' => $desc,
        ':tipo' => $id_tipo
    ];
    $stmt->execute($params);
    $id_master = $stmt->fetchColumn();

    // Insertar detalles
    $sqlDet = "INSERT INTO notificacion_cobro_detalle_master (
        id_notificacion_master, id_plan_cuenta, descripcion, monto,
        id_condominio, anio, mes, estado, id_moneda, tipo_movimiento
    ) VALUES (
        :master, :plan, :desc, :monto, :cond, :anio, :mes, 'pendiente', :mon, :tipo
    )";
    $stmt = $conn->prepare($sqlDet);
    for ($i = 0; $i < count($cuentas); $i++) {
        // Obtener el tipo de cuenta
        $sql = "SELECT tipo FROM plan_cuenta WHERE id_plan = :id AND id_condominio = :c";
        $stmtTipo = $conn->prepare($sql);
        $stmtTipo->execute([':id' => (int)$cuentas[$i], ':c' => $id_condo]);
        $tipo = $stmtTipo->fetchColumn();
        if (!$tipo) {
            throw new Exception("Cuenta no encontrada para id_plan_cuenta: {$cuentas[$i]}");
        }
        $paramsDet = [
            ':master' => $id_master,
            ':plan' => (int)$cuentas[$i],
            ':desc' => strtoupper(trim($descs[$i])),
            ':monto' => floatval(str_replace(',', '.', $montos[$i])),
            ':cond' => $id_condo,
            ':anio' => $anio,
            ':mes' => $mes,
            ':mon' => $id_mon,
            ':tipo' => $tipo
        ];
        $stmt->execute($paramsDet);
    }

    $conn->commit();
    echo json_encode(['status' => 'ok', 'id' => $id_master]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>