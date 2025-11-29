<?php
session_start();
require_once("core/config.php");
require_once("core/funciones.php");
require_once("core/PDO.class.php");

$func = new Funciones();
$conn = DB::getInstance();
$userid = $_SESSION['userid'];
$id_condominio = $_POST['id_condominio']; // recibido desde el frontend

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = $_POST['search']['value'];

$searchQuery = "i.id_condominio = :id_condominio";
$searchQueryVal = [
    ":id_condominio" => 5
];

if ($searchValue != '') {
    $searchQuery .= " AND (i.identificacion ILIKE :search OR i.torre ILIKE :search OR i.piso ILIKE :search OR i.calle ILIKE :search OR i.manzana ILIKE :search)";
    $searchQueryVal[":search"] = "%$searchValue%";
}

$totalQuery = "SELECT COUNT(*) as allcount FROM inmueble i WHERE $searchQuery";
$stmtTotal = $conn->prepare($totalQuery);
$stmtTotal->execute($searchQueryVal);
$totalRecords = $stmtTotal->fetch(PDO::FETCH_ASSOC)['allcount'];

$dataQuery = "SELECT i.* FROM inmueble i WHERE $searchQuery ORDER BY $columnName $columnSortOrder LIMIT $rowperpage OFFSET $row";
$stmtData = $conn->prepare($dataQuery);
$stmtData->execute($searchQueryVal);
$records = $stmtData->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($records as $row) {
    $tipo = (!empty($row['torre']) || !empty($row['piso'])) ? 'Apartamento' : 'Casa';
    $data[] = [
        "id_inmueble" => $row['id_inmueble'],
        "identificacion" => $row['identificacion'],
        "tipo_inmueble" => $row['tipo'],
        "torre" => $row['torre'],
        "piso" => $row['piso'],
        "manzana" => $row['manzana'],
        "avenida" => $row['avenida'],
         "alicuota" => $row['alicuota'],
        "calle" => $row['calle'],
        "correlativo" => $row['correlativo'],
        "estado" => $row['estado'] ? 'Activo' : 'Inactivo'
    ];
}

$response = [
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecords,
    "aaData" => $data
];

echo json_encode($response);
?>
