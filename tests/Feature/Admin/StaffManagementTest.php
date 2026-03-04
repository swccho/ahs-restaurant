<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AdminApiTestHelpers;

class StaffManagementTest extends TestCase
{
    use AdminApiTestHelpers, RefreshDatabase;

    public function test_owner_can_list_staff(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $this->createStaff($restaurant);

        $this->actingAsAdmin($owner);
        $response = $this->getJson('/api/admin/staff');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.meta.total', 2);
    }

    public function test_owner_can_create_staff(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($owner);
        $response = $this->postJson('/api/admin/staff', [
            'name' => 'New Staff',
            'email' => 'staff@test.com',
            'password' => 'password123',
            'role' => 'staff',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.email', 'staff@test.com')
            ->assertJsonPath('data.role', 'staff');
        $this->assertDatabaseHas('users', [
            'email' => 'staff@test.com',
            'restaurant_id' => $restaurant->id,
            'role' => 'staff',
        ]);
    }

    public function test_owner_can_update_staff(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $staff = $this->createStaff($restaurant, ['name' => 'Old Name']);

        $this->actingAsAdmin($owner);
        $response = $this->putJson("/api/admin/staff/{$staff->id}", [
            'name' => 'Updated Name',
            'email' => $staff->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_owner_can_toggle_staff(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $staff = $this->createStaff($restaurant, ['is_active' => true]);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/staff/{$staff->id}/toggle", [
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $staff->refresh();
        $this->assertFalse($staff->is_active);
    }

    public function test_staff_cannot_access_staff_endpoints(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $staff = $this->createStaff($restaurant);

        $this->actingAsAdmin($staff);
        $response = $this->getJson('/api/admin/staff');

        $response->assertStatus(403);
    }

    public function test_owner_cannot_disable_self(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/staff/{$owner->id}/toggle", [
            'is_active' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_inactive_staff_cannot_access_me(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $staff = $this->createStaff($restaurant, ['is_active' => false]);

        $this->actingAsAdmin($staff);
        $response = $this->getJson('/api/admin/me');

        $response->assertStatus(403);
    }

    public function test_staff_created_has_restaurant_id_of_owner(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        [$restaurantB, ] = $this->createRestaurantWithOwner();

        $this->actingAsAdmin($owner);
        $this->postJson('/api/admin/staff', [
            'name' => 'Staff',
            'email' => 'staff2@test.com',
            'password' => 'password123',
            'role' => 'staff',
        ]);

        $user = User::where('email', 'staff2@test.com')->first();
        $this->assertSame($restaurant->id, $user->restaurant_id);
    }

    public function test_owner_can_delete_staff_but_not_owner(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $staff = $this->createStaff($restaurant);

        $this->actingAsAdmin($owner);
        $response = $this->deleteJson("/api/admin/staff/{$staff->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);

        $response2 = $this->deleteJson("/api/admin/staff/{$owner->id}");
        $response2->assertStatus(403);
    }
}
