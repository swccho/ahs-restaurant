<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'restaurant.scoped' => \App\Http\Middleware\EnsureUserHasRestaurant::class,
            'admin.restaurant.scope' => \App\Http\Middleware\EnsureAdminRestaurantScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    $e->getMessage(),
                    $e->status,
                    $e->errors()
                );
            }
        });
        $exceptions->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error($e->getMessage() ?: 'This action is unauthorized.', 403);
            }
        });
    })->create();
