<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DebugScopeController extends AdminController
{
    public function scope(): JsonResponse
    {
        $user = Auth::user();
        return ApiResponse::success([
            'user_id' => $user->id,
            'restaurant_id' => $user->restaurant_id,
            'role' => $user->role,
        ]);
    }
}
