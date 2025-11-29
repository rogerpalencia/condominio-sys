<?php
// click.php — seguimiento de clic y redireccion a recurso
session_start();
require_once("core/config.php");
require_once("core/PDO.class.php");

// Leer token
$token = $_GET['t'] ?? null;
if (!$token) {
    die("Link invalido.");
}

try {
    $db = DB::getInstance();

    // Buscar info del link
    $sql = "SELECT id_email, link_target, link_payload, link_expira_at
              FROM email.cola
             WHERE link_token = :t
             LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':t' => $token]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Link no encontrado o invalido.");
    }

    // Validar expiracion
    if ($row['link_expira_at'] && strtotime($row['link_expira_at']) < time()) {
        die("El enlace ha expirado.");
    }

    $db->beginTransaction();

    // Actualizar cola
    $sqlUp = "UPDATE email.cola
                 SET estado = CASE WHEN estado IN ('enviado','abierto') THEN 'clic' ELSE estado END,
                     clic_en = NOW()
               WHERE id_email = :id";
    $stUp = $db->prepare($sqlUp);
    $stUp->execute([':id' => $row['id_email']]);

    // Insertar evento
    $sqlEvt = "INSERT INTO email.evento(id_email, tipo, meta_json)
               VALUES(:id, 'clic', '{}'::jsonb)";
    $stEvt = $db->prepare($sqlEvt);
    $stEvt->execute([':id' => $row['id_email']]);

    $db->commit();

    // Redirigir al recurso real
    // Aquí decides cómo resolver el destino:
    switch ($row['link_target']) {
        case 'recibo':
            // Ejemplo: ver_recibo.php?id=... (desde payload JSON)
            $payload = json_decode($row['link_payload'], true);
            $idRecibo = $payload['id_recibo'] ?? null;
            if ($idRecibo) {
                header("Location: ver_recibo.php?id=" . urlencode($idRecibo));
                exit;
            }
            break;

        case 'estado_cuenta':
            $payload = json_decode($row['link_payload'], true);
            $idInmueble = $payload['id_inmueble'] ?? null;
            if ($idInmueble) {
                header("Location: estado_cuenta.php?id_inmueble=" . urlencode($idInmueble));
                exit;
            }
            break;

        default:
            echo "Acceso valido, pero no hay recurso asociado.";
    }

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error interno: " . htmlspecialchars($e->getMessage());
}
