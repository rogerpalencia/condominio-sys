<?php
// master_inmuebles_data.php
session_start();
require_once("core/config.php");
require_once("core/funciones.php");
require_once("core/PDO.class.php");

// Desactivar errores en pantalla para evitar que interfieran con el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

$func = new Funciones();
$conn = DB::getInstance();
$userid = $_SESSION['userid'] ?? null;
$id_condominio = $_POST['id_condominio'] ?? null;

// Validar id_condominio
if (!$id_condominio) {
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "iTotalRecords" => 0,
        "iTotalDisplayRecords" => 0,
        "aaData" => [],
        "error" => "El idCondominio es requerido."
    ]);
    exit;
}

$draw = $_POST['draw'] ?? 1;
$row = $_POST['start'] ?? 0;
$rowperpage = $_POST['length'] ?? 10;

// Manejar el ordenamiento
$columnIndex = isset($_POST['order'][0]['column']) ? $_POST['order'][0]['column'] : 1;
$columnSortOrder = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

// Mapear el índice de la columna a un nombre de columna válido
$columnMap = [
    0 => 'id_inmueble',
    1 => 'correlativo',
    2 => 'identificacion',
    3 => 'tipo',
    4 => 'id_inmueble'
];

if (!isset($columnMap[$columnIndex])) {
    $columnIndex = 1; // Por defecto, ordenar por correlativo
}
$columnName = $columnMap[$columnIndex];

$searchValue = $_POST['search']['value'] ?? '';

$searchQuery = "i.id_condominio = :id_condominio";
$searchQueryVal = [
    ":id_condominio" => $id_condominio
];

if ($searchValue != '') {
    $searchQuery .= " AND (i.identificacion ILIKE :search OR i.torre ILIKE :search OR i.piso ILIKE :search OR i.calle ILIKE :search OR i.manzana ILIKE :search)";
    $searchQueryVal[":search"] = "%$searchValue%";
}

try {
    // Total de registros
    $totalQuery = "SELECT COUNT(*) as allcount FROM inmueble i WHERE i.id_condominio = :id_condominio";
    if ($searchValue != '') {
        $totalQuery .= " AND (i.identificacion ILIKE :search OR i.torre ILIKE :search OR i.piso ILIKE :search OR i.calle ILIKE :search OR i.manzana ILIKE :search)";
    }
    $stmtTotal = $conn->prepare($totalQuery);
    $stmtTotal->execute($searchQueryVal);
    $totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['allcount'];

    // Datos base de inmuebles
    $dataQuery = "SELECT i.* FROM inmueble i WHERE $searchQuery ORDER BY i.$columnName $columnSortOrder LIMIT $rowperpage OFFSET $row";
    $stmtData = $conn->prepare($dataQuery);
    $stmtData->execute($searchQueryVal);
    $records = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    // Consultas auxiliares
    $sql_summary = "SELECT m.codigo AS moneda, SUM(n.monto_x_pagar) AS total_pendiente
                    FROM notificacion_cobro n
                    INNER JOIN moneda m ON n.id_moneda = m.id_moneda
                    WHERE n.id_inmueble = :id_inmueble
                    GROUP BY m.codigo
                    ORDER BY m.codigo";

    $sql_credito = "SELECT m.codigo AS moneda, SUM(c.monto) AS total_credito
                    FROM credito_a_favor c
                    INNER JOIN moneda m ON c.id_moneda = m.id_moneda
                    WHERE c.id_inmueble = :id_inmueble
                    GROUP BY m.codigo
                    ORDER BY m.codigo";

    $sql_recibo = "SELECT COUNT(*) FROM notificacion_cobro WHERE id_inmueble = :id_inmueble";

    // Propietario (si existe) para el inmueble (toma 1)
    $sql_prop = "SELECT pi.id_propietario,
                        p.nombre1, p.nombre2, p.apellido1, p.apellido2,
                        u.correo
                 FROM propietario_inmueble pi
                 JOIN propietario p ON p.id_propietario = pi.id_propietario
                 LEFT JOIN menu_login.usuario u ON u.id_usuario = p.id_usuario
                 WHERE pi.id_inmueble = :id_inmueble
                 LIMIT 1";

    $data = [];
    foreach ($records as $row) {
        // Deudas
        $stmt_summary = $conn->prepare($sql_summary);
        $stmt_summary->bindParam(':id_inmueble', $row['id_inmueble'], PDO::PARAM_INT);
        $stmt_summary->execute();
        $deudas_por_moneda = $stmt_summary->fetchAll(PDO::FETCH_ASSOC);

        // Créditos
        $stmt_credito = $conn->prepare($sql_credito);
        $stmt_credito->bindParam(':id_inmueble', $row['id_inmueble'], PDO::PARAM_INT);
        $stmt_credito->execute();
        $creditos_por_moneda = $stmt_credito->fetchAll(PDO::FETCH_ASSOC);

        // Recibos
        $stmt_recibo = $conn->prepare($sql_recibo);
        $stmt_recibo->bindParam(':id_inmueble', $row['id_inmueble'], PDO::PARAM_INT);
        $stmt_recibo->execute();
        $tiene_recibos = $stmt_recibo->fetchColumn() > 0;

        // Propietario
        $stmt_prop = $conn->prepare($sql_prop);
        $stmt_prop->bindParam(':id_inmueble', $row['id_inmueble'], PDO::PARAM_INT);
        $stmt_prop->execute();
        $prop = $stmt_prop->fetch(PDO::FETCH_ASSOC);

        $propietario = null;
        if ($prop) {
            $nombre = trim(
                preg_replace(
                    '/\s+/', ' ',
                    ($prop['nombre1'] ?? '') . ' ' . ($prop['nombre2'] ?? '') . ' ' .
                    ($prop['apellido1'] ?? '') . ' ' . ($prop['apellido2'] ?? '')
                )
            );
            $propietario = [
                "id"     => (int)$prop['id_propietario'],
                "nombre" => mb_strtoupper($nombre, 'UTF-8'),
                "correo" => $prop['correo'] ?? ''
            ];
        }

        $data[] = [
            "id_inmueble"   => $row['id_inmueble'],
            "correlativo"   => $row['correlativo'],
            "identificacion"=> $row['identificacion'],
            "tipo_inmueble" => $row['tipo'],
            "alicuota"      => $row['alicuota'],
            "deudas"        => $deudas_por_moneda,
            "creditos"      => $creditos_por_moneda,
            "puede_borrar"  => !$tiene_recibos,
            "propietario"   => $propietario
        ];
    }

    $response = [
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecords,
        "aaData" => $data
    ];

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode([
        "draw" => intval($draw),
        "iTotalRecords" => 0,
        "iTotalDisplayRecords" => 0,
        "aaData" => [],
        "error" => "Error en el servidor: " . $e->getMessage()
    ]);
}
