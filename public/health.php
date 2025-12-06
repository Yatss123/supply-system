<?php
// Simple health check that doesn't require Laravel framework
header('Content-Type: application/json');
http_response_code(200);

echo json_encode([
    'status' => 'ok',
    'timestamp' => date('c'),
    'service' => 'supply-system'
]);
?>