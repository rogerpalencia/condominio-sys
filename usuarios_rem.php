<?php

include 'layouts/session.php';
$userid = $_SESSION['userid'];
require_once 'core/PDO.class.php';
$conn = DB::getInstance();
require_once 'core/funciones.php';
$func = new Funciones();
require_once 'layouts/vars.php';

$ctrlestatus = 0;
$ctrlrespuesta = 'Error en Transaccion';
$id = $_POST['id'];
if ($id !== 0) {
    $sql = "DELETE FROM usuarios WHERE id_usuario='$id'";
    $stmt = $conn->query($sql);
    $ctrlestatus = 1;
    $ctrlrespuesta = 'Se ha eliminado el Item ';
} else {
    $ctrlestatus = 0;
    $ctrlrespuesta = 'No se ha eliminado el Item';
}

$datos = [
    'respuesta' => $ctrlrespuesta,
    'estatus' => $ctrlestatus,
];

header('Content-Type: application/json');
echo json_encode($datos, JSON_FORCE_OBJECT);
