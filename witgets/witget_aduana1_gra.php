<?php
$userid= $_SESSION['userid'] ;
include "./core/config.php" ; 
include "./core/funciones.php" ; 
include "../core/config.php" ; 
include "../core/funciones.php" ; 

$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if(!$conn){
    die("Conexion Fallida");
    exit();
}


$sql = "SELECT
id_estatus, 
count(*),
sum(monto_a_pagar)
FROM
declaraciones_aduana
WHERE
EXTRACT(MONTH FROM declaraciones_aduana.fecha_declaracion) = EXTRACT(MONTH FROM now() )  and id_estatus>0
GROUP BY
declaraciones_aduana.id_estatus
ORDER BY
declaraciones_aduana.id_estatus";

$empRecords = pg_query($conn,$sql);
$data = array();

$arr = pg_fetch_all($empRecords);

 echo pg_fetch_result($res, 2, 0);

 
 foreach ($arr as $row)
 {


    if ($row["id_estatus"]== 1) {
        $decla_cant= $row["count"];
        $decla_sum=  $row["sum"];
     };

     if ($row["id_estatus"]== 2) {
        $por_rev_pag_adu_cant= $row["count"];
        $por_rev_pag_adu_sum=  $row["sum"];
     };


     if ($row["id_estatus"]== 3) {
        $por_pago_cant= $row["count"];
        $por_pago_sum=  $row["sum"];
     };

     if ($row["id_estatus"]== 4) {
        $por_adu_cant= $row["count"];
        $por_adu_sum=  $row["sum"];
     };


     if ($row["id_estatus"]== 5) {
        $por_sello_cant= $row["count"];
        $por_sello_sum=  $row["sum"];
     };

     if ($row["id_estatus"]== 6) {
        $selladas_cant= $row["count"];
        $selladas_sum=  $row["sum"];
     };

 };


 echo " <div id='aduana1' class='apex-charts'></div>"; 
?>









