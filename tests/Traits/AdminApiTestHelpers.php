<?php

namespace Tests\Traits;

use App\Models\Restaurant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait AdminApiTestHelpers
{
    /**
     * Create a restaurant and an owner user. Returns [Restaurant, User].
     *
     * @return array{0: Restaurant, 1: User}
     */
    protected function createRestaurantWithOwner(): array
    {
        $restaurant = Restaurant::factory()->create();
        $owner = User::factory()->create([
            'restaurant_id' => $restaurant->id,
            'role' => 'owner',
            'is_active' => true,
        ]);
        return [$restaurant, $owner];
    }

    /**
     * Create a staff user for the given restaurant.
     */
    protected function createStaff(Restaurant $restaurant, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'restaurant_id' => $restaurant->id,
            'role' => 'staff',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Act as the given user for subsequent requests (Sanctum).
     */
    protected function actingAsAdmin(User $user): static
    {
        Sanctum::actingAs($user, ['*']);
        return $this;
    }

    /**
     * Assert response is 403 Forbidden or 404 Not Found (cross-restaurant / unauthorized).
     */
    protected function assertForbiddenOrNotFound($response): void
    {
        $status = $response->getStatusCode();
        $this->assertTrue(
            $status === 403 || $status === 404,
            "Expected 403 or 404, got {$status}. Body: " . $response->getContent()
        );
    }
}
