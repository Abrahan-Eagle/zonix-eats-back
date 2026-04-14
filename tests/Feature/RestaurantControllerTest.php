<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantControllerTest extends TestCase
{
    use RefreshDatabase;

    public function actingAsBuyer()
    {
        $user = \App\Models\User::factory()->create(['role' => 'users']);
        \Laravel\Sanctum\Sanctum::actingAs($user);

        return $user;
    }

    public function test_can_list_restaurants()
    {
        $this->actingAsBuyer();
        \App\Models\Commerce::factory()->withProfile()->count(3)->create();
        $response = $this->getJson('/api/buyer/restaurants');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'items',
                    'restaurants',
                    'data' => [
                        '*' => ['id', 'profile_id', 'business_name', 'image', 'address', 'phone', 'open', 'schedule'],
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
                'message',
            ]);
    }

    public function test_list_restaurants_only_includes_open_commerces(): void
    {
        $this->actingAsBuyer();

        \App\Models\Commerce::factory()->withProfile()->create([
            'open' => true,
            'status' => 'approved',
            'business_name' => 'Abierto',
        ]);

        \App\Models\Commerce::factory()->withProfile()->create([
            'open' => false,
            'status' => 'approved',
            'business_name' => 'Cerrado',
        ]);

        $response = $this->getJson('/api/buyer/restaurants');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $restaurants = $response->json('data.data') ?? [];
        $this->assertCount(1, $restaurants);
        $this->assertSame('Abierto', $restaurants[0]['business_name']);
    }

    public function test_can_show_restaurant_details()
    {
        $this->actingAsBuyer();
        $commerce = \App\Models\Commerce::factory()->withProfile()->create([
            'open' => true,
            'status' => 'approved',
        ]);
        $response = $this->getJson("/api/buyer/restaurants/{$commerce->id}");
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $commerce->id);
    }

    public function test_show_restaurant_not_found()
    {
        $this->actingAsBuyer();
        $response = $this->getJson('/api/buyer/restaurants/999');
        $response->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_show_closed_restaurant_returns_not_found()
    {
        $this->actingAsBuyer();
        $commerce = \App\Models\Commerce::factory()->withProfile()->create([
            'open' => false,
            'status' => 'approved',
        ]);

        $response = $this->getJson("/api/buyer/restaurants/{$commerce->id}");

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
