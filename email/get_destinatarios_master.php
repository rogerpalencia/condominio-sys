<?php
// sys/email/get_destinatarios_master.php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/PDO.class.php';

try {
    $id_condominio = isset($_GET['id_condominio']) ? (int)$_GET['id_condominio'] : 0;
    if ($id_condominio <= 0) {
        echo json_encode(['status'=>'error','message'=>'id_condominio inválido']); exit;
    }

    $conn = DB::getInstance();

$sql = "
  SELECT
    TRIM(u.correo) AS email,
    TRIM(COALESCE(p.nombre1,'')) || ' ' || TRIM(COALESCE(p.apellido1,'')) AS nombre,
    COALESCE(
      array_to_string(
        ARRAY(
          SELECT DISTINCT i2.identificacion
          FROM public.inmueble i2
          JOIN public.propietario_inmueble pi2 ON pi2.id_inmueble = i2.id_inmueble
          WHERE pi2.id_propietario = p.id_propietario
            AND i2.id_condominio = :c
          ORDER BY i2.identificacion
        ), ' / '
      ), ''
    ) AS inmuebles
  FROM public.inmueble i
  JOIN public.propietario_inmueble pi ON pi.id_inmueble = i.id_inmueble
  JOIN public.propietario p           ON p.id_propietario = pi.id_propietario
  JOIN menu_login.usuario u           ON u.id_usuario   = pi.id_usuario
  WHERE i.id_condominio = :c
    AND u.correo IS NOT NULL AND TRIM(u.correo) <> ''
  GROUP BY email, nombre, p.id_propietario
  ORDER BY email
";

    $st = $conn->prepare($sql);
    $st->execute([':c'=>$id_condominio]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    echo json_encode(['status'=>'ok','data'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[get_destinatarios_master] ' . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Error interno al obtener destinatarios']);
}
