<?php session_start(); ?>

<?php
$userid = $_SESSION['userid'];

require_once 'core/PDO.class.php';
$conn = DB::getInstance();

require_once 'core/funciones.php';
$func = new Funciones();
require_once 'layouts/vars.php';

$ctrlrespuesta = 'Error en Transemail';
$ctrlemail = 0;
$parserJsn = PARSERJSN;
$ip = $_SERVER['REMOTE_ADDR'];
$id_usuario = $_POST['id_usuario'];
$n = $_POST['nombre'];
$nc = $_POST['nombrecompleto'];
$p = $_POST['perfil'];
$r = $_POST['rif'];
$c = sha1($_POST['clave']);

if (isset($_SESSION['userid'])) {
    $userid = $_SESSION['userid'];
    if (intval($id_usuario) !== 0) {
        $sql = "UPDATE usuarios SET nombrecompleto = '$nc', nombre = '$n', perfil = $p, rif='$r', clave = '$c' WHERE id_usuario = '$id_usuario'";
    } else {
        $sql = "INSERT INTO usuarios (nombre, nombrecompleto, perfil, rif, clave ) VALUES ('$n','$nc',$p,'$r','$c')";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ctrlemail = 1;
    $ctrlrespuesta = 'Datos Guardados';
}
$datos = [
    'respuesta' => $ctrlrespuesta,
    'email' => $ctrlemail,
];

//$ctrlrespuesta = $sql ;
$datos = [
    'respuesta' => $ctrlrespuesta,
    'email' => $ctrlemail,
];
if ($parserJsn == 0) {
    header('Content-Type: application/json');
}
echo json_encode($datos, JSON_FORCE_OBJECT);
?>