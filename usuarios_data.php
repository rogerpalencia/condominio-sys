<?php session_start(); ?>

<?php
//# Database configuration
$userid = $_SESSION['userid'];

include 'core/config.php';
include 'core/funciones.php';

$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die('Conexion Fallida');
    exit();
}
$func = new Funciones();

//# Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

//# Search
$searchQuery = 'id_usuario > $1';
$searchQueryVal = [];
$searchQueryVal[] = 0;
if ($searchValue != '') {
    $searchQuery .= ' and (nombre ILIKE $2 OR nombres_y_apellidos ILIKE $3 OR gerencia ILIKE $4 OR cargo ILIKE $5)';
    $searchQueryVal[] = "%$searchValue%";
    $searchQueryVal[] = "%$searchValue%";
    $searchQueryVal[] = "%$searchValue%";
    $searchQueryVal[] = "%$searchValue%";
}

//# Total number of records without filter
$sql = 'select count(*) as allcount from usuarios';
$result = pg_query($conn, $sql);
$records = pg_fetch_assoc($result);
$totalRecords = $records['allcount'];

//# Total number of record with filter
$sql = 'select count(*) as allcount from usuarios where 1 = 1 and '.$searchQuery;
$result = pg_query_params($conn, $sql, $searchQueryVal);
$totalRecordwithFilter = $records['allcount'];

//# Fetch records
$sql = 'SELECT id_usuario, nombre, rif, perfil, nombres_y_apellidos, cedula, gerencia, cargo, activo 
        FROM usuarios 
        WHERE 1=1 AND '.$searchQuery.' 
        ORDER BY activo ASC, '.$columnName.' '.$columnSortOrder.' 
        LIMIT '.$rowperpage.' OFFSET '.$row;

 
        


        
$empRecords = pg_query_params($conn, $sql, $searchQueryVal);
$data = [];

while ($row = pg_fetch_assoc($empRecords)) {
    $data[] = [
        'id_usuario'            => $row['id_usuario'],
        'nombre'                => $row['nombre'],// contiene el email y para no complicar se queda asi
        'rif'                   => $row['rif'],
        'perfil'                => $row['perfil'],
        'nombres_y_apellidos'   => $row['nombres_y_apellidos'],
        'cedula'                => $row['cedula'],
        'gerencia'              => $row['gerencia'],
        'cargo'                 => $row['cargo'],
        'activo'                => $row['activo']
        ];
}

//# Response
$response = [
    'draw' => intval($draw),
    'iTotalRecords' => $totalRecords,
    'iTotalDisplayRecords' => $totalRecordwithFilter,
    'aaData' => $data,
];

echo json_encode($response);
