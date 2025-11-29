<?php session_start() ;?>
<?php
$userid= $_SESSION['userid'] ;
require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();

require_once("core/funciones.php") ; 
$func= new Funciones();
require_once("layouts/vars.php") ; 
$parserJsn = PARSERJSN ;

$ctrlrespuesta = "Error en Transaccion";
$ctrlestatus = 0;

$ip = $_SERVER['REMOTE_ADDR'];
$username= $_SESSION['username'] ;
$fecha_creado = date('Y-m-d H:i:s', time());


$id_pagos=intval($_POST['id']) ; 
if ($id_pagos!==0)
{
	$sql="SELECT id_estatus,rif,id_contribuyente,codigo,monto FROM pagos WHERE id_pagos='$id_pagos'" ;
    $stmt= $conn->prepare($sql) ;
    $stmt->execute();
    $row=$stmt->fetch();
    $id_contribuyente=$row['id_contribuyente'] ;
    $id_declaracion=$row['id_declaracion'] ;
    $codigo = $row['codigo'] ;
    $monto = $row['monto'] ;
    $rif= $row['rif'] ;

    if ($row['id_estatus']==1)
    {
        $sql2= "UPDATE pagos SET id_estatus=2, 
        estatus='Conciliado', 
        user_aprueba= '$username',
        user_ip_aprueba= '$ip',
        user_id_aprueba= '$userid'  
        WHERE id_pagos='$id_pagos'" ; 
        
        $stmt= $conn->prepare($sql2) ;
        $stmt->execute();
        if ($stmt)
        {
            // CONSULTAR MONTO PAGADO DE LA DUA
                $sql= "SELECT id_declaracion,monto_a_pagar FROM declaraciones_aduana WHERE id_contribuyente= '$id_contribuyente' AND id_declaracion='$id_declaracion'" ;
                $stmt= $conn->prepare($sql2) ;
                $stmt->execute();
                $monto_pendiente =$row['monto_a_pagar'] ;

                $transf_pagado = 0;
                $sql="SELECT sum(monto) AS transf_pagado FROM pagos WHERE id_declaracion='$id_declaracion' AND id_contribuyente= '$id_contribuyente'" ;
                $stmt= $conn->prepare($sql) ;
                $stmt->execute();
                $row= $stmt->fetch();
                $transf_pagado = $row['transf_pagado'] ?? 0 ;
                if ($transf_pagado > 0) 
                    $monto_pendiente=$monto_pendiente - $transf_pagado ;
            
                $cred_pagado = 0;
                // BUSCAR SI LA DUA FUE PAGADA POR CREDITO FISCAL 
                $sql= "SELECT sum(credito_fiscal)* -1 AS cred_pagado FROM credito_contribuyente WHERE id_declaracion='$id_declaracion' AND id_contribuyente= '$id_contribuyente' AND operacion= 'Egreso'";
                $stmt= $conn->prepare($sql) ;
                $stmt->execute();
                $row=$stmt->fetch();
                $cred_pagado = $row['cred_pagado'] ?? 0 ;
                if ($cred_pagado > 0)
                    $monto_pendiente=$monto_pendiente - $cred_pagado ;  

            // FIN COSULTAR MONTO PENDIENTE
                
            $sql="UPDATE declaraciones_aduana SET monto_pendiente= '$monto_pendiente' WHERE id_contribuyente= '$id_contribuyente' AND id_declaracion='$id_declaracion'" ;
            $stmt= $conn->prepare($sql) ;
            $stmt->execute();

            // REVISAR CUANTOS PAGO PARA MARCAR LA DUA COMO CONCILIADA
            $sql="SELECT count(*) AS cuantos FROM pagos WHERE codigo='$codigo' AND id_contribuyente='$id_contribuyente' AND id_estatus>0" ; 
            $stmt= $conn->prepare($sql) ;
            $stmt->execute();
            $row=$stmt->fetch();
            $cuantosreg = $row['cuantos'] ;

            $sql="SELECT count(*) AS conciliados FROM pagos WHERE codigo='$codigo' AND id_contribuyente='$id_contribuyente' AND id_estatus=2" ; 
            $stmt= $conn->prepare($sql) ;
            $stmt->execute();
            $row=$stmt->fetch();
            
            if ($cuantosreg==$row['conciliados']){

                $sql="SELECT id_estatus FROM declaraciones_aduana WHERE id_declaracion='$id_declaracion' AND id_contribuyente='$id_contribuyente'" ; 
                $stmt= $conn->prepare($sql) ;
                $stmt->execute();
                $row=$stmt->fetch();
                if ($row['id_estatus']==2){
                    $id_estatus= 3 ; 
                    $estatus="Rev. Aduana" ;
                }    
                if ($row['id_estatus']==4){
                    if ($monto_pendiente<=0){
                        $id_estatus= 5 ; 
                        $estatus="Por Sellar" ;
                    }
                }

                // PASAR EXCEDENETE  DEL PAGO A CREDITO FISCAL AL RIF DEL CONTRIBUYENTE
                if (($id_estatus==3) || ($id_estatus==4))
                {
                    $sql= "SELECT monto_a_pagar FROM declaraciones_aduana WHERE id_contribuyente='$id_contribuyente' AND id_declaracion='$id_declaracion'" ;
                    $stmt= $conn->prepare($sql) ;
                    $stmt->execute();
                    $row=$stmt->fetch();
                    $excedente = $row['monto_a_pagar'];
                    $credito_fiscal= 0 ;

                    $sql3= "SELECT sum(monto) AS pagado FROM pagos WHERE id_declaracion='$id_declaracion' AND id_estatus = 2 AND id_contribuyente= '$id_contribuyente'";
                    $stmt= $conn->prepare($sql3) ;
                    $stmt->execute();
                    $row=$stmt->fetch();
                    if ($row['pagado'] > 0)
                    {
                        $excedente= $excedente - $row['pagado'] ;
                        if ($excedente < 0) 
                            $credito_fiscal= $excedente * -1; 
                    }


                    if ($credito_fiscal > 0)
                    {
                        $operacion= "Ingreso"  ;
                        $sql= "INSERT INTO credito_contribuyente 
                        (usuario,ip,id_contribuyente,codigo,operacion,rif,credito_fiscal,fecha_creado) VALUES 
                        ('$username','$ip','$id_contribuyente','$codigo','$operacion','$rif','$credito_fiscal','$fecha_creado')" ;
                        $stmt= $conn->prepare($sql) ;
                        $stmt->execute();
                        if ($stmt)
                        {
                            $sql= "UPDATE declaraciones_aduana SET monto_pendiente=0 WHERE id_contribuyente='$id_contribuyente' AND id_declaracion='$id_declaracion'" ;
                            $stmt= $conn->prepare($sql) ;
                            $stmt->execute();
                        }
                    }    
                }

                $sql= "UPDATE declaraciones_aduana SET id_estatus='$id_estatus', estatus='$estatus', rev_pagos_estatus='' WHERE id_contribuyente='$id_contribuyente' AND id_declaracion='$id_declaracion'" ;
                $stmt= $conn->prepare($sql) ;
                $stmt->execute();
                
            }
                        
            $ctrlrespuesta="Item Aprobado" ;
            $ctrlestatus= 1;
        }
    }
}	

//$ctrlrespuesta=$sql2 ;
//$ctrlestatus=1 ;

$datos= array(
    'respuesta'=>$ctrlrespuesta,
    'estatus'=> $ctrlestatus
);

if ($parserJsn==0)
    header('Content-Type: application/json');

echo json_encode($datos, JSON_FORCE_OBJECT);
    