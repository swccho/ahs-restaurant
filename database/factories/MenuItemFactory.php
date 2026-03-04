<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);
        return [
            'restaurant_id' => Restaurant::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numerify('####'),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 50),
            'discount_price' => null,
            'is_available' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }
}
