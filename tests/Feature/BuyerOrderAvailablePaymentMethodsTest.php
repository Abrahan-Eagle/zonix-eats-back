<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\Commerce;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests para GET /api/buyer/orders/{id}/available-payment-methods
 * (métodos de pago del comercio de la orden, para que el comprador elija al subir comprobante).
 */
class BuyerOrderAvailablePaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_404_when_order_not_found(): void
    {
        $user = User::factory()->create(['role' => 'users']);
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/buyer/orders/99999/available-payment-methods');
        $response->assertStatus(404)->assertJson(['success' => false, 'message' => 'Orden no encontrada']);
    }

    public function test_returns_404_when_order_belongs_to_another_user(): void
    {
        $buyer = User::factory()->create(['role' => 'users']);
        Profile::factory()->create(['user_id' => $buyer->id]);

        $otherProfile = Profile::factory()->create();
        $commerce = Commerce::factory()->create(['profile_id' => $otherProfile->id]);
        $order = Order::factory()->create([
            'profile_id' => $otherProfile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
        ]);

        $this->actingAs($buyer, 'sanctum');
        $response = $this->getJson("/api/buyer/orders/{$order->id}/available-payment-methods");
        $response->assertStatus(404);
    }

    public function test_returns_commerce_payment_methods_for_buyer_order(): void
    {
        $buyer = User::factory()->create(['role' => 'users']);
        $buyerProfile = Profile::factory()->create(['user_id' => $buyer->id]);

        $commerceOwnerProfile = Profile::factory()->create();
        $commerce = Commerce::factory()->create(['profile_id' => $commerceOwnerProfile->id]);

        $order = Order::factory()->create([
            'profile_id' => $buyerProfile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
        ]);

        $commerce->paymentMethods()->create([
            'type' => 'mobile_payment',
            'phone' => '04121234567',
            'is_active' => true,
            'is_default' => true,
            'reference_info' => ['alias' => 'Pago móvil - Personal'],
        ]);
        $commerce->paymentMethods()->create([
            'type' => 'bank_transfer',
            'account_number' => '01020000000000001234',
            'is_active' => true,
            'is_default' => false,
            'reference_info' => ['alias' => 'Transferencia Bancaria'],
        ]);

        $this->actingAs($buyer, 'sanctum');
        $response = $this->getJson("/api/buyer/orders/{$order->id}/available-payment-methods");

        $response->assertStatus(200)->assertJson(['success' => true]);
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $first = $data[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('type', $first);
        $this->assertArrayHasKey('label', $first);
        $this->assertContains($first['type'], ['mobile_payment', 'bank_transfer']);
        $labels = array_column($data, 'label');
        $this->assertContains('Pago móvil - Personal', $labels);
        $this->assertContains('Transferencia Bancaria', $labels);
    }

    public function test_returns_empty_array_when_commerce_has_no_payment_methods(): void
    {
        $buyer = User::factory()->create(['role' => 'users']);
        $buyerProfile = Profile::factory()->create(['user_id' => $buyer->id]);
        $commerceOwnerProfile = Profile::factory()->create();
        $commerce = Commerce::factory()->create(['profile_id' => $commerceOwnerProfile->id]);

        $order = Order::factory()->create([
            'profile_id' => $buyerProfile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
        ]);

        $this->actingAs($buyer, 'sanctum');
        $response = $this->getJson("/api/buyer/orders/{$order->id}/available-payment-methods");

        $response->assertStatus(200)->assertJson(['success' => true, 'data' => []]);
    }

    public function test_returns_only_active_payment_methods(): void
    {
        $buyer = User::factory()->create(['role' => 'users']);
        $buyerProfile = Profile::factory()->create(['user_id' => $buyer->id]);
        $commerceOwnerProfile = Profile::factory()->create();
        $commerce = Commerce::factory()->create(['profile_id' => $commerceOwnerProfile->id]);

        $order = Order::factory()->create([
            'profile_id' => $buyerProfile->id,
            'commerce_id' => $commerce->id,
            'status' => 'pending_payment',
        ]);

        $commerce->paymentMethods()->create([
            'type' => 'mobile_payment',
            'phone' => '04121234567',
            'is_active' => true,
            'reference_info' => ['alias' => 'Activo'],
        ]);
        $commerce->paymentMethods()->create([
            'type' => 'cash',
            'is_active' => false,
            'reference_info' => ['alias' => 'Inactivo'],
        ]);

        $this->actingAs($buyer, 'sanctum');
        $response = $this->getJson("/api/buyer/orders/{$order->id}/available-payment-methods");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Activo', $data[0]['label']);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/buyer/orders/1/available-payment-methods');
        $response->assertStatus(401);
    }
}
