<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

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
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'items',
                'pagination',
            ],
        ]);
        $items = $response->json('data.items');
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
    }

    public function test_cannot_list_orders_if_not_authenticated()
    {
        $response = $this->getJson('/api/buyer/orders');
        $response->assertStatus(401);
    }
}
