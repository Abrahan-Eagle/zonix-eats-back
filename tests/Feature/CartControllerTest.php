<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_product_to_cart()
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        // Crear comercio y producto (comercio abierto)
        $commerce = Commerce::factory()->create(['open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 5,
        ]);

        $data = [
            'product_id' => $product->id,
            'quantity' => 2,
        ];
        $response = $this->postJson('/api/buyer/cart/add', $data);
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Producto agregado al carrito'])
            ->assertJsonStructure(['success', 'data' => ['items', 'notes'], 'message']);
        $this->assertNotEmpty($response->json('data.items.0.line_id'));
    }

    public function test_show_cart_contents()
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/buyer/cart');
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['items', 'notes'], 'message']);
    }

    public function test_rejects_add_to_cart_when_stock_is_insufficient()
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $commerce = Commerce::factory()->create(['open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 1,
        ]);

        $response = $this->postJson('/api/buyer/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'OUT_OF_STOCK');
    }

    public function test_add_same_product_with_different_notes_creates_different_lines()
    {
        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $commerce = Commerce::factory()->create(['open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 10,
        ]);

        $this->postJson('/api/buyer/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'notes' => 'Sin cebolla',
        ])->assertStatus(200);

        $response = $this->postJson('/api/buyer/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
            'notes' => 'Con extra salsa',
        ])->assertStatus(200);

        $response->assertJsonCount(2, 'data.items');
    }
}
