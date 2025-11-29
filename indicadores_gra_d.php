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

$sql = "select * from indicadores order by fecha DESC limit 15";

$empRecords = pg_query($conn, $sql);
$data = array();

while ($row = pg_fetch_assoc($empRecords)) {


    $date = $row['fecha'];
    $date = strtotime($date);





    $data[] = array(

        "x" =>  date('d-m-Y / h:i A', $date),
        "y" => $row['petro'],
        "z" => $row['dolar'],
       
    );
   
}




echo json_encode($data);

?>