<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function actingAsBuyer()
    {
        $user = \App\Models\User::factory()->create(['role' => 'users']);
        \Laravel\Sanctum\Sanctum::actingAs($user);

        return $user;
    }

    public function test_can_show_product_details()
    {
        $this->actingAsBuyer();
        $commerce = \App\Models\Commerce::factory()->withProfile()->create([
            'open' => true,
            'status' => 'approved',
        ]);
        $product = \App\Models\Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
        ]);
        $response = $this->getJson("/api/buyer/products/{$product->id}");
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_show_product_not_found()
    {
        $this->actingAsBuyer();
        $response = $this->getJson('/api/buyer/products/999');
        $response->assertStatus(404);
    }

    public function test_show_product_returns_not_found_when_not_available()
    {
        $this->actingAsBuyer();
        $commerce = \App\Models\Commerce::factory()->withProfile()->create([
            'open' => true,
            'status' => 'approved',
        ]);
        $product = \App\Models\Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => false,
        ]);

        $response = $this->getJson("/api/buyer/products/{$product->id}");
        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_can_list_products_with_canonical_pagination_contract(): void
    {
        $this->actingAsBuyer();
        $commerce = \App\Models\Commerce::factory()->withProfile()->create([
            'open' => true,
            'status' => 'approved',
        ]);
        Product::factory()->count(2)->create([
            'commerce_id' => $commerce->id,
            'available' => true,
        ]);

        $response = $this->getJson('/api/buyer/products?per_page=1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items',
                    'products',
                    'data',
                    'pagination' => ['current_page', 'last_page', 'per_page', 'total'],
                ],
            ]);
    }
}
