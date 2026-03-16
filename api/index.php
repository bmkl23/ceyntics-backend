<?php
// Add debug logging
error_log("API ENTRY POINT HIT");

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

error_log("BEFORE AUTOLOAD");
require __DIR__.'/../vendor/autoload.php';
error_log("AFTER AUTOLOAD");

$app = require __DIR__.'/../bootstrap/app.php';
error_log("APP LOADED");

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

error_log("ROUTE MATCHED: " . $request->path());
$response->send();
$kernel->terminate($request, $response);
