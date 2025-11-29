<?php
require_once("core/config.php");
require_once("core/PDO.class.php");
header('Content-Type: application/json');

$id = $_POST['id_inmueble'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'ID no recibido']);
    exit;
}

$conn = DB::getInstance();
$sql = "SELECT * FROM inmueble WHERE id_inmueble = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($data);
