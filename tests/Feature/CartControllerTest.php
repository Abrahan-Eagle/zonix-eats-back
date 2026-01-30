<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Commerce;
use Laravel\Sanctum\Sanctum;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_product_to_cart()
    {
        $user = User::factory()->create(['role' => 'users']);
        Sanctum::actingAs($user);
        
        // Crear comercio y producto (comercio abierto)
        $commerce = Commerce::factory()->create(['open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
        ]);
        
        $data = [
            'product_id' => $product->id,
            'quantity' => 2
        ];
        $response = $this->postJson('/api/buyer/cart/add', $data);
        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Producto agregado al carrito'])
                 ->assertJsonStructure(['cart']);
    }

    public function test_show_cart_contents()
    {
        $user = User::factory()->create(['role' => 'users']);
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/buyer/cart');
        $response->assertStatus(200)
                 ->assertJsonStructure(['cart']);
    }
}
