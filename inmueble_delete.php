<?php
require_once("core/config.php");
require_once("core/PDO.class.php");
header('Content-Type: application/json');
$conn = DB::getInstance();
$id = $_POST['id_inmueble'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID no recibido']);
    exit;
}

$conn = DB::getInstance();

try {
    $stmt = $conn->prepare("DELETE FROM inmueble WHERE id_inmueble = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    echo json_encode(['status' => 'ok']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
