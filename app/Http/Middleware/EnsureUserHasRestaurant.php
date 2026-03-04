<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRestaurant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->restaurant_id) {
            return ApiResponse::error('User is not assigned to a restaurant.', 403);
        }
        $request->merge(['restaurant_id' => $user->restaurant_id]);
        return $next($request);
    }
}
