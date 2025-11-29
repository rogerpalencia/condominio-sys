<?php
session_start(); // Asegúrate de iniciar la sesión si aún no lo has hecho

## Database configuration
date_default_timezone_set("America/Caracas");
$userid = $_SESSION['userid'];
include "core/config.php";
include "core/funciones.php";
require_once("core/PDO.class.php");
$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die("Conexion Fallida");
    exit();
}
$func = new Funciones();
$conn2 = DB::getInstance();

$sql = "SELECT * FROM indicadores ORDER BY fecha DESC LIMIT 15";
$empRecords = pg_query($conn, $sql);
$dataPetro = array();
$dataDolar = array();

while ($row = pg_fetch_assoc($empRecords)) {
    $date = strtotime($row['fecha']);

    $dataPetro[] = array(
        "x" => date('d-m-Y / h:i A', $date),
        "y" => $row['petro']
    );

    $dataDolar[] = array(
        "x" => date('d-m-Y / h:i A', $date),
        "y" => $row['dolar']
    );
}

$response = array(
    "petro" => $dataPetro,
    "dolar" => $dataDolar
);

echo json_encode($response);
?>
