<?php
$root = dirname(__DIR__);
$autoloadCandidates = [
    $root . '/vendor/autoload.php',
    $root . '/supply_system/vendor/autoload.php',
];
$bootstrapCandidates = [
    $root . '/bootstrap/app.php',
    $root . '/supply_system/bootstrap/app.php',
];
$autoload = null;
foreach ($autoloadCandidates as $p) {
    if (file_exists($p)) { $autoload = $p; break; }
}
if ($autoload === null) { http_response_code(500); echo 'Autoload not found.'; exit; }
require_once $autoload;
$bootstrap = null;
foreach ($bootstrapCandidates as $p) {
    if (file_exists($p)) { $bootstrap = $p; break; }
}
if ($bootstrap === null) { http_response_code(500); echo 'Bootstrap app not found.'; exit; }
$app = require_once $bootstrap;
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);