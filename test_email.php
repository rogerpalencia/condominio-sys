<?php
// test_endpoint.php - Archivo simple para probar conectividad
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

echo json_encode([
    "status" => "ok",
    "message" => "Endpoint funcionando correctamente",
    "timestamp" => date('Y-m-d H:i:s'),
    "method" => $_SERVER['REQUEST_METHOD'],
    "php_version" => phpversion(),
    "server" => $_SERVER['SERVER_NAME'] ?? 'N/A',
    "port" => $_SERVER['SERVER_PORT'] ?? 'N/A',
    "request_uri" => $_SERVER['REQUEST_URI'] ?? 'N/A'
]);
?>