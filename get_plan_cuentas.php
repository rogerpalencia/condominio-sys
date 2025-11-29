<?php
require_once 'core/PDO.class.php';
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn) throw new Exception('No se pudo conectar a la base de datos');
    $id_condominio = (int)($_POST['id_condominio'] ?? 0);
    $tipo = $_POST['tipo'] ?? null;

    if ($id_condominio <= 0) throw new Exception('ID de condominio inválido');

    $sql = "SELECT id_plan, codigo, nombre, tipo
            FROM plan_cuenta
            WHERE id_condominio = :c AND estado = TRUE";
    $params = [':c' => $id_condominio];
    if ($tipo && in_array($tipo, ['ingreso', 'egreso'])) {
        $sql .= " AND tipo = :tipo";
        $params[':tipo'] = $tipo;
    }
    $sql .= " ORDER BY tipo, codigo";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $options = '<option value="">Seleccione</option>';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tipo_label = ($row['tipo'] == 'ingreso') ? '[Ingreso]' : '[Egreso]';
        $options .= '<option value="' . $row['id_plan'] . '" data-tipo="' . $row['tipo'] . '">' .
                    htmlspecialchars($row['codigo'] . ' - ' . $row['nombre'] . ' ' . $tipo_label) . '</option>';
    }

    echo json_encode(['status' => 'ok', 'options' => $options]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>