<?php
// notif_master_data.php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';

try {
    $conn = DB::getInstance();
    if (!$conn || !$conn->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
        throw new Exception('Sin conexión a BD');
    }

    // ======= SOLO ESTO ES CRÍTICO =======
    $id_condominio = 0;
    if (isset($_POST['id_condominio']) && $_POST['id_condominio'] !== '') {
        $id_condominio = (int)$_POST['id_condominio'];
    } elseif (isset($_SESSION['id_condominio'])) {
        $id_condominio = (int)$_SESSION['id_condominio'];
    } elseif (isset($_GET['id_condominio'])) {
        $id_condominio = (int)$_GET['id_condominio'];
    }
    if ($id_condominio <= 0) {
        throw new Exception('ID de condominio inválido');
    }
    // =====================================

    // Parámetros DataTables
    $draw   = (int)($_POST['draw']   ?? 0);
    $start  = (int)($_POST['start']  ?? 0);
    $length = (int)($_POST['length'] ?? 10);
    $search = trim($_POST['search']['value'] ?? '');

    // === Consulta base (SIN ORDER/LIMIT) ===
    $sql_core = "SELECT n.id_notificacion_master, n.anio, n.mes, n.fecha_emision, 
                        n.descripcion, m.codigo AS moneda, n.monto_total, n.estado, 
                        CASE n.id_tipo 
                            WHEN 1 THEN 'Presupuesto' 
                            WHEN 2 THEN 'Relación Ingr./Egr.' 
                            ELSE '' 
                        END AS tipo_txt,
                        CASE 
                            WHEN COUNT(DISTINCT d.tipo_movimiento) = 1 AND MIN(d.tipo_movimiento) = 'ingreso' THEN 'ingresos'
                            WHEN COUNT(DISTINCT d.tipo_movimiento) = 1 AND MIN(d.tipo_movimiento) = 'egreso' THEN 'egresos'
                            WHEN COUNT(DISTINCT d.tipo_movimiento) > 1 THEN 'mixto'
                            ELSE 'sin movimientos'
                        END AS movimientos
                 FROM notificacion_cobro_master n
                 JOIN moneda m ON m.id_moneda = n.id_moneda
                 LEFT JOIN notificacion_cobro_detalle_master d 
                    ON d.id_notificacion_master = n.id_notificacion_master
                 WHERE n.id_condominio = :id_condominio
                 GROUP BY n.id_notificacion_master, n.anio, n.mes, n.fecha_emision, 
                          n.descripcion, m.codigo, n.monto_total, n.estado, n.id_tipo";

    $params = [':id_condominio' => $id_condominio];

    // Aplica HAVING si hay búsqueda
    $sql_list = $sql_core;
    if ($search !== '') {
        $sql_list .= " HAVING (n.descripcion ILIKE :s 
                               OR n.anio::text ILIKE :s 
                               OR n.mes::text ILIKE :s)";
        $params[':s'] = "%{$search}%";
    }

    // ===== ORDEN ===== (un solo ORDER BY al final)
    $orders = [];
    if (!empty($_POST['order'])) {
        // Mapea índices de columnas de DataTables -> columnas SQL
        $columnsMap = [
            0 => 'n.id_notificacion_master',
            1 => 'n.anio',
            2 => 'n.mes',
            3 => 'n.fecha_emision',
            4 => 'n.descripcion',
            5 => 'm.codigo',
            6 => 'n.monto_total',
            7 => 'n.estado',
            8 => 'n.id_tipo',
            9 => 'movimientos' // alias calculado
        ];

        foreach ($_POST['order'] as $ord) {
            $idx = (int)$ord['column'];
            $dir = strtolower($ord['dir']) === 'asc' ? 'ASC' : 'DESC';
            if (isset($columnsMap[$idx])) {
                $orders[] = $columnsMap[$idx] . ' ' . $dir;
            }
        }
    }
    $order_clause = $orders ? implode(', ', $orders) : 'n.anio DESC, n.mes DESC';

    // ===== Conteos =====
    // Total sin filtro (cuenta filas del GROUP BY para ese condominio)
    $sql_count_total = "SELECT COUNT(*) FROM (
                           $sql_core
                        ) AS sub_total";
    $st_total = $conn->prepare($sql_count_total);
    $st_total->bindValue(':id_condominio', $id_condominio, PDO::PARAM_INT);
    $st_total->execute();
    $recordsTotal = (int)$st_total->fetchColumn();

    // Total filtrado (respeta HAVING si hay búsqueda)
    $sql_count_filtered = "SELECT COUNT(*) FROM (
                              $sql_list
                           ) AS sub_filt";
    $st_filt = $conn->prepare($sql_count_filtered);
    foreach ($params as $k=>$v) $st_filt->bindValue($k,$v);
    $st_filt->execute();
    $recordsFiltered = (int)$st_filt->fetchColumn();

    // ===== Listado con orden y paginación =====
    $sql_final = $sql_list . " ORDER BY $order_clause LIMIT :length OFFSET :start";
    $st = $conn->prepare($sql_final);
    foreach ($params as $k=>$v) $st->bindValue($k,$v);
    $st->bindValue(':length', $length, PDO::PARAM_INT);
    $st->bindValue(':start',  $start,  PDO::PARAM_INT);
    $st->execute();
    $data = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'draw'            => $draw,
        'recordsTotal'    => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $data
    ]);

} catch (Throwable $e) {
    echo json_encode([
        'draw' => isset($draw)?(int)$draw:0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}
