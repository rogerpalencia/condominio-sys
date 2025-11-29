<?php
session_start();
require_once 'core/PDO.class.php';

$db = DB::getInstance();
if ($db === null) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']);
    exit;
}

$id_condominio = isset($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;

$sql = "SELECT rc.id_recibo, rc.numero_recibo, rc.fecha_emision, rc.monto_total, rc.observaciones,
               p.nombre AS propietario, i.identificacion AS inmueble
        FROM recibo_cabecera rc
        JOIN propietario p ON rc.id_propietario = p.id_propietario
        JOIN propietario_inmueble pi ON p.id_propietario = pi.id_propietario
        JOIN inmueble i ON pi.id_inmueble = i.id_inmueble
        WHERE i.id_condominio = :id_condominio
        AND rc.estado = 'en_revision'";
$stmt = $db->prepare($sql);
$stmt->execute(['id_condominio' => $id_condominio]);
$recibos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($recibos);
?>