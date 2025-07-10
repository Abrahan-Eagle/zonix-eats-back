<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Laravel\Sanctum\Sanctum;

class DeliveryRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_can_list_assigned_orders()
    {
        $delivery = User::factory()->deliveryAgent()->create();
        Sanctum::actingAs($delivery);
        // Crear órdenes asignadas
        $user = \App\Models\User::factory()->create(['role' => 'users']);
        $profile = \App\Models\Profile::factory()->create(['user_id' => $user->id]);
        $orders = \App\Models\Order::factory()->count(2)->create([
            'estado' => 'en_camino',
            'delivery_id' => $delivery->id,
            'profile_id' => $profile->id
        ]);
        // Listar órdenes asignadas (simulación de endpoint delivery)
        $response = $this->getJson('/api/delivery/orders');
        $response->assertStatus(200);
    }

    public function test_delivery_can_mark_order_as_delivered()
    {
        $delivery = User::factory()->deliveryAgent()->create();
        Sanctum::actingAs($delivery);
        $user = \App\Models\User::factory()->create(['role' => 'users']);
        $profile = \App\Models\Profile::factory()->create(['user_id' => $user->id]);
        $order = \App\Models\Order::factory()->create([
            'estado' => 'en_camino',
            'delivery_id' => $delivery->id,
            'profile_id' => $profile->id
        ]);
        // Marcar orden como entregada (simulación de endpoint delivery)
        $response = $this->patchJson("/api/delivery/orders/{$order->id}/status", ['estado' => 'entregado']);
        $response->assertStatus(200);
    }
}
