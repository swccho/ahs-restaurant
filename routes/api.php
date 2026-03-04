<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->prefix('admin')->group(function (): void {
    Route::get('/health', function () {
        return ApiResponse::success(['status' => 'ok']);
    });

    Route::post('/login', [\App\Http\Controllers\Api\Admin\AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin.restaurant.scope'])->group(function (): void {
        Route::post('/logout', [\App\Http\Controllers\Api\Admin\AuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\Api\Admin\AuthController::class, 'me']);
        Route::get('/_debug/scope', [\App\Http\Controllers\Api\Admin\DebugScopeController::class, 'scope']);
    });
});
