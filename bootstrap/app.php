<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
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