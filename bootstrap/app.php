<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        using: function () {
            // INLINE LOGIN ROUTE - NO CONTROLLERS NEEDED
            Route::post('/api/auth/login', function (Request $request) {
                $request->validate([
                    'email' => 'required|email',
                    'password' => 'required',
                ]);

                $user = DB::table('users')
                    ->where('email', $request->email)
                    ->first();

                if (!$user || !Hash::check($request->password, $user->password)) {
                    throw ValidationException::withMessages([
                        'email' => ['Invalid credentials'],
                    ]);
                }

                // Simple token response (replace with Sanctum later)
                return response()->json([
                    'token' => base64_encode($user->id . ':' . $user->email),
                    'user' => $user
                ]);
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }
        });
    })->create();
