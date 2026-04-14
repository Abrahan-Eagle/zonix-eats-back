<?php

namespace Tests\Feature;

use App\Models\Commerce;
use App\Models\Product;
use App\Models\Profile;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $cartService;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService;

        // Crear usuario autenticado con perfil (carrito asociado a profile)
        $this->user = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $this->user->id]);
        Sanctum::actingAs($this->user);

        // Crear comercio y productos para los tests (stock suficiente para update quantity a 5)
        $commerce = Commerce::factory()->create(['open' => true]);
        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'id' => 1,
            'available' => true,
            'stock_quantity' => 10,
        ]);
        Product::factory()->create([
            'commerce_id' => $commerce->id,
            'id' => 2,
            'available' => true,
            'stock_quantity' => 10,
        ]);
    }

    public function test_add_to_cart_new_product()
    {
        $productData = [
            'product_id' => 1,
            'quantity' => 2,
        ];

        $cart = $this->cartService->addToCart($productData);

        $this->assertIsArray($cart);
        $this->assertArrayHasKey('items', $cart);
        $this->assertCount(1, $cart['items']);
        $this->assertEquals(2, $cart['items'][0]['quantity']);
        $this->assertEquals(1, $cart['items'][0]['product_id']);
    }

    public function test_add_to_cart_existing_product()
    {
        // Agregar producto por primera vez
        $this->cartService->addToCart(['product_id' => 1, 'quantity' => 2]);

        // Agregar el mismo producto de nuevo
        $cart = $this->cartService->addToCart(['product_id' => 1, 'quantity' => 3]);

        $this->assertIsArray($cart);
        $this->assertCount(1, $cart['items']);
        $this->assertEquals(5, $cart['items'][0]['quantity']); // 2 + 3
    }

    public function test_update_quantity()
    {
        // Agregar producto al carrito
        $this->cartService->addToCart(['product_id' => 1, 'quantity' => 2]);

        // Actualizar cantidad
        $cart = $this->cartService->updateQuantity(1, 5);

        $this->assertIsArray($cart);
        $this->assertEquals(5, $cart['items'][0]['quantity']);
    }

    public function test_remove_from_cart()
    {
        // Agregar productos al carrito
        $this->cartService->addToCart(['product_id' => 1, 'quantity' => 2]);
        $this->cartService->addToCart(['product_id' => 2, 'quantity' => 1]);

        // Remover un producto
        $cart = $this->cartService->removeFromCart(1);

        $this->assertIsArray($cart);
        $this->assertCount(1, $cart['items']);
        $this->assertEquals(2, $cart['items'][0]['product_id']);
    }

    public function test_add_notes_to_cart()
    {
        // Agregar producto al carrito
        $this->cartService->addToCart(['product_id' => 1, 'quantity' => 2]);

        // Agregar notas
        $cart = $this->cartService->addNotes('Sin cebolla, por favor');

        $this->assertIsArray($cart);
        $this->assertEquals('Sin cebolla, por favor', $cart['notes']);
    }

    public function test_clear_cart()
    {
        // Agregar productos al carrito
        $this->cartService->addToCart(['product_id' => 1, 'quantity' => 2]);

        // Limpiar carrito
        $cart = $this->cartService->clearCart();

        $this->assertIsArray($cart);
        $this->assertArrayHasKey('items', $cart);
        $this->assertEmpty($cart['items']);
    }

    public function test_add_to_cart_throws_when_stock_is_insufficient()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Stock insuficiente');

        Product::where('id', 1)->update(['stock_quantity' => 1]);
        $this->cartService->addToCart(['product_id' => 1, 'quantity' => 2]);
    }

    public function test_add_same_product_with_different_notes_keeps_separate_lines()
    {
        $this->cartService->addToCart([
            'product_id' => 1,
            'quantity' => 1,
            'notes' => 'Sin cebolla',
        ]);

        $cart = $this->cartService->addToCart([
            'product_id' => 1,
            'quantity' => 1,
            'notes' => 'Con extra salsa',
        ]);

        $this->assertCount(2, $cart['items']);
        $this->assertNotEquals($cart['items'][0]['line_id'], $cart['items'][1]['line_id']);
    }
}
