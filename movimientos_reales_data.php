<?php
// movimientos_reales_data.php
// version 1.0
// al cambiar debes modificar las versiones.

require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn) throw new Exception('No se pudo conectar a la base de datos');
    $id_condominio = (int)($_POST['id_condominio'] ?? 0);
    if ($id_condominio <= 0) throw new Exception('ID de condominio inválido');

    $draw = (int)$_POST['draw'];
    $start = (int)$_POST['start'];
    $length = (int)$_POST['length'];
    $search = trim($_POST['search']['value'] ?? '');
    $order_col = $_POST['columns'][$_POST['order'][0]['column']]['data'] ?? 'id_movimiento';
    $order_dir = strtoupper($_POST['order'][0]['dir'] ?? 'desc');

    $valid_columns = [
        'id_movimiento' => 'mg.id_movimiento',
        'fecha_movimiento' => 'mg.fecha_movimiento',
        'descripcion' => 'mg.descripcion',
        'cuenta' => 'c.nombre',
        'moneda' => 'm.codigo',
        'monto_base_total' => '(SELECT COALESCE(SUM(monto_base), 0) FROM movimiento_detalle_ingreso WHERE id_movimiento_general = mg.id_movimiento) + (SELECT COALESCE(SUM(monto_base), 0) FROM movimiento_detalle_egreso WHERE id_movimiento_general = mg.id_movimiento)', // Cambiado de monto_total
        'mes_contable' => 'mg.mes_contable',
        'anio_contable' => 'mg.anio_contable',
        'tipo_movimiento' => 'mg.tipo_movimiento',
        'estado' => 'mg.estado'
    ];
    $order_col_sql = $valid_columns[$order_col] ?? 'mg.id_movimiento';
    $order_dir = in_array($order_dir, ['ASC', 'DESC']) ? $order_dir : 'DESC';

    $sql = "SELECT mg.id_movimiento, mg.fecha_movimiento, mg.descripcion, 
                   c.nombre AS cuenta, m.codigo AS moneda,
                   (SELECT COALESCE(SUM(monto_base), 0) FROM movimiento_detalle_ingreso WHERE id_movimiento_general = mg.id_movimiento) +
                   (SELECT COALESCE(SUM(monto_base), 0) FROM movimiento_detalle_egreso WHERE id_movimiento_general = mg.id_movimiento) AS monto_base_total,
                   mg.mes_contable, mg.anio_contable, mg.tipo_movimiento, mg.estado
            FROM movimiento_general mg
            JOIN cuenta c ON c.id_cuenta = mg.id_cuenta
            JOIN moneda m ON m.id_moneda = mg.id_moneda
            WHERE mg.id_condominio = :id_condominio";
    $params = [':id_condominio' => $id_condominio];
    if (!empty($search)) {
        $sql .= " AND (mg.descripcion ILIKE :search OR c.nombre ILIKE :search)";
        $params[':search'] = "%$search%";
    }

    $sql_count = "SELECT COUNT(*) FROM movimiento_general WHERE id_condominio = :id_condominio";
    if (!empty($search)) $sql_count .= " AND descripcion ILIKE :search";
    $stmt = $conn->prepare($sql_count);
    $stmt->execute($params);
    $recordsTotal = $stmt->fetchColumn();

    $sql .= " ORDER BY $order_col_sql $order_dir LIMIT :length OFFSET :start";
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) $stmt->bindValue($key, $value);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        'draw' => $draw ?? 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}