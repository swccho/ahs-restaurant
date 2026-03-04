<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return ApiResponse::error('Invalid credentials.', 422);
        }

        $user = Auth::user();
        if (! $user->restaurant_id) {
            Auth::logout();
            return ApiResponse::error('User is not assigned to a restaurant.', 403);
        }

        $user->load('restaurant');
        return ApiResponse::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'restaurant' => [
                'id' => $user->restaurant->id,
                'name' => $user->restaurant->name,
            ],
            'session' => true,
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return ApiResponse::success(null, 'Logged out.');
    }

    public function me(): JsonResponse
    {
        $user = Auth::user();
        $user->load('restaurant');
        return ApiResponse::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'restaurant' => [
                'id' => $user->restaurant->id,
                'name' => $user->restaurant->name,
            ],
        ]);
    }
}
