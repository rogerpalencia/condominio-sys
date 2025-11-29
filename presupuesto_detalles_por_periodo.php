<?php
@session_start();
require_once 'core/PDO.class.php';
header('Content-Type: application/json; charset=utf-8');

ob_start();
$send = function($payload, $code=200){
    if (ob_get_length() !== false) { ob_clean(); }
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
};

try {
    $conn = DB::getInstance();
    if (!$conn) { $send(['status'=>'error','message'=>'No hay conexión DB'], 500); }

    $id_condominio = isset($_POST['id_condominio']) && ctype_digit($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;
    $anio          = isset($_POST['anio']) && ctype_digit($_POST['anio']) ? (int)$_POST['anio'] : 0;
    $mes           = isset($_POST['mes']) && ctype_digit($_POST['mes']) ? (int)$_POST['mes'] : -1;

    if ($id_condominio <= 0 || $anio <= 0 || $mes < 0) {
        $send(['status'=>'error','message'=>'Parámetros inválidos'], 400);
    }

    $sqlMaster = "
        SELECT id_notificacion_master
        FROM notificacion_cobro_master
        WHERE id_condominio = :c AND anio = :y AND mes = :m AND id_tipo = 1
        LIMIT 1
    ";
    $st = $conn->prepare($sqlMaster);
    $st->execute([':c'=>$id_condominio, ':y'=>$anio, ':m'=>$mes]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) { $send(['status'=>'error','message'=>'No existe PRESUPUESTO para ese periodo'], 404); }

    $id_master_pres = (int)$row['id_notificacion_master'];

    $sqlDet = "
        SELECT d.id_plan_cuenta,
               d.descripcion,
               COALESCE(d.tipo_movimiento, 'ingreso') AS tipo_movimiento
        FROM notificacion_cobro_detalle_master d
        WHERE d.id_notificacion_master = :id
        ORDER BY d.id_plan_cuenta, d.descripcion
    ";
    $st = $conn->prepare($sqlDet);
    $st->execute([':id'=>$id_master_pres]);
    $detalles = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$detalles) { $send(['status'=>'error','message'=>'El presupuesto no tiene detalles'], 404); }

    $send(['status'=>'ok','detalles'=>$detalles], 200);

} catch (Throwable $e) {
    $send(['status'=>'error','message'=>$e->getMessage()], 500);
}
