<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'type' => 'percentage',
            'value' => fake()->numberBetween(5, 20),
            'min_order_amount' => fake()->randomFloat(2, 10, 50),
            'coupon_code' => null,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ];
    }
}
