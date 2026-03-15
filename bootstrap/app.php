<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Step 1 — create the app instance directly
$app = new Application(dirname(__DIR__));

// Step 2 — redirect to /tmp BEFORE routes are registered
if (!is_writable(dirname(__DIR__) . '/bootstrap/cache')) {
    foreach ([
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/views',
        '/tmp/storage/framework/testing',
        '/tmp/storage/logs',
        '/tmp/storage/app/public',
        '/tmp/bootstrap/cache',
    ] as $dir) {
        if (!is_dir($dir)) mkdir($dir, 0777, true);
    }
    $app->useStoragePath('/tmp/storage');
    $app->useBootstrapPath('/tmp/bootstrap');
}

// Step 3 — configure using the pre-configured app instance
return (new Illuminate\Foundation\Configuration\ApplicationBuilder($app))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, $request) {
            $status = method_exists($e, 'getStatusCode')
                ? $e->getStatusCode()
                : 500;
            return new \Illuminate\Http\JsonResponse([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ], $status);
        });
    })->create();