<?php
@session_start();
header('Content-Type: application/json');
require_once 'core/PDO.class.php';
require_once 'core/funciones.php';

// Verificar si la clase Funciones está cargada y tiene el método
if (!class_exists('Funciones') || !method_exists(new Funciones(), 'formato_moneda')) {
    function formato_moneda_temporal($valor, $moneda) {
        $valor = floatval($valor);
        return number_format($valor, 2, '.', ',') . ' ' . $moneda;
    }
} else {
    $func = new Funciones();
}

// Obtener conexión a la base de datos
$conn = DB::getInstance();

// Obtener ID del usuario autenticado
$id_usuario = (int)($_SESSION['userid'] ?? 0);

// Consultar el ID del condominio y la moneda base
$sql_condo = "SELECT c.id_condominio, c.id_moneda AS moneda_base
              FROM administradores a
              JOIN condominio c ON c.id_condominio = a.id_condominio
              WHERE a.id_usuario = :u AND a.estatus = TRUE
              LIMIT 1";
$stmt_condo = $conn->prepare($sql_condo);
$stmt_condo->execute([':u' => $id_usuario]);
$row_condo = $stmt_condo->fetch(PDO::FETCH_ASSOC);

if (!$row_condo) {
    echo json_encode(["data" => [], "recordsTotal" => 0, "recordsFiltered" => 0, "error" => "Usuario no asociado a un condominio"]);
    exit;
}

$id_condominio = $row_condo['id_condominio'];
$moneda_base_id = $row_condo['moneda_base'];

// Validar que id_moneda en condominio sea válido
if (!$moneda_base_id) {
    echo json_encode(["data" => [], "recordsTotal" => 0, "recordsFiltered" => 0, "error" => "La moneda base del condominio no está configurada. Por favor, configúrela en la tabla condominio."]);
    exit;
}

// Obtener el código de la moneda base para formateo
$sql_moneda_base = "SELECT codigo FROM moneda WHERE id_moneda = :id_moneda";
$stmt_moneda_base = $conn->prepare($sql_moneda_base);
$stmt_moneda_base->execute([':id_moneda' => $moneda_base_id]);
$moneda_base_codigo = $stmt_moneda_base->fetchColumn() ?: 'Bs'; // Fallback a 'Bs' si falla

// Obtener filtros de fecha (mes y año) desde el POST, con valores por defecto
$anio = $_POST['anio'] ?? date('Y');
$mes = $_POST['mes'] ?? date('m');

// Obtener parámetros de DataTable
$search = $_POST['search']['value'] ?? '';
$order_column = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
$order_dir = $_POST['order'][0]['dir'] ?? 'asc';
$start = (int)($_POST['start'] ?? 0);
$length = (int)($_POST['length'] ?? 10);

// Definir columnas ordenables
$columns = [
    'mc.fecha_movimiento',
    'c.nombre',
    'mc.tipo_movimiento',
    'mc.monto',
    'm.codigo',
    'mc.estado',
    'mc.descripcion'
];
$order_by = $columns[$order_column] ?? 'mc.fecha_movimiento'; // Valor por defecto si el índice no es válido

// Consulta base para contar registros totales
$sql_base = "FROM movimiento_cuenta mc
             LEFT JOIN moneda m ON mc.id_moneda = m.id_moneda
             LEFT JOIN cuenta c ON mc.id_cuenta = c.id_cuenta
             WHERE mc.id_condominio = :id_condominio
               AND EXTRACT(MONTH FROM mc.fecha_movimiento) = :mes
               AND EXTRACT(YEAR FROM mc.fecha_movimiento) = :anio";

// Contar registros totales filtrados
$sql_count = "SELECT COUNT(*) " . $sql_base;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
$stmt_count->bindParam(':mes', $mes, PDO::PARAM_INT);
$stmt_count->bindParam(':anio', $anio, PDO::PARAM_INT);
if ($search) {
    $search = "%$search%";
    $sql_count .= " AND (
        c.nombre ILIKE :search OR
        mc.tipo_movimiento ILIKE :search OR
        mc.estado ILIKE :search OR
        mc.descripcion ILIKE :search
    )";
    $stmt_count->bindParam(':search', $search, PDO::PARAM_STR);
}
$stmt_count->execute();
$total_filtered = $stmt_count->fetchColumn() ?: 0;

// Consulta para obtener datos paginados
if ($search) {
    $sql_data = "SELECT mc.id_movimiento, mc.fecha_movimiento, c.nombre AS cuenta, mc.tipo_movimiento,
                 mc.monto, m.codigo AS moneda_codigo, mc.tasa, mc.monto_base, mc.estado, mc.descripcion, c.id_cuenta, m.id_moneda
                 $sql_base
                 AND (
                     c.nombre ILIKE :search OR
                     mc.tipo_movimiento ILIKE :search OR
                     mc.estado ILIKE :search OR
                     mc.descripcion ILIKE :search
                 )
                 ORDER BY $order_by $order_dir
                 LIMIT :length OFFSET :start";
    $stmt_data = $conn->prepare($sql_data);
    $stmt_data->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
    $stmt_data->bindParam(':mes', $mes, PDO::PARAM_INT);
    $stmt_data->bindParam(':anio', $anio, PDO::PARAM_INT);
    $stmt_data->bindParam(':search', $search, PDO::PARAM_STR);
    $stmt_data->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt_data->bindParam(':start', $start, PDO::PARAM_INT);
} else {
    $sql_data = "SELECT mc.id_movimiento, mc.fecha_movimiento, c.nombre AS cuenta, mc.tipo_movimiento,
                 mc.monto, m.codigo AS moneda_codigo, mc.tasa, mc.monto_base, mc.estado, mc.descripcion, c.id_cuenta, m.id_moneda
                 $sql_base
                 ORDER BY $order_by $order_dir
                 LIMIT :length OFFSET :start";
    $stmt_data = $conn->prepare($sql_data);
    $stmt_data->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
    $stmt_data->bindParam(':mes', $mes, PDO::PARAM_INT);
    $stmt_data->bindParam(':anio', $anio, PDO::PARAM_INT);
    $stmt_data->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt_data->bindParam(':start', $start, PDO::PARAM_INT);
}
$stmt_data->execute();

$rows = [];
while ($row = $stmt_data->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = [
        'id_movimiento' => $row['id_movimiento'],
        'fecha_movimiento' => $row['fecha_movimiento'] ?: null,
        'tipo_movimiento' => ucfirst($row['tipo_movimiento']),
        'monto' => isset($func) ? $func->formato_moneda($row['monto'] ?? 0, $row['moneda_codigo']) : formato_moneda_temporal($row['monto'] ?? 0, $row['moneda_codigo']),
        'moneda' => $row['moneda_codigo'], // Símbolo de la moneda del movimiento
        'tasa' => $row['tasa'] ? number_format($row['tasa'], 6) : '0.000000', // Tasa de conversión a moneda base
        'monto_base' => isset($func) ? $func->formato_moneda($row['monto_base'] ?? 0, $moneda_base_codigo) : formato_moneda_temporal($row['monto_base'] ?? 0, $moneda_base_codigo), // Moneda base
        'cuenta' => $row['cuenta'] ?? 'Sin cuenta',
        'estado' => $row['estado'] ?? 'Desconocido',
        'observacion' => $row['descripcion'] ?? '-', // Alineado con guardar_movimiento.php
        'id_cuenta' => $row['id_cuenta']
    ];
}

// Devolver respuesta en formato JSON para DataTable
echo json_encode([
    "draw" => intval($_POST['draw'] ?? 0),
    "recordsTotal" => $total_filtered,
    "recordsFiltered" => $total_filtered,
    "data" => $rows,
    "error" => null // Añadido para manejar errores
]);
?>