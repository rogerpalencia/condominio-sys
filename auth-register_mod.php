<?php session_start() ;?>

<?php
require_once("core/PDO.class.php") ; 
$conn=  DB::getInstance();
require_once("layouts/vars.php") ; 
$ctrlrespuesta="Transacción Invalida"; 
$ctrlestatus=0;
$parserJsn=PARSERJSN ;
if (isset($_POST['username']))
{ 
    $username=$_POST['username'] ;
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql= "SELECT id_validacion_usuario FROM validacion_usuarios WHERE login=:username" ;
    $stmt= $conn->prepare($sql) ;
    $stmt->bindParam(":username",$_POST['username']);
    $stmt->execute();
    $row= $stmt->fetch();
    $id_validacion_usuario = $row['id_validacion_usuario'] ?? null ;
    if ($id_validacion_usuario==0){
        $clave = sha1($_POST['userpass']);
        $token = bin2hex(random_bytes(256)); 

        $id_ente=1;
        /*$sql= "INSERT INTO entes (email_id) VALUES (:username) RETURNING id" ;
        $stmt= $conn->prepare($sql) ;
        $stmt->bindParam(':username',$_POST['username']);
        $stmt->execute();
        $row= $stmt->fetch();
        $id_ente = $row['id'] ?? null ;
        */

        $sql2= "INSERT INTO validacion_usuarios (ip,login,clave,id_ente,perfil) VALUES ('$ip',:username,'$clave','$id_ente',99)";
        $stmt= $conn->prepare($sql2) ;
        $stmt->bindParam(':username',$_POST['username']);
        $stmt->execute();
        if ($stmt)
        {
            $enlaceconf = "https://j2dca.com/semat-pc/Contribuyentes/auth-login.php?login=".$username. "&conf=".$token  ;
            $to = $username;
            $header = "From:no-responder@j2dca.com\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html\r\n";
            $subject = "Confirmacion de su Correo SEMAT-PC";
            $message =  "Email: <b>$username</b><br>";
            $message .= "Enlace: ".$enlaceconf . "<br>";
            $message .= "SEMAT-PC<br>";
            mail($to,$subject,$message,$header);
            $ctrlrespuesta="Su Cuenta ha sido Creada. Confirme su cuenta de Correo haciendo Click en el enlace enviado a: " .$username ; 
            $ctrlestatus=1; 
        }
    }
}

$datos= array(
'respuesta'=> $ctrlrespuesta,
'estatus'=> $ctrlestatus 
);
if ($parserJsn==0)
    header('Content-Type: application/json');
echo json_encode($datos, JSON_FORCE_OBJECT);
?>

