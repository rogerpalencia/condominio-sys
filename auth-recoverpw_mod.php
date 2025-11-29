<?php session_start() ;?>

<?php
require_once("layouts/mainfile.php")  ;
$model= new Mainfile();
$conn= $model->get_connection();

$ctrlrespuesta="Transacción Invalida"; 
$ctrlestatus=0;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/src/SMTP.php';

// passing true in constructor enables exceptions in PHPMailer
$mail = new PHPMailer(true);
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $uri_path);
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/$uri_segments[1]";

if (isset($_POST['useremail'])) {

    $sql = "SELECT token,username,useremail,apellidos,nombres FROM usuarios WHERE useremail = :useremail";
    $stmt= $conn->prepare($sql) ;
    $stmt->bindParam(":useremail",$_POST['useremail']);
    $stmt->execute();
    $row= $stmt->fetch();

    $token = $row['token'] ?? null ;
    $useremail= $row['useremail'] ?? null ;
    $apellidos = $row['apellidos']  ?? null ;
    $nombres=$row['nombres'] ?? null ;
    $username = $apellidos." ".$nombres ;

    if ($token) {
        $subject = "Recuperacion de Su Clave";
        $body = "Saludos, $username. Clickee aqui para restablecer su Clave " . $actual_link . "/auth-reset-password.php?token=$token ";
        
        $sender_email = "From: " . echo EMAILAPP; 

        try {
            // Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // for detailed debug output
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->Username = $gmailid;
            $mail->Password = $gmailpassword;

            // Sender and recipient settings
            $mail->setFrom($gmailid, $gmailusername);
            $mail->addAddress($useremail, $username);
            $mail->addReplyTo($gmailid, $gmailusername); // to set the reply to

            // Setting the email content
            $mail->IsHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            $msg = "Enviamos un correo a su direccion con el enlace para recuperar su clave";
            // header("location:auth-login.php");
        } catch (Exception $e) {
            $ctrlrespuesta =  "Error enviado correo. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
       $ctrlrespuesta= "No existe Email";
    }
}


//$ctrlrespuesta=$sql ;

$datos= array(
'respuesta'=> $ctrlrespuesta,
'estatus'=> $ctrlestatus 
);

header('Content-Type: application/json');
echo json_encode($datos, JSON_FORCE_OBJECT);



?>