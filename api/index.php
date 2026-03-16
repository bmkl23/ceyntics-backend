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

// Set ALL writable paths to /tmp subdirs (Vercel-safe)
$app->useStoragePath('/tmp/storage');
$app->useCachePath('/tmp/cache');
$app->useViewPath('/tmp/views');  // Fix Blade compiler

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
