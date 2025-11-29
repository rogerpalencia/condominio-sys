<?php
// set_condominio.php
@session_start();
header('Content-Type: application/json');
require_once("core/PDO.class.php");

if (!isset($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada o usuario no autenticado']);
    exit;
}

$userid = (int)$_SESSION['userid'];
$id_condominio = isset($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;

if ($id_condominio <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID de condominio inválido']);
    exit;
}

try {
    $conn = DB::getInstance();

    // VALIDAR usando la VISTA (rol ∪ admin). No hay rastro de usuario_condominio.
    $sql = '
        SELECT 1
        FROM public.vw_condominios_del_usuario
        WHERE id_usuario = :uid AND id_condominio = :cid
        LIMIT 1
    ';
    $stmt = $conn->prepare($sql);
    $stmt->execute([':uid' => $userid, ':cid' => $id_condominio]);

    if ($stmt->fetchColumn() === false) {
        echo json_encode(['status' => 'error', 'message' => 'No autorizado para ese condominio']);
        exit;
    }

    $_SESSION['id_condominio'] = $id_condominio;
    echo json_encode(['status' => 'ok', 'id_condominio' => $id_condominio]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
}
