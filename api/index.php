<?php

ob_start();
header('Access-Control-Allow-Origin: https://ceyntics-frontend.vercel.app');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_flush();
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';

// CRITICAL: Set ALL writable paths AFTER app creation
$app->useStoragePath('/tmp/storage');
$app->useCachePath('/tmp/cache');
$app->useViewPath('/tmp/views');

// Pre-create tmp subdirs (Vercel allows /tmp writes)
$dirs = ['storage/framework/views', 'storage/framework/cache', 'storage/framework/sessions'];
foreach ($dirs as $dir) {
    $fullPath = '/tmp/' . $dir;
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0777, true);
    }
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
