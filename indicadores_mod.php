<?php session_start() ;?>

<?PHP
$userid= $_SESSION['userid'] ;
require_once("layouts/vars.php") ; 
require_once("core/funciones.php") ; 
require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();
date_default_timezone_set("America/Caracas");
$fecha= date('m/d/Y h:i:s a', time());




//$fecha= date('m/d/Y');
$petro= $_POST['petro'] ;
$dolar= $_POST['dolar'] ;

$func= new Funciones();


$ctrlrespuesta = "Error en Transaccion en Indicadores";
$ctrlestatus = 0;




        $sql= "INSERT INTO indicadores (fecha,    dolar,   petro   )
                               VALUES  ('$fecha','$dolar','$petro' )" ;


        $stmt= $conn->prepare($sql) ;
        $stmt->execute();
        $ctrlestatus=1; 
        $ctrlrespuesta="Indicadores Actualizados" ;
    


$datos= array(
    'respuesta'=> $ctrlrespuesta,
    'estatus'=> $ctrlestatus
);


echo json_encode($datos, JSON_FORCE_OBJECT);
?>
