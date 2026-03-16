<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            // EMBED ROUTES DIRECTLY - NO file loading issues
            Route::post('/api/auth/login', [AuthController::class, 'login']);
            
            Route::middleware('auth:sanctum')->group(function () {
                Route::post('/api/auth/logout', [AuthController::class, 'logout']);
                Route::get('/api/auth/me', [AuthController::class, 'me']);
                // Add other routes as needed...
            });
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
