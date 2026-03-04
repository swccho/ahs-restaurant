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

        Route::prefix('categories')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'store']);
            Route::patch('/reorder', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'reorder']);
            Route::get('/{category}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'show']);
            Route::put('/{category}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'update']);
            Route::delete('/{category}', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'destroy']);
            Route::patch('/{category}/toggle', [\App\Http\Controllers\Api\Admin\CategoryController::class, 'toggle']);
        });
    });
});
