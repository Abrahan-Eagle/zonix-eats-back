<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Laravel\Sanctum\Sanctum;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_orders_for_authenticated_buyer()
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = \App\Models\Profile::factory()->create(['user_id' => $user->id]);
        Order::factory()->count(2)->create(['profile_id' => $profile->id]);
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/buyer/orders');
        $response->assertStatus(200);
        $data = $response->json();
        // Verificar estructura de paginación
        if (isset($data['data'])) {
            $this->assertCount(2, $data['data']);
        } else {
            // Si no tiene paginación, verificar que sea array
            $this->assertIsArray($data);
            $this->assertGreaterThanOrEqual(2, count($data));
        }
    }

    public function test_cannot_list_orders_if_not_authenticated()
    {
        $response = $this->getJson('/api/buyer/orders');
        $response->assertStatus(401);
    }
}
