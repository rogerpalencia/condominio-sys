<?php
// guardar_movimiento_real.php
// version 1.3 (07-2025)

@session_start();
require_once 'core/PDO.class.php';
require_once 'core/funciones.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn) throw new Exception('No se pudo conectar a la base de datos');

    /* === 1. Datos === */
    $id_movimiento  = (int)($_POST['id_movimiento']  ?? 0);
    $id_condominio  = (int)($_POST['id_condominio']  ?? 0);
    $fecha          = $_POST['fecha_movimiento']     ?? date('Y-m-d');
    $tipo           = $_POST['tipo_movimiento']      ?? '';
    $descripcion    = trim($_POST['descripcion']     ?? '');
    $estado         = $_POST['estado']               ?? 'pendiente';
    $id_plan_cuenta = (int)($_POST['id_plan_cuenta'] ?? 0);
    $mes_contable   = trim($_POST['mes_contable']    ?? '');
    $anio_contable  = trim($_POST['anio_contable']   ?? '');

    if ($mes_contable === '') $mes_contable = date('m');
    if ($anio_contable === '') $anio_contable = date('Y');

    if ($id_condominio<=0 || !$fecha || !$tipo || !$id_plan_cuenta || !$mes_contable || !$anio_contable)
        throw new Exception('Datos incompletos.');

    /* === 2. Reconstruir detalles === */
    if (empty($_POST['detalles']['id_cuenta']))
        throw new Exception('Detalles vacíos.');

    $raw = $_POST['detalles'];
    $n   = count($raw['id_cuenta']);
    $detalles = [];
    for ($i=0;$i<$n;$i++) $detalles[] = [
        'id_cuenta'   => (int)$raw['id_cuenta'][$i],
        'id_moneda'   => (int)$raw['id_moneda'][$i],
        'monto'       => (float)$raw['monto'][$i],
        'tasa'        => (float)$raw['tasa'][$i],
        'monto_base'  => (float)$raw['monto_base'][$i],
        'referencia'  => trim($raw['referencia'][$i])
    ];

    $id_cuenta_enc = $detalles[0]['id_cuenta'];
    $id_moneda_enc = $detalles[0]['id_moneda'];
    if ($id_cuenta_enc<=0 || $id_moneda_enc<=0)
        throw new Exception('Cuenta/moneda del primer detalle inválida.');

    $monto_total = array_sum(array_column($detalles,'monto_base'));
    if ($monto_total<=0) throw new Exception('Monto total cero.');

    /* === 3. Transacción === */
    $conn->beginTransaction();

    if ($id_movimiento>0) {
        /* 3a. Actualizar encabezado */
        $stmt = $conn->prepare(
            "SELECT tipo_movimiento FROM movimiento_general
             WHERE id_movimiento=:id AND id_condominio=:cid");
        $stmt->execute([':id'=>$id_movimiento,':cid'=>$id_condominio]);
        $tipo_anterior = $stmt->fetchColumn();
        if (!$tipo_anterior) throw new Exception('Movimiento no existe.');

        $tabla = ($tipo_anterior==='ingreso')
               ? 'movimiento_detalle_ingreso'
               : 'movimiento_detalle_egreso';
        $conn->prepare("DELETE FROM $tabla WHERE id_movimiento_general=:id")
             ->execute([':id'=>$id_movimiento]);

        $sql = "UPDATE movimiento_general SET
                   fecha_movimiento=:fecha, tipo_movimiento=:tipo,
                   descripcion=:desc, monto_total=:monto, estado=:estado,
                   id_cuenta=:cu, id_moneda=:mo,
                   mes_contable=:mes, anio_contable=:anio
                WHERE id_movimiento=:id AND id_condominio=:cid";
        $conn->prepare($sql)->execute([
            ':fecha'=>$fecha, ':tipo'=>$tipo, ':desc'=>$descripcion,
            ':monto'=>$monto_total, ':estado'=>$estado,
            ':cu'=>$id_cuenta_enc, ':mo'=>$id_moneda_enc,
            ':mes'=>$mes_contable, ':anio'=>$anio_contable,
            ':id'=>$id_movimiento, ':cid'=>$id_condominio
        ]);

    } else {
        /* 3b. Insertar encabezado nuevo */
        $sql = "INSERT INTO movimiento_general
                (id_condominio,fecha_movimiento,tipo_movimiento,
                 descripcion,monto_total,estado,fecha_creacion,
                 id_cuenta,id_moneda,mes_contable,anio_contable)
                VALUES (:cid,:fecha,:tipo,:desc,:monto,:estado,
                        CURRENT_TIMESTAMP,:cu,:mo,:mes,:anio)
                RETURNING id_movimiento";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':cid'=>$id_condominio, ':fecha'=>$fecha, ':tipo'=>$tipo,
            ':desc'=>$descripcion, ':monto'=>$monto_total, ':estado'=>$estado,
            ':cu'=>$id_cuenta_enc, ':mo'=>$id_moneda_enc,
            ':mes'=>$mes_contable, ':anio'=>$anio_contable
        ]);
        $id_movimiento = (int)$stmt->fetchColumn();   // ← ahora sí
    }

    /* === 4. Insertar detalles === */
    foreach ($detalles as $d) {
        if ($tipo==='ingreso') {
            $sql = "INSERT INTO movimiento_detalle_ingreso
                    (id_movimiento_general,id_cuenta,id_plan_cuenta,
                     monto,tasa,monto_base,referencia)
                    VALUES (:id,:cu,:pl,:m,:t,:b,:r)";
            $conn->prepare($sql)->execute([
                ':id'=>$id_movimiento, ':cu'=>$d['id_cuenta'],
                ':pl'=>$id_plan_cuenta, ':m'=>$d['monto'],
                ':t'=>$d['tasa'], ':b'=>$d['monto_base'], ':r'=>$d['referencia']
            ]);
        } else { /* egreso */
            $sql = "INSERT INTO movimiento_detalle_egreso
                    (id_movimiento_general,id_cuenta,id_plan_cuenta,
                     descripcion,monto_aplicado,tasa,monto_base,
                     estado,fecha_aplicacion)
                    VALUES (:id,:cu,:pl,:d,:m,:t,:b,:e,:f)";
            $conn->prepare($sql)->execute([
                ':id'=>$id_movimiento, ':cu'=>$d['id_cuenta'],
                ':pl'=>$id_plan_cuenta, ':d'=>$descripcion,
                ':m'=>$d['monto'], ':t'=>$d['tasa'], ':b'=>$d['monto_base'],
                ':e'=>$estado, ':f'=>$fecha
            ]);
        }
    }

    $conn->commit();
    echo json_encode(['status'=>'ok']);

} catch (Exception $e) {
    if ($conn instanceof PDO && $conn->inTransaction()) $conn->rollBack();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
