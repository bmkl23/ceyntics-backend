<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        using: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(dirname(__DIR__).'/routes/api.php');

            Route::middleware('web')
                ->group(dirname(__DIR__).'/routes/web.php');
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