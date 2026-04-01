<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderLifecycleStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_invalid_transition_returns_409_with_error_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status' => 'processing']);
        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error_code', 'ORDER_INVALID_TRANSITION');
    }

    public function test_admin_valid_transition_updates_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status' => 'shipped']);
        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(200)->assertJsonPath('success', true);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'delivered']);
    }

    public function test_commerce_invalid_transition_returns_409_with_error_code(): void
    {
        $commerceUser = User::factory()->create(['role' => 'commerce']);
        $profile = Profile::factory()->create(['user_id' => $commerceUser->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id]);
        $order = Order::factory()->create([
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
        ]);
        Sanctum::actingAs($commerceUser);

        $response = $this->putJson("/api/commerce/orders/{$order->id}/status", [
            'status' => 'processing',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error_code', 'ORDER_INVALID_TRANSITION');
    }

    public function test_delivery_cannot_accept_order_twice_returns_409_consistent_error(): void
    {
        $deliveryUser = User::factory()->create(['role' => 'delivery']);
        $profile = Profile::factory()->create(['user_id' => $deliveryUser->id]);
        $agent = DeliveryAgent::factory()->create(['profile_id' => $profile->id]);
        $otherAgent = DeliveryAgent::factory()->create();

        $order = Order::factory()->create(['status' => 'shipped']);
        OrderDelivery::factory()->create([
            'order_id' => $order->id,
            'agent_id' => $otherAgent->id,
            'status' => 'assigned',
        ]);

        Sanctum::actingAs($deliveryUser);

        $response = $this->postJson("/api/delivery/orders/{$order->id}/accept", [
            'notes' => 'intentando aceptar',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error_code', 'ORDER_ALREADY_ASSIGNED');
    }
}
