<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 15, 80);
        $deliveryFee = fake()->randomFloat(2, 0, 5);
        $total = round($subtotal + $deliveryFee, 2);
        return [
            'restaurant_id' => Restaurant::factory(),
            'status' => 'pending',
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'order_note' => null,
            'delivery_type' => fake()->randomElement(['delivery', 'pickup']),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'discount_amount' => 0,
            'total' => $total,
            'status_updated_at' => null,
            'status_updated_by' => null,
        ];
    }
}
