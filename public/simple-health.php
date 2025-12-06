<?php
// Ultra-simple health check that just returns OK
http_response_code(200);
header('Content-Type: text/plain');
echo 'OK';
exit;