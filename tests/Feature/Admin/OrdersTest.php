<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AdminApiTestHelpers;

class OrdersTest extends TestCase
{
    use AdminApiTestHelpers, RefreshDatabase;

    public function test_index_supports_filters(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        Order::factory()->for($restaurant)->create(['status' => 'pending']);
        Order::factory()->for($restaurant)->create(['status' => 'delivered']);
        $order = Order::factory()->for($restaurant)->create([
            'status' => 'pending',
            'customer_phone' => '+1234567890',
            'customer_name' => 'Alice Filter',
        ]);

        $this->actingAsAdmin($owner);
        $response = $this->getJson('/api/admin/orders?status=pending');

        $response->assertStatus(200)
            ->assertJsonPath('data.meta.total', 2);

        $response2 = $this->getJson('/api/admin/orders?q=Alice');
        $response2->assertStatus(200)
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_show_returns_items_and_totals(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $order = Order::factory()->for($restaurant)->create([
            'subtotal' => 25.00,
            'delivery_fee' => 3.00,
            'total' => 28.00,
        ]);
        OrderItem::factory()->for($order)->count(2)->create();

        $this->actingAsAdmin($owner);
        $response = $this->getJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.subtotal', 25)
            ->assertJsonPath('data.total', 28)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_status_pending_to_accepted_allowed(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $order = Order::factory()->for($restaurant)->create(['status' => 'pending']);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'accepted',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertSame('accepted', $order->status);
        $this->assertNotNull($order->status_updated_at);
        $this->assertSame($owner->id, $order->status_updated_by);
    }

    public function test_status_preparing_to_delivered_not_allowed(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $order = Order::factory()->for($restaurant)->create(['status' => 'preparing']);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.current_status', 'preparing');
        $order->refresh();
        $this->assertSame('preparing', $order->status);
    }

    public function test_status_pending_to_cancelled_allowed_with_cancel_reason(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $order = Order::factory()->for($restaurant)->create(['status' => 'pending']);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'cancelled',
            'cancel_reason' => 'Customer requested',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('Customer requested', $order->cancel_reason);
    }

    public function test_status_delivered_cannot_change(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $order = Order::factory()->for($restaurant)->create(['status' => 'delivered']);

        $this->actingAsAdmin($owner);
        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'pending',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_status_update_writes_audit_fields(): void
    {
        [$restaurant, $owner] = $this->createRestaurantWithOwner();
        $order = Order::factory()->for($restaurant)->create([
            'status' => 'pending',
            'status_updated_at' => null,
            'status_updated_by' => null,
        ]);

        $this->actingAsAdmin($owner);
        $this->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'accepted']);

        $order->refresh();
        $this->assertNotNull($order->status_updated_at);
        $this->assertSame($owner->id, $order->status_updated_by);

        $response = $this->getJson("/api/admin/orders/{$order->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.status_audit.status_updated_by.id', $owner->id);
    }
}
