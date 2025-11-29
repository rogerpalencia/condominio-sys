<?php
include "core/config.php";
include "core/funciones.php";

$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
    die(json_encode(["status" => "error", "error" => "Conexion Fallida"]));
    exit();
}

$tabla = $_POST['tabla'] ?? '';
$campo = $_POST['campo'] ?? '';
$valor = $_POST['valor'] ?? '';
$id = $_POST['id'] ?? '';

if (!$tabla || !$campo || !$id) {
    echo json_encode(["status" => "error", "error" => "Datos incompletos"]);
    exit;
}

$query = "UPDATE $tabla SET $campo = $1 WHERE id = $2";
$result = pg_query_params($conn, $query, [$valor, $id]);

if ($result) {
    echo json_encode(["status" => "ok"]);
} else {
    echo json_encode(["status" => "error", "error" => "Error al actualizar"]);
}

pg_close($conn);
