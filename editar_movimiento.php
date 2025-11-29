<?php
// editar_movimiento.php
// version 1.3
// al cambiar debes modificar las versiones.

@session_start();
require_once 'core/PDO.class.php';
require_once 'core/funciones.php';

header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    $id_movimiento = (int)($_GET['id'] ?? 0);
    $id_condominio = (int)(isset($_SESSION['id_condominio']) ? $_SESSION['id_condominio'] : 0);

    // Forzar un valor predeterminado si id_condominio es inválido
    if ($id_condominio <= 0) {
        $id_condominio = 1; // Reemplaza con un id_condominio válido o redirige al login
        file_put_contents('debug_edit.log', 'ADVERTENCIA: id_condominio forzado a ' . $id_condominio . PHP_EOL, FILE_APPEND);
    }

    if ($id_movimiento <= 0) {
        throw new Exception('ID de movimiento no válido.');
    }

    // Consulta para obtener los datos del movimiento
    $sql = "SELECT mg.*, 
                   (SELECT COALESCE(SUM(monto_base), 0) FROM movimiento_detalle_ingreso WHERE id_movimiento_general = mg.id_movimiento) +
                   (SELECT COALESCE(SUM(monto_base), 0) FROM movimiento_detalle_egreso WHERE id_movimiento_general = mg.id_movimiento) AS monto_base_total
            FROM movimiento_general mg
            WHERE mg.id_movimiento = :id AND mg.id_condominio = :cid";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_movimiento, ':cid' => $id_condominio]);
    $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$movimiento) {
        throw new Exception('Movimiento no encontrado. Verifique el ID o el condominio: id=' . $id_movimiento . ', cid=' . $id_condominio);
    }

    // Obtener detalles con todos los campos necesarios
    $detalles = [];
    if ($movimiento['tipo_movimiento'] === 'ingreso') {
        $sql = "SELECT id_cuenta, id_plan_cuenta, monto, tasa, monto_base, referencia, id_moneda
                FROM movimiento_detalle_ingreso
                WHERE id_movimiento_general = :id";
    } else {
        $sql = "SELECT id_cuenta, id_plan_cuenta, monto_aplicado AS monto, tasa, monto_base, descripcion AS referencia, id_moneda
                FROM movimiento_detalle_egreso
                WHERE id_movimiento_general = :id";
    }
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id_movimiento]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($detalles)) {
        $movimiento['detalles'] = [['id_cuenta' => '', 'id_moneda' => '', 'monto' => 0, 'tasa' => 1, 'monto_base' => 0, 'referencia' => '', 'id_plan_cuenta' => '']];
    } else {
        $movimiento['detalles'] = $detalles;
    }

    file_put_contents('debug_edit.log', print_r($movimiento, true) . PHP_EOL, FILE_APPEND);
    echo json_encode($movimiento);
} catch (Exception $e) {
    http_response_code(500);
    $error = ['status' => 'error', 'message' => $e->getMessage()];
    file_put_contents('debug_edit.log', print_r($error, true) . PHP_EOL, FILE_APPEND);
    echo json_encode($error);
}
?>