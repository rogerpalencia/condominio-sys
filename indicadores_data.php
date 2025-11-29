<?php session_start() ;?>

<?php
## Database configuration
date_default_timezone_set("America/Caracas");
$userid= $_SESSION['userid'] ;
include "core/config.php" ; 
include "core/funciones.php" ; 
require_once("core/PDO.class.php") ; 
$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if(!$conn){
    die("Conexion Fallida");
    exit();
}
$func= new Funciones();
$conn2=  DB::getInstance();

$sql = "select * from empresas where id_contribuyente='$userid'" ;
$stmt= $conn2->prepare($sql) ;
$stmt->execute();
$row= $stmt->fetch();
$id_empresa= intval($row['id_empresa']) ?? 0 ;
$rif= $row['rif'] ?? null ;
$razon_social = $row['razon_social'] ?? null ;


## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

## Search 
$searchQuery = "id>=$1";
$searchQueryVal = array();
$searchQueryVal[] = 0;
if ($searchValue != '') {
    $searchQuery .= " or (id_accionista $2) ";
    $searchQueryVal[] = '%' . $searchValue . '%';
    $searchQueryVal[] = '%' . $searchValue . '%';

}

## Total number of records without filter
$sql = "select count(*) as allcount from indicadores ";
$result = pg_query($conn, $sql);
$records = pg_fetch_assoc($result);
$totalRecords = $records['allcount'];

## Total number of record with filter
$sql = "select count(*) as allcount from indicadores where 1=1 and " . $searchQuery;
$result = pg_query_params($conn, $sql, $searchQueryVal);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$sql = "select * from indicadores where 1=1 and " . $searchQuery . " order by " . "fecha" . " " . "DESC". " limit $rowperpage OFFSET $row";

$empRecords = pg_query_params($conn, $sql, $searchQueryVal);
$data = array();

while ($row = pg_fetch_assoc($empRecords)) {


    $date = $row['fecha'];
    $date = strtotime($date);





    $data[] = array(
        "id"    => $row['id'],
        "fecha" =>  date('d-m-Y / h:i A', $date),
        "petro" => $func->fmtbs($row['petro']),
        "dolar" =>$func->fmtbs($row['dolar'])
    );
}

## Response
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);