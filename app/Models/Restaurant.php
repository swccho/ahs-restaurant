<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'cover_path',
        'theme_color',
        'phone',
        'whatsapp',
        'email',
        'address',
        'google_map_url',
        'delivery_fee',
        'min_order_amount',
        'delivery_enabled',
        'pickup_enabled',
        'estimated_delivery_time',
        'opening_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'delivery_enabled' => 'boolean',
            'pickup_enabled' => 'boolean',
            'delivery_fee' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'opening_hours' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
