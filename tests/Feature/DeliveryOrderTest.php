<?php

namespace Tests\Feature;

use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_can_list_accept_and_update_order_status()
    {
        $user = User::factory()->create(['role' => 'delivery']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $delivery = DeliveryAgent::factory()->create([
            'profile_id' => $profile->id,
            'company_id' => null,
        ]);
        \App\Models\Order::query()->delete();
        $order = Order::factory()->create([
            'status' => 'shipped',
            'delivery_company_id' => null,
        ]);
        $this->actingAs($user, 'sanctum');

        // Listar órdenes asignadas (vacío, aún no aceptó ninguna)
        $response = $this->getJson('/api/delivery/orders');
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertCount(0, $response->json('data'));

        // Listar órdenes disponibles (shipped sin delivery asignado)
        $response = $this->getJson('/api/delivery/available-orders');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        // Aceptar orden
        $response = $this->postJson('/api/delivery/orders/'.$order->id.'/accept');
        $response->assertStatus(200)->assertJson(['success' => true, 'message' => 'Orden aceptada exitosamente']);

        // Listar órdenes asignadas (ahora tiene 1)
        $response = $this->getJson('/api/delivery/orders');
        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertCount(1, $response->json('data'));

        // Marcar como entregada
        $response = $this->patchJson('/api/delivery/orders/'.$order->id.'/status', [
            'status' => 'delivered',
        ]);
        $response->assertStatus(200)->assertJson(['success' => true]);
        $order->refresh();
        $this->assertEquals('delivered', $order->status);
    }
}
