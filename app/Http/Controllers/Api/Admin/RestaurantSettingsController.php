<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\UpdateRestaurantSettingsRequest;
use App\Http\Resources\RestaurantSettingsResource;
use App\Http\Responses\ApiResponse;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RestaurantSettingsController extends AdminController
{
    public function show(): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($this->restaurantId());
        $this->authorize('view', $restaurant);

        return ApiResponse::success(
            new RestaurantSettingsResource($restaurant),
            'Settings fetched.'
        );
    }

    public function update(UpdateRestaurantSettingsRequest $request): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($this->restaurantId());
        $this->authorize('update', $restaurant);

        DB::transaction(function () use ($request, $restaurant): void {
            $restaurant->update([
                'name' => $request->validated('name'),
                'slug' => $request->validated('slug'),
                'logo_path' => $request->filled('logo_path') ? $request->validated('logo_path') : $restaurant->logo_path,
                'cover_path' => $request->filled('cover_path') ? $request->validated('cover_path') : $restaurant->cover_path,
                'theme_color' => $request->validated('theme_color'),
                'phone' => $request->validated('phone'),
                'whatsapp' => $request->validated('whatsapp'),
                'email' => $request->validated('email'),
                'address' => $request->validated('address'),
                'google_map_url' => $request->validated('google_map_url'),
                'delivery_fee' => $request->validated('delivery_fee'),
                'min_order_amount' => $request->validated('min_order_amount'),
                'delivery_enabled' => $request->boolean('delivery_enabled', (bool) $restaurant->delivery_enabled),
                'pickup_enabled' => $request->boolean('pickup_enabled', (bool) $restaurant->pickup_enabled),
                'estimated_delivery_time' => $request->validated('estimated_delivery_time'),
                'opening_hours' => $request->validated('opening_hours'),
            ]);
        });

        return ApiResponse::success(
            new RestaurantSettingsResource($restaurant->fresh()),
            'Settings updated.'
        );
    }
}
