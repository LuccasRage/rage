<?php
// Test endpoint to verify auth routing is working
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

echo json_encode([
    'status' => 'success',
    'message' => 'Auth routing is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'request_uri' => $_SERVER['REQUEST_URI'],
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>