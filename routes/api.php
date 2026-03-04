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

        Route::prefix('staff')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Api\Admin\StaffController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Admin\StaffController::class, 'store']);
            Route::get('/{user}', [\App\Http\Controllers\Api\Admin\StaffController::class, 'show']);
            Route::put('/{user}', [\App\Http\Controllers\Api\Admin\StaffController::class, 'update']);
            Route::patch('/{user}/toggle', [\App\Http\Controllers\Api\Admin\StaffController::class, 'toggle']);
            Route::delete('/{user}', [\App\Http\Controllers\Api\Admin\StaffController::class, 'destroy']);
        });

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
        Route::post('/uploads/offer-banner', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadOfferBanner']);
        Route::post('/uploads/restaurant-logo', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadRestaurantLogo']);
        Route::post('/uploads/restaurant-cover', [\App\Http\Controllers\Api\Admin\UploadController::class, 'uploadRestaurantCover']);

        Route::get('/settings', [\App\Http\Controllers\Api\Admin\RestaurantSettingsController::class, 'show']);
        Route::put('/settings', [\App\Http\Controllers\Api\Admin\RestaurantSettingsController::class, 'update']);

        Route::prefix('menu-items')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'store']);
            Route::get('/{menuItem}', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'show']);
            Route::put('/{menuItem}', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'update']);
            Route::delete('/{menuItem}', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'destroy']);
            Route::patch('/{menuItem}/availability', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'availability']);
            Route::patch('/{menuItem}/featured', [\App\Http\Controllers\Api\Admin\MenuItemController::class, 'featured']);
        });

        Route::prefix('offers')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Api\Admin\OfferController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Admin\OfferController::class, 'store']);
            Route::get('/{offer}', [\App\Http\Controllers\Api\Admin\OfferController::class, 'show']);
            Route::put('/{offer}', [\App\Http\Controllers\Api\Admin\OfferController::class, 'update']);
            Route::delete('/{offer}', [\App\Http\Controllers\Api\Admin\OfferController::class, 'destroy']);
            Route::patch('/{offer}/toggle', [\App\Http\Controllers\Api\Admin\OfferController::class, 'toggle']);
        });

        Route::prefix('orders')->group(function (): void {
            Route::get('/', [\App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
            Route::get('/{order}', [\App\Http\Controllers\Api\Admin\OrderController::class, 'show']);
            Route::patch('/{order}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'status']);
        });
    });
});
