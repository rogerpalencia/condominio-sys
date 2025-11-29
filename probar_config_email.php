<?php
// sys/probar_config_email.php — enviar email de prueba usando config guardada
header('Content-Type: application/json');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Parámetros
$id_condominio = $_POST['id_condominio'] ?? null;
$destino       = $_POST['destino'] ?? null;

if (!$id_condominio || !$destino) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros']);
    exit;
}

// ==== BD ====
require_once "core/config.php";
require_once "core/PDO.class.php";
$conn = DB::getInstance();

$stmt = $conn->prepare("SELECT * FROM email.config WHERE id_condominio = :id LIMIT 1");
$stmt->execute([':id' => $id_condominio]);
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) {
    echo json_encode(['status' => 'error', 'message' => 'No hay configuración de email para este condominio.']);
    exit;
}

// ==== PHPMailer ====
require_once "assets/libs/PHPMailer/src/PHPMailer.php";
require_once "assets/libs/PHPMailer/src/SMTP.php";
require_once "assets/libs/PHPMailer/src/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$debugLog = '';

$claveSMTP = !empty($config['contrasena_enc'])
    ? base64_decode($config['contrasena_enc'])
    : '';




try {
    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->Port       = (int)$config['puerto'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['usuario'];
    $mail->Password = $claveSMTP;



    $mail->SMTPSecure = strtolower($config['seguridad']) === 'ssl' ? 'ssl' : 'tls';
    $mail->Timeout    = 20;

    // SSL relajado → evita problemas con certificados de cPanel/Exim
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ];

    // Debug capturado
    $mail->SMTPDebug   = 2;
    $mail->Debugoutput = function ($str, $level) use (&$debugLog) {
        $debugLog .= "[$level] $str\n";
    };

    // Cabeceras
    $mail->setFrom($config['from_email'], $config['from_name']);
    if (!empty($config['reply_to_email'])) {
        $mail->addReplyTo($config['reply_to_email'], $config['reply_to_name'] ?? '');
    }
    $mail->addAddress($destino);

    // Contenido
    $mail->Subject = "Prueba SMTP - Condominio #$id_condominio";
    $mail->Body    = "Hola 👋,\n\nEste es un correo de prueba enviado desde el sistema de condominios Rhodium.\n\nSi lo recibes, la configuración SMTP funciona.";

    $mail->send();

    echo json_encode([
        'status'  => 'ok',
        'message' => "Correo enviado a $destino",
        'config_used' => [
            'host' => $mail->Host,
            'port' => $mail->Port,
            'user' => $mail->Username,
            'security' => $mail->SMTPSecure,
        ],
        'debug'   => $debugLog
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => "Mailer Error: " . $mail->ErrorInfo,
        'exception' => $e->getMessage(),
        'config_used' => [
            'host' => $config['host'],
            'port' => $config['puerto'],
            'user' => $config['usuario'],
            'security' => $config['seguridad'],
        ],
        'debug'   => $debugLog
    ]);
}
