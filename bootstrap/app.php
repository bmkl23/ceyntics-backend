<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // FORCE LOAD ROUTES - Vercel-safe absolute paths
            $apiRoutes = __DIR__.'/../routes/api.php';
            $webRoutes = __DIR__.'/../routes/web.php';
            
            if (file_exists($apiRoutes)) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group($apiRoutes);
            }
            
            if (file_exists($webRoutes)) {
                Route::middleware('web')
                    ->group($webRoutes);
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                return response()->json([
                    'message' => $e->getMessage(),
                    'exception' => get_class($e),
                ], $status);
            }
        });
    })->create();
