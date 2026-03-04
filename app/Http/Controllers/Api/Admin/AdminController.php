<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Auth;

abstract class AdminController extends Controller
{
    protected function restaurantId(): int
    {
        $id = Auth::user()?->restaurant_id;
        if ($id === null) {
            abort(ApiResponse::error('User is not assigned to a restaurant.', 403));
        }
        return $id;
    }

    protected function abortIfNotOwner(): void
    {
        if (! Auth::user()?->isOwner()) {
            abort(ApiResponse::error('This action is restricted to owners.', 403));
        }
    }
}
