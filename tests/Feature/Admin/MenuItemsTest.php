<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\AdminApiTestHelpers;

class MenuItemsTest extends TestCase
{
    use AdminApiTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_index_filters_by_category_id(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $cat1 = Category::factory()->for($restaurant)->create();
        $cat2 = Category::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->for($cat1)->count(2)->create();
        MenuItem::factory()->for($restaurant)->for($cat2)->create();

        $this->actingAsAdmin($owner);
        $response = $this->getJson("/api/admin/menu-items?category_id={$cat1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.meta.total', 2);
    }

    public function test_index_filters_by_is_available_and_is_featured(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->for($category)->create(['is_available' => true, 'is_featured' => true]);
        MenuItem::factory()->for($restaurant)->for($category)->create(['is_available' => false]);

        $this->actingAsAdmin($owner);
        $response = $this->getJson('/api/admin/menu-items?is_available=1&is_featured=1');

        $response->assertStatus(200)
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_index_search_by_name(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->for($category)->create(['name' => 'Unique Burger Name']);
        MenuItem::factory()->for($restaurant)->for($category)->create(['name' => 'Pasta']);

        $this->actingAsAdmin($owner);
        $response = $this->getJson('/api/admin/menu-items?search=Burger');

        $response->assertStatus(200)
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonFragment(['name' => 'Unique Burger Name']);
    }

    public function test_create_validates_category_belongs_to_restaurant(): void
    {
        [$restaurantA, $ownerA] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();
        $categoryB = Category::factory()->for($restaurantB)->create();

        $this->actingAsAdmin($ownerA);
        $response = $this->postJson('/api/admin/menu-items', [
            'name' => 'Item',
            'category_id' => $categoryB->id,
            'price' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_create_validates_discount_price_lte_price(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/menu-items', [
            'name' => 'Item',
            'category_id' => $category->id,
            'price' => 10,
            'discount_price' => 15,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['discount_price']);
    }

    public function test_slug_uniqueness_per_restaurant(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();
        MenuItem::factory()->for($restaurant)->for($category)->create(['name' => 'Same Name']);

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/menu-items', [
            'name' => 'Same Name',
            'category_id' => $category->id,
            'price' => 10,
        ]);

        $response->assertStatus(201);
        $items = MenuItem::where('restaurant_id', $restaurant->id)->where('name', 'Same Name')->get();
        $this->assertCount(2, $items);
        $slugs = $items->pluck('slug')->unique()->values();
        $this->assertCount(2, $slugs);
    }

    public function test_availability_endpoint_updates_is_available(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();
        $item = MenuItem::factory()->for($restaurant)->for($category)->create(['is_available' => true]);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/menu-items/{$item->id}/availability", [
            'is_available' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_available', false);
        $item->refresh();
        $this->assertFalse($item->is_available);
    }

    public function test_featured_endpoint_updates_is_featured(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $category = Category::factory()->for($restaurant)->create();
        $item = MenuItem::factory()->for($restaurant)->for($category)->create(['is_featured' => false]);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/menu-items/{$item->id}/featured", [
            'is_featured' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_featured', true);
    }

    public function test_upload_accepts_valid_image_and_returns_path_and_url(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $file = UploadedFile::fake()->image('logo.jpg', 100, 100)->size(100);

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/uploads/menu-item-image', [
            'image' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['path', 'url']]);
    }

    public function test_upload_rejects_invalid_mime(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/uploads/menu-item-image', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_upload_rejects_file_over_2mb(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $file = UploadedFile::fake()->image('big.jpg')->size(2049);

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/uploads/menu-item-image', [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }
}
