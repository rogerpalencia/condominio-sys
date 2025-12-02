<?php
// email/enviar_email.php — ENDPOINT POST de reenvío manual (UI). Sin lógica de cola.
header('Content-Type: application/json');

require_once __DIR__ . "/../core/config.php";
require_once __DIR__ . "/../core/PDO.class.php";
require_once __DIR__ . "/lib_email.php"; // compose_email(), send_via_smtp(), normalizar_tipo()

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status'=>'ready','message'=>'Use POST.']);
        exit;
    }

    // Parámetros
    $id_condominio = isset($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;
    $tipo          = isset($_POST['tipo']) ? normalizar_tipo((string)$_POST['tipo']) : '';
    $id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $destino       = isset($_POST['destino']) ? (string)$_POST['destino'] : 'default';
    $token         = isset($_POST['token']) ? (string)$_POST['token'] : '';
    $to_name_opt   = isset($_POST['nombre']) ? (string)$_POST['nombre'] : null; // opcional: personalizar saludo

    if ($id_condominio <= 0 || $tipo === '' || $id <= 0) {
        echo json_encode(['status'=>'error','message'=>'Parámetros obligatorios incompletos (id_condominio, tipo, id).']);
        exit;
    }

    // Validaciones por tipo
    if ($tipo === 'notificacion' && $token === '') {
        echo json_encode(['status'=>'error','message'=>'Falta token para notificación.']);
        exit;
    }
    if ($tipo === 'master_relacion') {
        // Para endpoint, exigimos un email destino explícito
        if ($destino === 'default' || trim($destino) === '') {
            echo json_encode(['status'=>'error','message'=>'Para master_relacion se requiere un email destino.']);
            exit;
        }
    }

    $db = DB::getInstance();

    // Config SMTP activa (tolerante a ambos esquemas)
    $config = get_smtp_config($db, $id_condominio);
    if (!$config) {
        echo json_encode(['status'=>'error','message'=>'No hay configuración de email activa']);
        exit;
    }

    // Componer
    $mailData = compose_email($db, $id_condominio, $tipo, $id, $token, $destino, $to_name_opt);

    // Para master_relacion, el compose no resuelve correo; debe venir en $destino
    if ($tipo === 'master_relacion') {
        $mailData['to_email'] = trim($destino);
        $mailData['to_name']  = $to_name_opt ? trim($to_name_opt) : ($mailData['to_name'] ?? '');
    }

    if (empty($mailData['to_email'])) {
        echo json_encode(['status'=>'error','message'=>'El destinatario está vacío.']);
        exit;
    }

    // Enviar
    $res = send_via_smtp($config, $mailData);
    if (!($res['ok'] ?? false)) {
        echo json_encode([
            'status'=>'error',
            'message'=>$res['error'] ?? 'No se pudo enviar',
            'exception'=>$res['exception'] ?? null
        ]);
        exit;
    }

    echo json_encode(['status'=>'ok','message'=>"Correo enviado a {$mailData['to_email']}", 'message_id'=>$res['message_id'] ?? null]);

} catch (Throwable $e) {
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
}
