<?php
require_once("core/PDO.class.php");
header('Content-Type: application/json');

try {
    $conn = DB::getInstance();
    if (!$conn) { throw new Exception('Sin conexión a BD'); }

    // === Sanitizar y validar ===
    $id_condominio = isset($_POST['id_condominio']) && is_numeric($_POST['id_condominio']) ? (int)$_POST['id_condominio'] : 0;
    $id_inmueble   = isset($_POST['id_inmueble'])   && is_numeric($_POST['id_inmueble'])   ? (int)$_POST['id_inmueble']   : 0;

    $identificacion = trim($_POST['identificacion'] ?? '');
    $tipo           = isset($_POST['tipo']) && is_numeric($_POST['tipo']) ? (int)$_POST['tipo'] : 0;
    $torre          = trim($_POST['torre']   ?? '');
    $piso           = trim($_POST['piso']    ?? '');
    $calle          = trim($_POST['calle']   ?? '');
    $manzana        = trim($_POST['manzana'] ?? '');
    $alicuota_raw   = str_replace(',', '.', trim($_POST['alicuota'] ?? ''));
    $alicuota       = is_numeric($alicuota_raw) ? $alicuota_raw : null;

    if ($id_inmueble === 0 && $id_condominio <= 0) {
        throw new Exception('id_condominio inválido o ausente (revise sesión/hidden).');
    }
    if ($identificacion === '' || $tipo <= 0 || $alicuota === null) {
        throw new Exception('Datos obligatorios incompletos (identificación/tipo/alícuota).');
    }

    // Verifica condominio
    if ($id_inmueble === 0) { // solo en INSERT
        $chk = $conn->prepare("SELECT 1 FROM condominio WHERE id_condominio = :c");
        $chk->execute([':c' => $id_condominio]);
        if (!$chk->fetchColumn()) {
            throw new Exception('El condominio indicado no existe.');
        }
    }

    // === Nombre de tabla (ajusta aquí si tu tabla real es plural) ===
    $tabla = 'inmueble'; // o 'inmuebles'

    if ($id_inmueble > 0) {
        // ACTUALIZAR
        $sql = "UPDATE {$tabla}
                SET identificacion = :identificacion,
                    tipo           = :tipo,
                    torre          = :torre,
                    piso           = :piso,
                    calle          = :calle,
                    manzana        = :manzana,
                    alicuota       = :alicuota,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_inmueble = :id_inmueble";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_inmueble', $id_inmueble, PDO::PARAM_INT);
    } else {
        // INSERTAR
        $sql = "INSERT INTO {$tabla} (
                    id_condominio, identificacion, tipo, torre, piso, calle, manzana, alicuota, estado, fecha_creacion, fecha_actualizacion
                ) VALUES (
                    :id_condominio, :identificacion, :tipo, :torre, :piso, :calle, :manzana, :alicuota, true, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id_condominio', $id_condominio, PDO::PARAM_INT);
    }

    // Parámetros comunes (tipados)
    $stmt->bindValue(':identificacion', $identificacion, PDO::PARAM_STR);
    $stmt->bindValue(':tipo', $tipo, PDO::PARAM_INT);
    $stmt->bindValue(':torre', $torre !== '' ? $torre : null, $torre !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':piso', $piso !== '' ? $piso : null, $piso !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':calle', $calle !== '' ? $calle : null, $calle !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':manzana', $manzana !== '' ? $manzana : null, $manzana !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':alicuota', $alicuota, PDO::PARAM_STR);

    $stmt->execute();

    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
