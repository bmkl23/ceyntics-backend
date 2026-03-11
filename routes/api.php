<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CupboardController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\InventoryItemController;
use App\Http\Controllers\Api\BorrowController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\DashboardController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/items',                  [InventoryItemController::class, 'index']);
    Route::post('/items',                 [InventoryItemController::class, 'store']);
    Route::get('/items/{id}',             [InventoryItemController::class, 'show']);
    Route::put('/items/{id}',             [InventoryItemController::class, 'update']);
    Route::patch('/items/{id}/quantity',  [InventoryItemController::class, 'updateQuantity']);
    Route::patch('/items/{id}/status',    [InventoryItemController::class, 'updateStatus']);

    Route::get('/borrows',               [BorrowController::class, 'index']);
    Route::post('/borrows',              [BorrowController::class, 'store']);
    Route::get('/borrows/{id}',          [BorrowController::class, 'show']);
    Route::patch('/borrows/{id}/return', [BorrowController::class, 'processReturn']);

    Route::get('/logs', [ActivityLogController::class, 'index']);

    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users',     UserController::class);
        Route::apiResource('cupboards', CupboardController::class);
        Route::apiResource('places',    PlaceController::class);
        Route::delete('/items/{id}',    [InventoryItemController::class, 'destroy']);
    });
});