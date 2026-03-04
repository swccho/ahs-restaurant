<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRestaurantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated.', 401);
        }

        if (! $user->restaurant_id) {
            return ApiResponse::error('User is not assigned to a restaurant.', 403);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return ApiResponse::error('Account is inactive.', 403);
        }

        $user->loadMissing('restaurant');
        $restaurant = $user->restaurant;
        if (! $restaurant) {
            return ApiResponse::error('Restaurant not found.', 403);
        }

        if (isset($restaurant->is_active) && ! $restaurant->is_active) {
            return ApiResponse::error('Restaurant is not active.', 403);
        }

        $request->merge(['restaurant_id' => $user->restaurant_id]);
        return $next($request);
    }
}
