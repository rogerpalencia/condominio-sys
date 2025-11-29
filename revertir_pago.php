<?php
session_start();
require_once 'core/PDO.class.php';

$db = DB::getInstance();
if ($db === null) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$id_recibo = isset($_POST['id_recibo']) ? (int)$_POST['id_recibo'] : 0;
if ($id_recibo <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID de recibo inválido']);
    exit;
}

// Verificar el estado del recibo
$sql = "SELECT estado FROM recibo_cabecera WHERE id_recibo = :id_recibo";
$row = $db->row($sql, ['id_recibo' => $id_recibo]);
if ($row === false || $row === null) {
    echo json_encode(['status' => 'error', 'message' => 'Recibo no encontrado']);
    exit;
}

if ($row['estado'] !== 'en_revision') {
    echo json_encode(['status' => 'error', 'message' => 'El recibo no está en estado de revisión']);
    exit;
}

// Actualizar el estado del recibo a 'revertido'
$sql = "UPDATE recibo_cabecera SET estado = 'anulado' WHERE id_recibo = :id_recibo";
$db->query($sql, ['id_recibo' => $id_recibo]);

echo json_encode(['status' => 'success', 'message' => 'Pago Anulado correctamente']);
?>