<?php
// test_email.php — endpoint de verificación SMTP que envía un correo de prueba
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Entrada flexible (POST o querystring)
// El condominio 1 no existe en este entorno; se usa 5 por defecto
$id_condominio = isset($_REQUEST['id_condominio']) ? (int)$_REQUEST['id_condominio'] : 5;
$destino = trim($_REQUEST['destino'] ?? 'rogerpalencia@gmail.com');
$nombre_destino = trim($_REQUEST['nombre'] ?? 'Prueba SMTP');

if (!$destino) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Falta el correo destino (destino)'
    ]);
    exit;
}

require_once __DIR__ . '/email/lib_email.php';

try {
    $conn = DB::getInstance();
    $config = get_smtp_config($conn, $id_condominio);

    if (!$config) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No hay configuración SMTP activa para el condominio indicado.'
        ]);
        exit;
    }

    $ahora = date('Y-m-d H:i:s');
    $email = [
        'to_email' => $destino,
        'to_name'  => $nombre_destino,
        'subject'  => "Prueba SMTP condominio #{$id_condominio}",
        'html'     => "<p>Hola {$nombre_destino},</p><p>Esto es un correo de prueba generado por test_email.php a las {$ahora}.</p><p>Si lo recibes, la configuración SMTP funciona.</p>",
        'text'     => "Hola {$nombre_destino},\n\nEsto es un correo de prueba generado por test_email.php a las {$ahora}.\nSi lo recibes, la configuración SMTP funciona."
    ];

    $resultado = send_via_smtp($config, $email, true);

    echo json_encode([
        'status'  => $resultado['ok'] ? 'ok' : 'error',
        'message' => $resultado['ok'] ? 'Correo de prueba enviado (verifica tu bandeja)' : ($resultado['error'] ?? 'Error desconocido'),
        'email'   => [
            'to' => $email['to_email'],
            'subject' => $email['subject'],
        ],
        'config_usada' => [
            'host' => $config['host'] ?? null,
            'puerto' => $config['puerto'] ?? null,
            'usuario' => $config['usuario'] ?? null,
            'seguridad' => $config['seguridad'] ?? null,
            'from_email' => $config['from_email'] ?? null,
        ],
        'debug' => $resultado['debug'] ?? null,
        'sonda_tcp' => $resultado['probe'] ?? null,
        'exception' => $resultado['exception'] ?? null,
    ]);
} catch (Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Excepción no controlada durante el envío de prueba',
        'exception' => $e->getMessage(),
    ]);
}

