<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 3);
        $unitPrice = fake()->randomFloat(2, 5, 25);
        $lineTotal = round($unitPrice * $qty, 2);
        return [
            'order_id' => Order::factory(),
            'menu_item_id' => MenuItem::factory(),
            'item_name_snapshot' => fake()->words(3, true),
            'unit_price_snapshot' => $unitPrice,
            'qty' => $qty,
            'line_total' => $lineTotal,
        ];
    }
}
