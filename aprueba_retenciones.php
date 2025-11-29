<?php include 'layouts/session.php'; 

require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();
require_once("core/funciones.php") ; 
$func= new Funciones();
require_once("layouts/vars.php") ; 

$ctrlestatus = 0;
$ctrlrespuesta = "Error en Transaccion";
$userid=        $_SESSION['userid'] ;
$id    =        $_POST['id'];
$accion     =   $_POST['accion'];
$ip =           $_SERVER['REMOTE_ADDR'];
$username=      $_SESSION['username'] ;
$fecha_creado = date('Y-m-d H:i:s', time());

if (($id !== 0)&&($accion == '0dff85h74844r7fV')  ){//aprobar 

   
        $sql = "update retenciones_defi set 
        id_estatus= 2, 
        estatus='Aprobada',
        user_aprueba= '$username',
        user_ip_aprueba= '$ip',
        user_id_aprueba= '$userid',
        user_date_aprueba=  '$fecha_creado'    
        where id_retencion='$id'
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        if ($stmt) {
            $ctrlestatus = 1;
            $ctrlrespuesta = "Fué aprobada la retención ";
        }
   
}

if (($id !== 0) && ($accion == '4gf8g55f7d7f5gdX')  ) {// rechazar
 
        $sql = "update retenciones_defi set 
        id_estatus= 0, 
        estatus='Rechazada',       
        user_aprueba= '$username',
        user_ip_aprueba= '$ip',
        user_id_aprueba= '$userid',
        user_date_aprueba=  '$fecha_creado'       
        where id_retencion='$id'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        if ($stmt) {
            $ctrlestatus = 1;
            $ctrlrespuesta = "Fué rechazada la retención ";
        }
}





$datos = array(
    'respuesta' => $ctrlrespuesta,
    'estatus' => $ctrlestatus
);

header('Content-Type: application/json');
echo json_encode($datos, JSON_FORCE_OBJECT);
?>
