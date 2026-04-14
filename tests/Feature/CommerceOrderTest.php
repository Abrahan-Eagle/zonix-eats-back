<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_commerce_can_list_show_and_update_order_status()
    {
        $user = User::factory()->create(['role' => 'commerce']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $order = Order::factory()->create([
            'commerce_id' => $commerce->id,
            'status' => 'paid',
        ]);
        $this->actingAs($user, 'sanctum');

        // Listar órdenes
        $response = $this->getJson('/api/commerce/orders');
        $response->assertStatus(200)->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'items',
                'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
            ],
        ]);
        $this->assertCount(1, $response->json('data.items'));

        // Mostrar orden
        $response = $this->getJson('/api/commerce/orders/'.$order->id);
        $response->assertStatus(200)->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'status'],
        ]);
        $this->assertEquals($order->id, $response->json('data.id'));

        // Actualizar estado de la orden
        $response = $this->putJson('/api/commerce/orders/'.$order->id.'/status', [
            'status' => 'processing',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $order->refresh();
        $this->assertEquals('processing', $order->status);
    }

    public function test_commerce_can_set_delivered_on_pickup_order_after_shipped(): void
    {
        $user = User::factory()->create(['role' => 'commerce']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $order = Order::factory()->create([
            'commerce_id' => $commerce->id,
            'status' => 'shipped',
            'delivery_type' => 'pickup',
        ]);
        $this->actingAs($user, 'sanctum');

        $response = $this->putJson('/api/commerce/orders/'.$order->id.'/status', [
            'status' => 'delivered',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $order->refresh();
        $this->assertSame('delivered', $order->status);
    }

    public function test_commerce_cannot_set_delivered_on_delivery_order_after_shipped(): void
    {
        $user = User::factory()->create(['role' => 'commerce']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $commerce = Commerce::factory()->create(['profile_id' => $profile->id, 'open' => true]);
        $order = Order::factory()->create([
            'commerce_id' => $commerce->id,
            'status' => 'shipped',
            'delivery_type' => 'delivery',
        ]);
        $this->actingAs($user, 'sanctum');

        $response = $this->putJson('/api/commerce/orders/'.$order->id.'/status', [
            'status' => 'delivered',
        ]);
        $response->assertStatus(409);
        $order->refresh();
        $this->assertSame('shipped', $order->status);
    }
}
