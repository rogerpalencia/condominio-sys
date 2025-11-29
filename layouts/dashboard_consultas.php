<?php
require_once "./core/config.php";


$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die("Conexion Fallida");
    exit();
}

$query = "SELECT count(distinct rif) c  FROM  declaraciones_aduana WHERE id_estatus <> 0 ";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$cant_empre_activas= $row['c']; 

$query2 = "SELECT count(*) d FROM  empresas WHERE activa='s' or activa='S'";
$result = pg_query($query);
$row = pg_fetch_assoc($result);
$cant_empre_registradas= $row['d']; 

?>
