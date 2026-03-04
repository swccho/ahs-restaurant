<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RestaurantSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_path' => $this->logo_path,
            'logo_url' => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'cover_path' => $this->cover_path,
            'cover_url' => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null,
            'theme_color' => $this->theme_color,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'address' => $this->address,
            'google_map_url' => $this->google_map_url,
            'delivery_fee' => (float) $this->delivery_fee,
            'min_order_amount' => (float) $this->min_order_amount,
            'delivery_enabled' => (bool) $this->delivery_enabled,
            'pickup_enabled' => (bool) $this->pickup_enabled,
            'estimated_delivery_time' => $this->estimated_delivery_time,
            'opening_hours' => $this->opening_hours,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
