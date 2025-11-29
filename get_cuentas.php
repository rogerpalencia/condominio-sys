<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';

// Obtener conexión a la base de datos
$conn = DB::getInstance();

$id_usuario = (int)($_SESSION['userid'] ?? 0);

// Consultar el ID del condominio asociado al usuario
$sql_condo = "SELECT c.id_condominio
              FROM administradores a
              JOIN condominio c ON c.id_condominio = a.id_condominio
              WHERE a.id_usuario = :u AND a.estatus = TRUE
              LIMIT 1";
$stmt_condo = $conn->prepare($sql_condo);
$stmt_condo->execute([':u' => $id_usuario]);
$row_condo = $stmt_condo->fetch(PDO::FETCH_ASSOC);

if (!$row_condo) {
    echo json_encode([]);
    exit;
}

$id_condominio = $row_condo['id_condominio'];

// Consultar cuentas contables desde plan_cuenta asociadas al condominio
$sql = "SELECT id_plan AS id_cuenta, nombre
        FROM plan_cuenta
        WHERE id_condominio = :id_condominio AND estado = TRUE
        ORDER BY codigo"; // Ordenar por código para mantener la jerarquía
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
$stmt->execute();
$cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cuentas);
?>