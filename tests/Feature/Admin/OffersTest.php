<?php

namespace Tests\Feature\Admin;

use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\AdminApiTestHelpers;

class OffersTest extends TestCase
{
    use AdminApiTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_create_validates_percentage_value_1_to_100(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/offers', [
            'title' => 'Offer',
            'type' => 'percentage',
            'value' => 150,
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('value', $response->json('errors') ?? []);
    }

    public function test_create_validates_bogo_free_delivery_must_have_no_value(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/offers', [
            'title' => 'BOGO',
            'type' => 'bogo',
            'value' => 10,
        ]);

        $response->assertStatus(422);
    }

    public function test_create_validates_ends_at_after_starts_at(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/offers', [
            'title' => 'Offer',
            'type' => 'percentage',
            'value' => 10,
            'starts_at' => now()->addDays(2)->toISOString(),
            'ends_at' => now()->addDay()->toISOString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ends_at']);
    }

    public function test_toggle_flips_is_active(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $offer = Offer::factory()->for($restaurant)->create(['is_active' => true]);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/offers/{$offer->id}/toggle");

        $response->assertStatus(200);
        $offer->refresh();
        $this->assertFalse($offer->is_active);
    }

    public function test_is_valid_now_active_no_dates(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $offer = Offer::factory()->for($restaurant)->create([
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $this->actingAsAdmin($owner);
        $response = $this->getJson("/api/admin/offers/{$offer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_valid_now', true);
    }

    public function test_is_valid_now_active_future_start(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $offer = Offer::factory()->for($restaurant)->create([
            'is_active' => true,
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(7),
        ]);

        $this->actingAsAdmin($owner);
        $response = $this->getJson("/api/admin/offers/{$offer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_valid_now', false);
    }

    public function test_is_valid_now_active_expired_end(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $offer = Offer::factory()->for($restaurant)->create([
            'is_active' => true,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->subDay(),
        ]);

        $this->actingAsAdmin($owner);
        $response = $this->getJson("/api/admin/offers/{$offer->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_valid_now', false);
    }

    public function test_banner_upload_returns_path_and_url(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $file = UploadedFile::fake()->image('banner.jpg', 100, 100)->size(100);

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/uploads/offer-banner', [
            'image' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['path', 'url']]);
    }
}
