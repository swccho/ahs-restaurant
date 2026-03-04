<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRestaurantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() && $this->user()?->restaurant_id !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $restaurant = $this->user()->restaurant;

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('restaurants', 'slug')->ignore($restaurant->id),
            ],
            'logo_path' => ['nullable', 'string', 'max:500'],
            'cover_path' => ['nullable', 'string', 'max:500'],
            'theme_color' => ['nullable', 'string', 'max:20', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'google_map_url' => ['nullable', 'url', 'max:500'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'delivery_enabled' => ['sometimes', 'boolean'],
            'pickup_enabled' => ['sometimes', 'boolean'],
            'estimated_delivery_time' => ['nullable', 'string', 'max:60'],
            'opening_hours' => ['nullable', 'array'],
            'opening_hours.*.day' => ['required_with:opening_hours', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'opening_hours.*.open' => ['nullable', 'string', 'max:10'],
            'opening_hours.*.close' => ['nullable', 'string', 'max:10'],
            'opening_hours.*.closed' => ['sometimes', 'boolean'],
        ];
    }
}
