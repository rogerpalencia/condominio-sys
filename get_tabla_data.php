<?php

include "core/config.php";
include "core/funciones.php";

$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");
if (!$conn) {
	die("Conexion Fallida");
	exit();
}

$tabla = $_POST['tabla'] ?? '';
if (!$tabla) exit;

$result = pg_query($conn, "SELECT * FROM $tabla LIMIT 500");
if (!$result) {
	die("Error en la consulta");
}

$data = pg_fetch_all($result);
echo json_encode(["data" => $data]);
