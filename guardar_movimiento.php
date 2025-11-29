<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';
require_once 'core/funciones.php';

$conn = DB::getInstance();
$func = new Funciones();

$id_usuario = (int)($_SESSION['userid'] ?? 0);

// Obtener el ID del condominio y la moneda base del usuario
$sql_condo = "SELECT c.id_condominio, c.id_moneda AS id_moneda_base
              FROM administradores a
              JOIN condominio c ON c.id_condominio = a.id_condominio
              WHERE a.id_usuario = :u AND a.estatus = TRUE
              LIMIT 1";
$stmt_condo = $conn->prepare($sql_condo);
$stmt_condo->execute([':u' => $id_usuario]);
$row_condo = $stmt_condo->fetch(PDO::FETCH_ASSOC);

if (!$row_condo) {
    echo json_encode(['success' => false, 'mensaje' => 'Usuario no asociado a un condominio']);
    exit;
}

$id_condominio = $row_condo['id_condominio'];
$id_moneda_base = $row_condo['id_moneda_base'];

// Validar que id_moneda_base sea válido
if (!$id_moneda_base) {
    echo json_encode(['success' => false, 'mensaje' => 'La moneda base del condominio no está configurada. Por favor, configúrela en la tabla condominio.']);
    exit;
}

// Recolectar datos del POST
$data = [
    'id_movimiento' => $_POST['id_movimiento'] ?? '',
    'id_cuenta' => $_POST['id_cuenta'] ?? '',
    'tipo_movimiento' => $_POST['tipo_movimiento'] ?? '',
    'monto' => $_POST['monto'] ?? 0,
    'fecha_movimiento' => $_POST['fecha_movimiento'] ?? date('Y-m-d'),
    'estado' => $_POST['estado'] ?? 'pendiente',
    'observacion' => $_POST['observacion'] ?? '',
    'tasa' => $_POST['tasa'] ?? 1.0,
    'id_moneda' => $_POST['id_moneda'] ?? ''
];

// Validación de campos obligatorios
if (!$data['id_cuenta'] || !$data['tipo_movimiento'] || !$data['monto'] || !$data['fecha_movimiento'] || !$data['id_moneda']) {
    echo json_encode(['success' => false, 'mensaje' => 'Campos obligatorios incompletos, incluyendo la moneda.']);
    exit;
}

// Convertir a tipos adecuados
$id_cuenta = $data['id_cuenta'] ? (int)$data['id_cuenta'] : null;
$id_moneda = $data['id_moneda'] ? (int)$data['id_moneda'] : null;
$id_movimiento = $data['id_movimiento'] ? (int)$data['id_movimiento'] : null;
$monto = (float)$data['monto'];
$tasa = (float)$data['tasa'];

// Validar y obtener tasa de cambio
if ($tasa <= 0 || !$tasa) {
    // Si la tasa no es válida, consultar tipo_cambio
    $stmt_tasa = $conn->prepare("SELECT tasa FROM tipo_cambio 
                                 WHERE id_moneda_origen = :id_moneda 
                                 AND id_moneda_destino = :id_moneda_base 
                                 ORDER BY fecha_actualizacion DESC LIMIT 1");
    $stmt_tasa->execute([
        ':id_moneda' => $id_moneda,
        ':id_moneda_base' => $id_moneda_base
    ]);
    $tasa_db = $stmt_tasa->fetchColumn();
    if ($tasa_db) {
        $tasa = (float)$tasa_db;
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'No se encontró tasa de cambio para las monedas seleccionadas. Actualice la tabla tipo_cambio.']);
        exit;
    }
} elseif ($id_moneda == $id_moneda_base) {
    $tasa = 1.0; // Si las monedas son iguales, tasa = 1
}

// Calcular monto_base
$monto_base = $monto * $tasa;

// Determinar si es inserción o actualización
if ($id_movimiento) {
    $sql = "UPDATE movimiento_cuenta SET 
                id_cuenta = :id_cuenta,
                tipo_movimiento = :tipo_movimiento,
                monto = :monto,
                fecha_movimiento = :fecha_movimiento,
                estado = :estado,
                descripcion = :descripcion,
                tasa = :tasa,
                id_moneda = :id_moneda,
                id_condominio = :id_condominio,
                monto_base = :monto_base
            WHERE id_movimiento = :id_movimiento";
} else {
    $sql = "INSERT INTO movimiento_cuenta (
                id_cuenta, tipo_movimiento, monto, fecha_movimiento, estado, descripcion, tasa, id_moneda, id_condominio, monto_base
            ) VALUES (
                :id_cuenta, :tipo_movimiento, :monto, :fecha_movimiento, :estado, :descripcion, :tasa, :id_moneda, :id_condominio, :monto_base
            )";
}

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id_cuenta', $id_cuenta, PDO::PARAM_INT);
$stmt->bindParam(':tipo_movimiento', $data['tipo_movimiento'], PDO::PARAM_STR);
$stmt->bindParam(':monto', $monto, PDO::PARAM_STR);
$stmt->bindParam(':fecha_movimiento', $data['fecha_movimiento'], PDO::PARAM_STR);
$stmt->bindParam(':estado', $data['estado'], PDO::PARAM_STR);
$stmt->bindParam(':descripcion', $data['observacion'], PDO::PARAM_STR);
$stmt->bindParam(':tasa', $tasa, PDO::PARAM_STR);
$stmt->bindParam(':id_moneda', $id_moneda, PDO::PARAM_INT);
$stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
$stmt->bindParam(':monto_base', $monto_base, PDO::PARAM_STR);

if ($id_movimiento) {
    $stmt->bindParam(':id_movimiento', $id_movimiento, PDO::PARAM_INT);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'mensaje' => 'Movimiento guardado exitosamente']);
} else {
    $errorInfo = $stmt->errorInfo();
    echo json_encode(['success' => false, 'mensaje' => 'Error al guardar el movimiento: ' . $errorInfo[2]]);
}
?>