
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(dirname(__DIR__).'/routes/api.php');
        },
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