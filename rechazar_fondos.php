<?php
session_start();
require_once 'core/PDO.class.php';

/**
 * rechazar_fondos.php
 * Procesa la anulación de un recibo, actualizando:
 * - recibo_cabecera: estado a 'anulado'.
 * - recibo_origen_fondos: estado a 'rechazado' para todos los orígenes asociados.
 * - No modifica notificacion_cobro ni credito_a_favor, ya que el recibo se anula sin aplicar pagos.
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/rechazar_fondos.log');

/* --------- utilidades rápidas --------- */
function jerr($msg)
{
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit;
}

/* --------- parámetros de entrada --------- */
$id_recibo = isset($_POST['id_recibo']) ? (int)$_POST['id_recibo'] : 0;
$id_condominio = isset($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;
$id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;

if (!$id_recibo || !$id_condominio || !$id_usuario) {
    error_log('Parámetros inválidos - id_recibo: ' . $id_recibo . ', id_condominio: ' . $id_condominio . ', id_usuario: ' . $id_usuario);
    jerr('Parámetros inválidos.');
}

/* --------- conexión DB --------- */
$db = DB::getInstance();
if (!$db) {
    error_log('No se pudo conectar a la base de datos.');
    jerr('No se pudo conectar a la base de datos.');
}

/* --------- validar recibo --------- */
$recibo = $db->row(
    "SELECT estado FROM recibo_cabecera WHERE id_recibo = :id_recibo AND id_condominio = :id_condominio",
    ['id_recibo' => $id_recibo, 'id_condominio' => $id_condominio]
);
if (!$recibo) {
    error_log('Recibo no encontrado para id_recibo: ' . $id_recibo);
    jerr('Recibo no encontrado.');
}
if ($recibo['estado'] !== 'en_revision') {
    error_log('El recibo no está en estado "en_revision" (estado actual: ' . $recibo['estado'] . ')');
    jerr('El recibo no puede ser rechazado porque no está en revisión.');
}

/* --------- comienzo de la transacción --------- */
$db->query('BEGIN');

try {
    // Actualizar estado del recibo a 'anulado'
    $db->query(
        "UPDATE recibo_cabecera
            SET estado = 'anulado',
                fecha_actualizacion = CURRENT_TIMESTAMP
          WHERE id_recibo = :id_recibo",
        ['id_recibo' => $id_recibo]
    );

    // Actualizar todos los orígenes de fondos a 'rechazado'
    $db->query(
        "UPDATE recibo_origen_fondos
            SET estado = 'rechazado',
                fecha_actualizacion = CURRENT_TIMESTAMP
          WHERE id_recibo = :id_recibo",
        ['id_recibo' => $id_recibo]
    );

    /* --------- commit y respuesta --------- */
    $db->query('COMMIT');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Recibo anulado correctamente.']);
} catch (Exception $e) {
    $db->query('ROLLBACK');
    error_log('Error en rechazar_fondos.php: ' . $e->getMessage());
    jerr('Error en el servidor: ' . $e->getMessage());
}
?>