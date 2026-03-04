<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->prefix('admin')->group(function (): void {
    Route::get('/health', function () {
        return ApiResponse::success(['status' => 'ok']);
    });

    Route::post('/login', [\App\Http\Controllers\Api\Admin\AuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Api\Admin\AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
    Route::get('/me', [\App\Http\Controllers\Api\Admin\AuthController::class, 'me'])
        ->middleware(['auth:sanctum', 'restaurant.scoped']);
});
