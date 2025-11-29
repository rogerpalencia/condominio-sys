<?php
// core/condominio_context.php
// Requiere session_start() invocado antes en el script principal.

if (!isset($_SESSION['userid'])) {
    die('Error: Sesión no iniciada.');
}

$current_id_condominio = isset($_SESSION['id_condominio']) ? (int)$_SESSION['id_condominio'] : null;

/**
 * Devuelve el id_condominio actual (o null si no está definido).
 */
function get_id_condominio_actual() {
    return isset($_SESSION['id_condominio']) ? (int)$_SESSION['id_condominio'] : null;
}
