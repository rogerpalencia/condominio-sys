<?php
// update_usuario.php

require_once 'core/config.php';

$conn = pg_connect("host=$Servidor dbname=$BaseDeDatos user=$Usuario password=$Password");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que los campos necesarios estén presentes
    if (!isset($_POST['id_usuario'], $_POST['campo_modificado'], $_POST['nuevo_valor'])) {
        echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
        exit;
    }

    $id_usuario = $_POST['id_usuario'];
    $campo = $_POST['campo_modificado'];
    $nuevo_valor = $_POST['nuevo_valor'];

    // Definir campos permitidos para actualizar (lista blanca)
    $allowed_fields = ['nombre', 'nombres_y_apellidos', 'cedula', 'perfil', 'gerencia', 'cargo', 'activo'];

    if (!in_array($campo, $allowed_fields)) {
        echo json_encode(['success' => false, 'error' => 'Campo inválido.']);
        exit;
    }

    // Sanitizar `id_usuario` como entero
    $id_usuario = intval($id_usuario);

    // Preparar la consulta usando `pg_query_params` para prevenir inyección SQL en los valores
    // Sin embargo, no se puede parametrizar los nombres de columnas, por lo que es crucial usar una lista blanca
    $sql = "UPDATE usuarios SET $campo = $1 WHERE id_usuario = $2";
    $result = pg_query_params($conn, $sql, [$nuevo_valor, $id_usuario]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        // Para propósitos de depuración, puedes incluir el error de PostgreSQL
        echo json_encode(['success' => false, 'error' => pg_last_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método de solicitud inválido.']);
}
?>
