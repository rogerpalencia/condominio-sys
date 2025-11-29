<?php
// conciliacion_pagos_data.php
@session_start();
require_once("core/config.php");
require_once("core/funciones.php");
require_once("core/PDO.class.php");

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/conciliacion_pagos_data.log');

$func = new Funciones();
$conn = DB::getInstance();

$userid = isset($_SESSION['userid']) ? (int)$_SESSION['userid'] : 0;

/* ====== SOLO ESTO ES CRÍTICO: obtener id_condominio de forma robusta ====== */
$id_condominio = 0;
if (isset($_POST['id_condominio']) && $_POST['id_condominio'] !== '') {
    $id_condominio = (int)$_POST['id_condominio'];
} elseif (isset($_SESSION['id_condominio'])) {
    $id_condominio = (int)$_SESSION['id_condominio'];
} elseif (isset($_GET['id_condominio'])) {
    $id_condominio = (int)$_GET['id_condominio'];
}
if ($id_condominio <= 0) {
    error_log('Error: id_condominio requerido.');
    echo json_encode([
        "draw" => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
        "iTotalRecords" => 0,
        "iTotalDisplayRecords" => 0,
        "aaData" => [],
        "error" => "El id_condominio es requerido."
    ]);
    exit;
}
/* ========================================================================== */

// Parámetros DataTables (sin alterar tu lógica)
$draw            = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$row             = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$rowperpage      = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$columnIndex     = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
$columnSortOrder = (isset($_POST['order'][0]['dir']) && in_array($_POST['order'][0]['dir'], ['asc','desc'], true))
                    ? $_POST['order'][0]['dir'] : 'desc';
$searchValue     = $_POST['search']['value'] ?? '';

// Mapeo columnas (se mantiene)
$columnMap = [
    0 => 'rc.numero_recibo',
    1 => 'rc.fecha_emision',
    2 => 'u.correo',
    3 => 'p.nombre1',        // solo para ordenar
    4 => 'i.identificacion',
    5 => 'rc.total_pagado',
    6 => 'rc.observaciones',
    7 => 'rc.id_recibo'
];
if (!isset($columnMap[$columnIndex])) { $columnIndex = 0; }
$columnName = $columnMap[$columnIndex];

// WHERE base (solo usa id_condominio)
$where  = "rc.id_condominio = :id_condominio AND rc.estado = 'en_revision'";
$params = [":id_condominio" => $id_condominio];

// Búsqueda (igual que tenías)
$searchClause = "";
if ($searchValue !== '') {
    $searchClause = " AND (
        rc.numero_recibo ILIKE :search OR
        COALESCE(p.nombre1,'') ILIKE :search OR
        COALESCE(p.apellido1,'') ILIKE :search OR
        COALESCE(pf.nombre1,'') ILIKE :search OR
        COALESCE(pf.apellido1,'') ILIKE :search OR
        COALESCE(i.identificacion,'') ILIKE :search OR
        COALESCE(rc.observaciones,'') ILIKE :search OR
        COALESCE(u.correo,'') ILIKE :search
    )";
    $params[':search'] = "%{$searchValue}%";
}

try {
    // 1) Total sin búsqueda (sin joins)
    $sqlCount = "
        SELECT COUNT(*) AS allcount
        FROM recibo_cabecera rc
        WHERE rc.id_condominio = :id_condominio
          AND rc.estado = 'en_revision'
    ";
    $st = $conn->prepare($sqlCount);
    $st->execute([":id_condominio" => $id_condominio]);
    $totalRecords = (int)$st->fetch(PDO::FETCH_ASSOC)['allcount'];

    // 2) Total con búsqueda (mismos joins que en datos)
    $totalRecordsFiltered = $totalRecords;
    if ($searchValue !== '') {
        $sqlCountFiltered = "
            SELECT COUNT(*) AS allcount
            FROM recibo_cabecera rc
            LEFT JOIN propietario p ON p.id_propietario = rc.id_propietario
            LEFT JOIN inmueble   i ON i.id_inmueble   = rc.id_inmueble
            LEFT JOIN menu_login.usuario u ON u.id_usuario = rc.id_usuario
            LEFT JOIN LATERAL (
                SELECT pr.*
                FROM propietario_inmueble pi
                JOIN propietario pr ON pr.id_propietario = pi.id_propietario
                WHERE pi.id_inmueble = rc.id_inmueble
                ORDER BY pi.fecha_creacion DESC
                LIMIT 1
            ) pf ON true
            WHERE {$where} {$searchClause}
        ";
        $st = $conn->prepare($sqlCountFiltered);
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $st->execute();
        $totalRecordsFiltered = (int)$st->fetch(PDO::FETCH_ASSOC)['allcount'];
    }

    // 3) Datos paginados (igual, solo con el id_condominio correcto)
    $sqlData = "
        SELECT
            rc.id_recibo,
            rc.numero_recibo,
            rc.fecha_emision,
            rc.total_pagado AS monto_total,
            rc.observaciones,

            CASE
                WHEN p.id_propietario IS NOT NULL
                     AND TRIM(COALESCE(p.nombre1,'')) <> '' THEN
                    CONCAT(
                        INITCAP(COALESCE(p.nombre1,'')),
                        CASE WHEN COALESCE(p.nombre2,'') <> '' THEN ' ' || UPPER(SUBSTRING(p.nombre2,1,1)) || '.' ELSE '' END,
                        ' ',
                        INITCAP(COALESCE(p.apellido1,'')),
                        CASE WHEN COALESCE(p.apellido2,'') <> '' THEN ' ' || UPPER(SUBSTRING(p.apellido2,1,1)) || '.' ELSE '' END
                    )
                WHEN pf.id_propietario IS NOT NULL
                     AND TRIM(COALESCE(pf.nombre1,'')) <> '' THEN
                    CONCAT(
                        INITCAP(COALESCE(pf.nombre1,'')),
                        CASE WHEN COALESCE(pf.nombre2,'') <> '' THEN ' ' || UPPER(SUBSTRING(pf.nombre2,1,1)) || '.' ELSE '' END,
                        ' ',
                        INITCAP(COALESCE(pf.apellido1,'')),
                        CASE WHEN COALESCE(pf.apellido2,'') <> '' THEN ' ' || UPPER(SUBSTRING(pf.apellido2,1,1)) || '.' ELSE '' END
                    )
                ELSE '—'
            END AS propietario,

            i.identificacion AS inmueble,
            u.correo AS usuario

        FROM recibo_cabecera rc
        LEFT JOIN propietario p ON p.id_propietario = rc.id_propietario
        LEFT JOIN inmueble   i ON i.id_inmueble   = rc.id_inmueble
        LEFT JOIN menu_login.usuario u ON u.id_usuario = rc.id_usuario
        LEFT JOIN LATERAL (
            SELECT pr.*
            FROM propietario_inmueble pi
            JOIN propietario pr ON pr.id_propietario = pi.id_propietario
            WHERE pi.id_inmueble = rc.id_inmueble
            ORDER BY pi.fecha_creacion DESC
            LIMIT 1
        ) pf ON true

        WHERE {$where} {$searchClause}
        ORDER BY {$columnName} {$columnSortOrder}
        LIMIT :rowperpage OFFSET :row
    ";

    $st = $conn->prepare($sqlData);
    foreach ($params as $k => $v) {
        $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $st->bindValue(':rowperpage', $rowperpage, PDO::PARAM_INT);
    $st->bindValue(':row', $row, PDO::PARAM_INT);
    $st->execute();

    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    // 4) Respuesta
    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            "id_recibo"     => $r['id_recibo'],
            "numero_recibo" => $r['numero_recibo'],
            "fecha_emision" => $r['fecha_emision'],
            "usuario"       => $r['usuario'] ?? '—',
            "propietario"   => (trim($r['propietario']) === '') ? '—' : $r['propietario'],
            "inmueble"      => $r['inmueble'] ?? 'Sin inmueble',
            "monto_total"   => $r['monto_total'],
            "observaciones" => $r['observaciones'] ?? ''
        ];
    }

    echo json_encode([
        "draw" => $draw,
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordsFiltered,
        "aaData" => $data
    ]);

} catch (Exception $e) {
    error_log('Error servidor: ' . $e->getMessage());
    echo json_encode([
        "draw" => isset($draw) ? (int)$draw : 0,
        "iTotalRecords" => 0,
        "iTotalDisplayRecords" => 0,
        "aaData" => [],
        "error" => "Error en el servidor: " . $e->getMessage()
    ]);
}
