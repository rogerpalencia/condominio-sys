<?php
@session_start();
require_once 'core/PDO.class.php';

$conn = DB::getInstance();

$id_inmueble = isset($_POST['id_inmueble']) ? (int)$_POST['id_inmueble'] : 0;
if ($id_inmueble <= 0) {
    echo json_encode(['cuentas' => [], 'creditos' => []]);
    exit;
}

// 1. Buscar el condominio y moneda base
$sql = "SELECT c.id_condominio, c.id_moneda
        FROM inmueble i
        INNER JOIN condominio c ON i.id_condominio = c.id_condominio
        WHERE i.id_inmueble = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_inmueble]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['cuentas' => [], 'creditos' => []]);
    exit;
}

$id_condominio = (int)$row['id_condominio'];
$id_moneda_base = (int)$row['id_moneda'];

// 2. Traer las cuentas bancarias
$sql = "SELECT c.id_cuenta, c.nombre, c.tipo, c.banco, m.codigo AS moneda, m.id_moneda
        FROM cuenta c
        INNER JOIN moneda m ON c.id_moneda = m.id_moneda
        WHERE c.id_condominio = :id
        ORDER BY c.banco, c.nombre";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_condominio]);
$cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Asignar tasas a las cuentas
foreach ($cuentas as &$cuenta) {
    $id_moneda = (int)$cuenta['id_moneda'];
    if ($id_moneda === $id_moneda_base) {
        $cuenta['tasa'] = 1;
        $cuenta['moneda_base'] = true;
    } else {
        $sql_tasa = "SELECT tasa FROM tipo_cambio
                     WHERE id_moneda_origen = :o AND id_moneda_destino = :d
                     ORDER BY fecha_vigencia DESC LIMIT 1";
        $stmt_tasa = $conn->prepare($sql_tasa);
        $stmt_tasa->execute([':o' => $id_moneda, ':d' => $id_moneda_base]);
        $cuenta['tasa'] = (float)$stmt_tasa->fetchColumn() ?: 1;
        $cuenta['moneda_base'] = false;
    }
}
unset($cuenta);

// 3. Buscar los créditos disponibles
$sql_creditos = "SELECT 
                    c.id_moneda, 
                    m.codigo AS moneda, 
                    SUM(c.monto) AS saldo
                 FROM credito_a_favor c
                 JOIN moneda m ON c.id_moneda = m.id_moneda
                 WHERE c.id_inmueble = :id_inmueble
                   AND c.estado = 'activo'
                 GROUP BY c.id_moneda, m.codigo";

$stmt_creditos = $conn->prepare($sql_creditos);
$stmt_creditos->execute([':id_inmueble' => $id_inmueble]);
$creditos = [];

while ($row = $stmt_creditos->fetch(PDO::FETCH_ASSOC)) {
    $id_moneda = (int)$row['id_moneda'];

    if ($id_moneda !== $id_moneda_base) {  // <<< CORREGIDO
        
        $q = "SELECT tasa FROM tipo_cambio
              WHERE id_moneda_origen = :o AND id_moneda_destino = :d
              ORDER BY fecha_vigencia DESC LIMIT 1";
        $stmt_tasa = $conn->prepare($q);
        $stmt_tasa->execute([
            ':o' => $id_moneda,
            ':d' => $id_moneda_base
        ]);
        $tasa = $stmt_tasa->fetchColumn();
        $tasa = $tasa ? (float)$tasa : 1;
    } else {
        $tasa = 1;
    }

    $creditos[] = [
        'id_moneda' => $row['id_moneda'],
        'moneda' => $row['moneda'],
        'saldo' => (float)$row['saldo'],
        'tasa' => $tasa,
        'moneda_base' => ($id_moneda === $id_moneda_base) ? 1 : 0
    ];
}


unset($credito);

// 4. Enviar respuesta
echo json_encode([
    'cuentas' => $cuentas,
    'creditos' => $creditos
]);
