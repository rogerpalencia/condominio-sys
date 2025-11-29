<?php
// sys/email/track.php
require_once __DIR__ . '/../core/PDO.class.php';
$conn = DB::getInstance();

$t = $_GET['t'] ?? '';
if ($t !== '' && $conn) {
  try {
    // localizar el envío por pixel_token (puede estar en email.envio o email.cola->llegó a envio al momento de enviar)
    $st = $conn->prepare("SELECT id_envio FROM email.envio WHERE pixel_token=:t LIMIT 1");
    $st->execute([':t'=>$t]);
    if ($id = (int)$st->fetchColumn()) {
      $ins = $conn->prepare("INSERT INTO email.envio_evento(id_envio,tipo_evento,detalles) VALUES(:e,'apertura',NULL)");
      $ins->execute([':e'=>$id]);
    }
  } catch (Throwable $e) { /* silencio */ }
}

// Responder una imagen 1x1
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
