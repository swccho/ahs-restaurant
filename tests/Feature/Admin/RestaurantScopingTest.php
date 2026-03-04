<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AdminApiTestHelpers;

class RestaurantScopingTest extends TestCase
{
    use AdminApiTestHelpers, RefreshDatabase;

    public function test_user_from_restaurant_a_cannot_view_category_from_restaurant_b(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        $categoryB = Category::factory()->for($restaurantB)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->getJson("/api/admin/categories/{$categoryB->id}");

        $this->assertForbiddenOrNotFound($response);
    }

    public function test_user_from_restaurant_a_cannot_update_category_from_restaurant_b(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        $categoryB = Category::factory()->for($restaurantB)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->putJson("/api/admin/categories/{$categoryB->id}", [
            'name' => 'Updated Name',
            'sort_order' => 1,
        ]);

        $this->assertForbiddenOrNotFound($response);
    }

    public function test_user_from_restaurant_a_cannot_delete_category_from_restaurant_b(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        $categoryB = Category::factory()->for($restaurantB)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->deleteJson("/api/admin/categories/{$categoryB->id}");

        $this->assertForbiddenOrNotFound($response);
    }

    public function test_user_from_restaurant_a_cannot_view_menu_item_from_restaurant_b(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        $categoryB = Category::factory()->for($restaurantB)->create();
        $itemB = MenuItem::factory()->for($restaurantB)->for($categoryB)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->getJson("/api/admin/menu-items/{$itemB->id}");

        $this->assertForbiddenOrNotFound($response);
    }

    public function test_user_from_restaurant_a_cannot_view_offer_from_restaurant_b(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        $offerB = Offer::factory()->for($restaurantB)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->getJson("/api/admin/offers/{$offerB->id}");

        $this->assertForbiddenOrNotFound($response);
    }

    public function test_user_from_restaurant_a_cannot_view_order_from_restaurant_b(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        $orderB = Order::factory()->for($restaurantB)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->getJson("/api/admin/orders/{$orderB->id}");

        $this->assertForbiddenOrNotFound($response);
    }

    public function test_categories_index_returns_only_records_from_users_restaurant(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        Category::factory()->for($restaurantA)->count(2)->create();
        Category::factory()->for($restaurantB)->count(3)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->getJson('/api/admin/categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.total', 2);
    }

    public function test_create_category_ignores_restaurant_id_from_payload(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($ownerA);
        $response = $this->postJson('/api/admin/categories', [
            'name' => 'New Category',
            'restaurant_id' => $restaurantB->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'restaurant_id' => $restaurantA->id,
        ]);
    }
}
