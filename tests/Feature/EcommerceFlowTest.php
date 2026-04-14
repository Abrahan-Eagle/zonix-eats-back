<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_complete_purchase_flow()
    {
        // Crear usuario buyer y autenticar
        $user = User::factory()->create(['role' => 'users']);
        // Perfil con factory para que se cree un teléfono en tabla phones (requerido para orden)
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Comprador',
            'lastName' => 'Test',
            'address' => 'Calle Falsa 123',
            'photo_users' => 'https://via.placeholder.com/150',
            'status' => 'completeData',
        ]);
        Sanctum::actingAs($user);

        // Crear restaurante y producto (asegurando que el producto pertenece a un commerce abierto)
        $commerce = Commerce::factory()->withProfile()->create(['open' => true]);
        $product = Product::factory()->create([
            'commerce_id' => $commerce->id,
            'available' => true,
            'stock_quantity' => 10,
            'price' => 22.50,
        ]);
        $product->refresh();

        // Listar restaurantes
        $this->getJson('/api/buyer/restaurants')->assertStatus(200);

        // Ver detalles de restaurante
        $this->getJson("/api/buyer/restaurants/{$commerce->id}")->assertStatus(200);

        // Ver detalles de producto
        $this->getJson("/api/buyer/products/{$product->id}")->assertStatus(200);

        // Agregar producto al carrito
        $this->postJson('/api/buyer/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertStatus(200);

        // Ver carrito
        $this->getJson('/api/buyer/cart')->assertStatus(200);

        // Crear orden
        $orderData = [
            'products' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
            'commerce_id' => $commerce->id,
            'delivery_type' => 'delivery',
            'delivery_fee' => 0,
            'total' => round((float) $product->price * 2, 2),
            'delivery_address' => 'Calle Falsa 123',
        ];
        $this->postJson('/api/buyer/orders', $orderData)
            ->assertStatus(201)
            ->assertJsonFragment(['message' => 'Orden creada exitosamente']);

        // Listar órdenes
        $this->getJson('/api/buyer/orders')->assertStatus(200);
    }
}
