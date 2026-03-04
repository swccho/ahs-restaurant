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

        Route::post('/uploads/menu-item-image', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadMenuItemImage']);

        Route::prefix('menu-items')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'store']);
            Route::get('/{menuItem}', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'show']);
            Route::put('/{menuItem}', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'update']);
            Route::delete('/{menuItem}', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'destroy']);
            Route::patch('/{menuItem}/availability', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'availability']);
            Route::patch('/{menuItem}/featured', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'featured']);
        });
    });
});
