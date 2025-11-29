<?php
require_once "core/PDO.class.php";
$conn = DB::getInstance();
header('Content-Type: application/json');

$id_condominio = (int)($_GET['condominio'] ?? 0);
$anio = (int)($_GET['anio'] ?? 0);
$mes  = (int)($_GET['mes']  ?? 0);

$sql = "SELECT id_notificacion, 
               CASE
                   WHEN EXISTS (SELECT 1 FROM notificacion_cobro_detalle d
                                WHERE d.id_notificacion = nc.id_notificacion
                                  AND d.id_plan_cuenta::text <> '4.1')
                   THEN 'alicuota' ELSE 'fija' 
               END AS tipo_cuota
        FROM notificacion_cobro nc
        WHERE id_condominio = :cond AND anio = :anio AND mes = :mes
          AND id_tipo = 1 AND id_inmueble IS NULL
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':cond'=>$id_condominio,':anio'=>$anio,':mes'=>$mes]);
$cab = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cab) { echo json_encode(['status'=>'empty']); exit; }

$det = $conn->prepare("SELECT id_plan_cuenta, descripcion, monto
                       FROM notificacion_cobro_detalle
                       WHERE id_notificacion = :id");
$det->execute([':id'=>$cab['id_notificacion']]);
echo json_encode(['status'=>'ok','tipo'=>$cab['tipo_cuota'],'detalle'=>$det->fetchAll(PDO::FETCH_ASSOC)]);
