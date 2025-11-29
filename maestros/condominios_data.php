<?php 
@session_start() ;
## REvisado 16/07/2024

## Database configuration
$userid= $_SESSION['userid'] ;
include "core/config.php" ; 
include "core/funciones.php" ; 

$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if(!$conn){
    die("Conexion Fallida");
    exit();
}
$func= new Funciones();

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

## Search 
$searchQuery = "id_declaracion>$1";$searchQueryVal = array();
$searchQueryVal[] = 0;
if($searchValue != ''){
   $searchQuery .= " and (numero_dua ilike $3 or bel ilike $4 ) ";
    $searchQueryVal[] = '%'.$searchValue.'%';
    $searchQueryVal[] = '%'.$searchValue.'%';
    $searchQueryVal[] = '%'.$searchValue.'%';
}

## Total number of records without filter
$sql = "select count(*) as allcount from declaraciones_aduana id_contribuyente= '$userid' ";
$result = pg_query($conn,$sql);
$records = pg_fetch_assoc($result);
$totalRecords = $records['allcount'];

## Total number of record with filter
$sql = "select count(*) as allcount from declaraciones_aduana where id_contribuyente= '$userid' and ".$searchQuery;
$result = pg_query_params($conn,$sql,$searchQueryVal);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$sql = "select * from declaraciones_aduana where id_contribuyente= '$userid' and ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit $rowperpage OFFSET $row";

$empRecords = pg_query_params($conn,$sql,$searchQueryVal);
$data = array();

while ($row = pg_fetch_assoc($empRecords)) {
   $data[] = array( 
      "id_declaracion" => $row['id_declaracion'],
      "idc" => $row['idc'],
      "fecha_declaracion" => $func->formatfecha($row['fecha_declaracion']),
      "origen" => $row['origen'],
      "numero_dua" => $row['numero_dua'],
      "monto_dua" =>  $func->fmtbs($row['monto_dua']),
      "monto_a_pagar" => $func->fmtbs($row['monto_a_pagar']),
      "estatus" => $row['estatus']
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

?>
