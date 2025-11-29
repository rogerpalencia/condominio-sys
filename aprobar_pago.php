<?php
/*******************************
 *  Archivo: aprobar_pago.php
 *  Descripción: Script para aprobar un pago en el módulo de conciliación.
 *               Actualiza el estado del recibo a 'aprobado' y realiza las
 *               operaciones necesarias en notificacion_cobro.
 *  Entrada: id_recibo, id_condominio, id_usuario (POST)
 *  Respuesta: JSON con estado y mensaje para SweetAlert.
 *******************************/

// Iniciar sesión para acceder a variables de sesión
session_start();

// Configurar para que los errores se escriban en un archivo de log y no se muestren en pantalla
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'logs/aprobar_pago.log');

// Log inicial
error_log('Iniciando aprobar_pago.php');

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Error: Método no permitido. Se esperaba POST.');
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Se esperaba POST.'
    ]);
    exit;
}

// Verificar parámetros requeridos
$required_params = ['id_recibo', 'id_condominio', 'id_usuario'];
foreach ($required_params as $param) {
    if (!isset($_POST[$param])) {
        error_log("Error: $param no proporcionado en la solicitud.");
        echo json_encode([
            'status' => 'error',
            'message' => "Parámetro $param no proporcionado en la solicitud."
        ]);
        exit;
    }
    // Permitir que id_condominio sea 0 si es un valor válido en tu sistema
    if ($_POST[$param] === '' || ($_POST[$param] != '0' && (int)$_POST[$param] <= 0)) {
        error_log("Error: $param tiene un valor no válido: " . $_POST[$param]);
        echo json_encode([
            'status' => 'error',
            'message' => "Parámetro $param tiene un valor no válido: " . $_POST[$param]
        ]);
        exit;
    }
}

$id_recibo = (int)$_POST['id_recibo'];
$id_condominio = (int)$_POST['id_condominio'];
$id_usuario = (int)$_POST['id_usuario'];
error_log("Parámetros recibidos - ID Recibo: $id_recibo, ID Condominio: $id_condominio, ID Usuario: $id_usuario");

// Verificar que el usuario esté autenticado y coincida con el enviado
if (!isset($_SESSION['userid']) || (int)$_SESSION['userid'] !== $id_usuario) {
    error_log('Error: Sesión no válida o usuario no autenticado.');
    echo json_encode([
        'status' => 'error',
        'message' => 'Sesión no válida o usuario no autenticado.'
    ]);
    exit;
}

// Verificar permisos del usuario (por ejemplo, debe ser administrador)
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  //  error_log('Error: Permisos insuficientes. Se requiere rol de administrador.');
  //  echo json_encode([
  //      'status' => 'error',
  //      'message' => 'Permisos insuficientes. Se requiere rol de administrador.'
  //  ]);
  //  exit;
}

// Incluir dependencias
require_once 'core/PDO.class.php';

// Conectar a la base de datos
$db = DB::getInstance();
if ($db === null) {
    error_log('Error: No se pudo conectar a la base de datos.');
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo conectar a la base de datos.'
    ]);
    exit;
}
error_log('Conexión a la base de datos establecida');

// Iniciar transacción para asegurar consistencia
try {
    $db->beginTransaction();

    // Verificar que el recibo exista, esté en estado 'en_revision', y pertenezca al condominio
    $sql = "SELECT estado, id_propietario, id_condominio 
            FROM recibo_cabecera 
            WHERE id_recibo = :id_recibo FOR UPDATE";
    $recibo = $db->row($sql, ['id_recibo' => $id_recibo]);
    if (!$recibo) {
        throw new Exception('Recibo no encontrado: ID ' . $id_recibo);
    }

    if ($recibo['estado'] !== 'en_revision') {
        throw new Exception('El recibo no está en estado "en_revision". Estado actual: ' . $recibo['estado']);
    }

    if ((int)$recibo['id_condominio'] !== $id_condominio) {
        throw new Exception('El recibo no pertenece al condominio especificado.');
    }

    $id_propietario = (int)$recibo['id_propietario'];

    // Actualizar el estado del recibo a 'aprobado' y registrar quién lo aprobó
    $sql = "UPDATE recibo_cabecera 
            SET estado = 'aprobado', 
                fecha_actualizacion = NOW(),
                id_usuario = :id_usuario
            WHERE id_recibo = :id_recibo";
    $params = [
        'id_recibo' => $id_recibo,
        'id_usuario' => $id_usuario
    ];
    $affected_rows = $db->query($sql, $params);
    if ($affected_rows === false || $affected_rows === 0) {
        throw new Exception('No se pudo actualizar el estado del recibo.');
    }
    error_log('Recibo ID ' . $id_recibo . ' actualizado a estado "aprobado" por usuario ID ' . $id_usuario);

    // Obtener las notificaciones asociadas al recibo desde recibo_destino_fondos
    $sql = "SELECT id_notificacion, monto_aplicado 
            FROM recibo_destino_fondos 
            WHERE id_recibo = :id_recibo";
    $notificaciones = $db->query($sql, ['id_recibo' => $id_recibo]);
    if ($notificaciones === false) {
        throw new Exception('Error al obtener las notificaciones asociadas al recibo.');
    }

    // Actualizar el estado de las notificaciones
    foreach ($notificaciones as $notif) {
        $id_notificacion = (int)$notif['id_notificacion'];
        $monto_aplicado = (float)$notif['monto_aplicado'];

        // Obtener datos actuales de la notificación
        $sql = "SELECT monto_x_pagar, monto_pagado 
                FROM notificacion_cobro 
                WHERE id_notificacion = :id_notificacion FOR UPDATE";
        $notif_data = $db->row($sql, ['id_notificacion' => $id_notificacion]);
        if (!$notif_data) {
            throw new Exception('Notificación no encontrada: ID ' . $id_notificacion);
        }

        $monto_x_pagar = (float)$notif_data['monto_x_pagar'];
        $monto_pagado = (float)$notif_data['monto_pagado'];
        $nuevo_monto_pagado = $monto_pagado + $monto_aplicado;

        // Determinar el nuevo estado de la notificación
        $nuevo_estado = 'pendiente';
        if ($nuevo_monto_pagado >= $monto_x_pagar) {
            $nuevo_estado = 'pagada';
        } elseif ($nuevo_monto_pagado > 0) {
            $nuevo_estado = 'parcialmente_pagada';
        }

        // Actualizar la notificación
        $sql = "UPDATE notificacion_cobro 
                SET monto_pagado = :monto_pagado, 
                    estado = :estado, 
                    fecha_actualizacion = NOW() 
                WHERE id_notificacion = :id_notificacion";
        $params = [
            'monto_pagado' => $nuevo_monto_pagado,
            'estado' => $nuevo_estado,
            'id_notificacion' => $id_notificacion
        ];
        $affected_rows = $db->query($sql, $params);
        if ($affected_rows === false || $affected_rows === 0) {
            throw new Exception('No se pudo actualizar la notificación ID ' . $id_notificacion);
        }
        error_log('Notificación ID ' . $id_notificacion . ' actualizada: monto_pagado=' . $nuevo_monto_pagado . ', estado=' . $nuevo_estado);
    }

    // Confirmar transacción
    $db->commit();
    error_log('Transacción completada exitosamente');

    // Enviar respuesta de éxito
    echo json_encode([
        'status' => 'success',
        'message' => 'Pago aprobado correctamente.'
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $db->rollBack();
    error_log('Error: ' . $e->getMessage());

    // Enviar respuesta de error
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al aprobar el pago: ' . $e->getMessage()
    ]);
}
?>