<?php
// track.php — pixel de seguimiento de apertura de email
session_start();
require_once("core/config.php");
require_once("core/PDO.class.php");

header("Content-Type: image/gif");

// Crear imagen transparente 1x1 (GIF)
$gifPixel = base64_decode(
    "R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=="
);

// Leer token
$token = $_GET['t'] ?? null;
if (!$token) {
    echo $gifPixel;
    exit;
}

try {
    $db = DB::getInstance();
    $db->beginTransaction();

    // Actualizar estado de cola si estaba enviado
    $sql = "UPDATE email.cola
               SET estado = CASE WHEN estado = 'enviado' THEN 'abierto' ELSE estado END,
                   abierto_en = NOW()
             WHERE tracking_token = :t";
    $st = $db->prepare($sql);
    $st->execute([':t' => $token]);

    // Insertar evento
    $sqlEvt = "INSERT INTO email.evento(id_email, tipo, meta_json)
               SELECT id_email, 'abierto', '{}'::jsonb
                 FROM email.cola
                WHERE tracking_token = :t";
    $stEvt = $db->prepare($sqlEvt);
    $stEvt->execute([':t' => $token]);

    $db->commit();
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    // No devolvemos error al cliente (pixel siempre debe responder)
}

// Entregar pixel
echo $gifPixel;
