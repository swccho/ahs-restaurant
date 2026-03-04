<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AdminApiTestHelpers;

class AdminAuthTest extends TestCase
{
    use AdminApiTestHelpers, RefreshDatabase;

    public function test_owner_can_login_with_correct_credentials(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $response = $this->postJson('/api/admin/login', [
            'email' => $owner->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $owner->id)
            ->assertJsonPath('data.restaurant.id', $restaurant->id);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $response = $this->postJson('/api/admin/login', [
            'email' => $owner->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_inactive_user_cannot_login(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $owner->update(['is_active' => false]);

        $response = $this->postJson('/api/admin/login', [
            'email' => $owner->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $this->actingAsAdmin($owner);

        $response = $this->getJson('/api/admin/me');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $owner->id);
    }

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/admin/me');

        $response->assertStatus(401);
    }

    public function test_logout_invalidates_session(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/admin/logout');
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $owner->refresh();
        $this->assertCount(0, $owner->tokens()->get(), 'Token should be revoked after logout');
    }
}
