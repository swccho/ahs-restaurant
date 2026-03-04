<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AdminApiTestHelpers;

class CategoriesTest extends TestCase
{
    use AdminApiTestHelpers, RefreshDatabase;

    public function test_index_returns_paginated_results(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        Category::factory()->for($restaurant)->count(5)->create();

        $this->actingAsAdmin($owner);
        $response = $this->getJson('/api/admin/categories?per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.per_page', 2)
            ->assertJsonPath('data.meta.total', 5)
            ->assertJsonCount(2, 'data.data');
    }

    public function test_create_validates_name_required(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_validates_name_unique_per_restaurant(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        Category::factory()->for($restaurant)->create(['name' => 'Existing']);

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Existing',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_works(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create(['name' => 'Old Name']);

        $this->actingAsAdmin($owner);
        $response = $this->putJson("/api/admin/categories/{$category->id}", [
            'name' => 'New Name',
            'sort_order' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.sort_order', 5);
        $category->refresh();
        $this->assertSame('New Name', $category->name);
    }

    public function test_toggle_flips_is_active(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create(['is_active' => true]);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/categories/{$category->id}/toggle");

        $response->assertStatus(200);
        $category->refresh();
        $this->assertFalse($category->is_active);

        $this->patchJson("/api/admin/categories/{$category->id}/toggle");
        $category->refresh();
        $this->assertTrue($category->is_active);
    }

    public function test_reorder_updates_sort_order(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $c1 = Category::factory()->for($restaurant)->create(['sort_order' => 0]);
        $c2 = Category::factory()->for($restaurant)->create(['sort_order' => 1]);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson('/api/admin/categories/reorder', [
            'items' => [
                ['id' => $c1->id, 'sort_order' => 10],
                ['id' => $c2->id, 'sort_order' => 20],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertSame(10, $c1->fresh()->sort_order);
        $this->assertSame(20, $c2->fresh()->sort_order);
    }

    public function test_delete_blocked_when_category_has_menu_items(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->for($category)->create();

        $this->actingAsAdmin($owner);
        $response = $this->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_delete_succeeds_when_no_menu_items(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();

        $this->actingAsAdmin($owner);
        $response = $this->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
